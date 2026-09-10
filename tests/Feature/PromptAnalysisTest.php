<?php

namespace Tests\Feature;

use App\Exceptions\PromptAnalysisException;
use App\Jobs\AnalyzePromptJob;
use App\Livewire\AnalyzePrompt;
use App\Models\Prompt;
use App\Models\PromptAnalysis;
use App\Models\User;
use App\Services\OpenRouterPromptAnalyzer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;
use Livewire\Livewire;
use Tests\TestCase;

class PromptAnalysisTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.openrouter.api_key' => 'test-key',
            'services.openrouter.base_url' => 'https://openrouter.test/api/v1',
            'prompt-analysis.model' => 'test/analyzer-model',
            'prompt-analysis.per_hour' => 5,
        ]);
    }

    public function test_analyzer_requests_and_validates_structured_output(): void
    {
        Http::fake([
            'openrouter.test/*' => Http::response($this->successfulProviderResponse(), 200),
        ]);

        $result = app(OpenRouterPromptAnalyzer::class)->analyze('Summarize this.', 'GPT-4.1');

        $this->assertSame('Summarize source material clearly.', $result['analysis']['intent_summary']);
        $this->assertSame(['No audience is specified.', 'No output format is defined.'], $result['analysis']['weaknesses']);
        $this->assertSame('test/analyzer-model', $result['model']);
        $this->assertSame(120, $result['input_tokens']);

        Http::assertSent(function (Request $request): bool {
            $payload = $request->data();

            return $request->hasHeader('Authorization', 'Bearer test-key')
                && $request->url() === 'https://openrouter.test/api/v1/chat/completions'
                && $payload['response_format']['type'] === 'json_schema'
                && str_contains($payload['messages'][1]['content'], 'Summarize this.')
                && str_contains($payload['messages'][1]['content'], 'GPT-4.1');
        });
    }

    public function test_job_completes_analysis_and_preserves_source_snapshot(): void
    {
        Http::fake(['openrouter.test/*' => Http::response($this->successfulProviderResponse(), 200)]);
        $user = User::factory()->create();
        $prompt = Prompt::factory()->for($user)->create();
        $analysis = PromptAnalysis::factory()->for($prompt)->for($user)->create([
            'source_prompt_text' => 'The exact original prompt.',
            'source_target_model' => 'GPT-4.1',
        ]);

        (new AnalyzePromptJob($analysis))->handle(app(OpenRouterPromptAnalyzer::class));

        $analysis->refresh();
        $this->assertSame(PromptAnalysis::STATUS_COMPLETED, $analysis->status);
        $this->assertSame('The exact original prompt.', $analysis->source_prompt_text);
        $this->assertSame('Rewrite this prompt with an audience and a strict bullet format.', $analysis->analysis['improved_prompt']);
        $this->assertSame(120, $analysis->input_tokens);
        $this->assertSame(80, $analysis->output_tokens);
        $this->assertNotNull($analysis->completed_at);
    }

    public function test_malformed_provider_output_marks_job_as_failed(): void
    {
        Http::fake(['openrouter.test/*' => Http::response([
            'choices' => [['message' => ['content' => 'not-json']]],
        ], 200)]);
        $user = User::factory()->create();
        $prompt = Prompt::factory()->for($user)->create();
        $analysis = PromptAnalysis::factory()->for($prompt)->for($user)->create();

        try {
            (new AnalyzePromptJob($analysis))->handle(app(OpenRouterPromptAnalyzer::class));
            $this->fail('The job should reject malformed provider output.');
        } catch (PromptAnalysisException) {
            $analysis->refresh();
            $this->assertSame(PromptAnalysis::STATUS_FAILED, $analysis->status);
            $this->assertStringContainsString('invalid analysis', $analysis->failure_reason);
        }
    }

    public function test_owner_can_queue_analysis_and_duplicate_active_requests_are_blocked(): void
    {
        Queue::fake();
        $user = User::factory()->create();
        $prompt = Prompt::factory()->for($user)->create([
            'prompt_text' => 'Analyze this exact prompt.',
            'target_model' => 'Claude Sonnet',
        ]);

        Livewire::actingAs($user)
            ->test(AnalyzePrompt::class, ['prompt' => $prompt])
            ->call('analyze')
            ->assertHasNoErrors()
            ->call('analyze')
            ->assertHasErrors(['analyze']);

        $this->assertDatabaseHas('prompt_analyses', [
            'prompt_id' => $prompt->id,
            'user_id' => $user->id,
            'status' => PromptAnalysis::STATUS_PENDING,
            'source_prompt_text' => 'Analyze this exact prompt.',
            'source_target_model' => 'Claude Sonnet',
        ]);
        Queue::assertPushed(AnalyzePromptJob::class, 1);
    }

    public function test_hourly_analysis_limit_is_enforced(): void
    {
        Queue::fake();
        config(['prompt-analysis.per_hour' => 1]);
        $user = User::factory()->create();
        $prompt = Prompt::factory()->for($user)->create();

        Livewire::actingAs($user)->test(AnalyzePrompt::class, ['prompt' => $prompt])->call('analyze');
        $prompt->analyses()->update(['status' => PromptAnalysis::STATUS_FAILED]);

        Livewire::actingAs($user)
            ->test(AnalyzePrompt::class, ['prompt' => $prompt])
            ->call('analyze')
            ->assertHasErrors(['analyze']);

        $this->assertDatabaseCount('prompt_analyses', 1);
    }

    public function test_analysis_ui_is_owner_only_and_renders_before_after_result(): void
    {
        $owner = User::factory()->create();
        $viewer = User::factory()->create();
        $prompt = Prompt::factory()->for($owner)->public()->create(['prompt_text' => 'Original prompt text']);
        PromptAnalysis::factory()->for($prompt)->for($owner)->completed()->create([
            'source_prompt_text' => 'Original prompt text',
        ]);

        $this->actingAs($owner)
            ->get(route('prompts.show', $prompt))
            ->assertOk()
            ->assertSee('Weaknesses and ambiguities')
            ->assertSee('Create a concise customer update.')
            ->assertSee('Copy improved prompt');

        $this->actingAs($viewer)
            ->get(route('prompts.show', $prompt))
            ->assertOk()
            ->assertDontSee('Analyze and improve this prompt');

        Livewire::actingAs($viewer)
            ->test(AnalyzePrompt::class, ['prompt' => $prompt])
            ->assertForbidden();
    }

    public function test_authenticated_api_can_start_and_read_owned_analysis(): void
    {
        Queue::fake();
        $owner = User::factory()->create();
        $prompt = Prompt::factory()->for($owner)->create();
        Sanctum::actingAs($owner);

        $created = $this->postJson('/api/prompts/'.$prompt->slug.'/analyses')
            ->assertAccepted()
            ->assertJsonPath('data.status', PromptAnalysis::STATUS_PENDING);

        $analysisId = $created->json('data.id');
        $this->getJson('/api/prompts/'.$prompt->slug.'/analyses')
            ->assertOk()
            ->assertJsonCount(1, 'data');
        $this->getJson('/api/prompts/'.$prompt->slug.'/analyses/'.$analysisId)
            ->assertOk()
            ->assertJsonPath('data.source_target_model', $prompt->target_model);
    }

    public function test_api_reports_missing_configuration_without_creating_analysis(): void
    {
        config(['services.openrouter.api_key' => null]);
        $owner = User::factory()->create();
        $prompt = Prompt::factory()->for($owner)->create();
        Sanctum::actingAs($owner);

        $this->postJson('/api/prompts/'.$prompt->slug.'/analyses')
            ->assertServiceUnavailable()
            ->assertJsonPath('message', 'AI analysis is not configured yet. Add your OpenRouter API key and model.');

        $this->assertDatabaseCount('prompt_analyses', 0);
    }

    public function test_api_returns_conflict_when_analysis_is_already_active(): void
    {
        Queue::fake();
        $owner = User::factory()->create();
        $prompt = Prompt::factory()->for($owner)->create();
        PromptAnalysis::factory()->for($prompt)->for($owner)->create();
        Sanctum::actingAs($owner);

        $this->postJson('/api/prompts/'.$prompt->slug.'/analyses')
            ->assertConflict()
            ->assertJsonPath('message', 'An analysis is already in progress for this prompt.');
    }

    public function test_api_rejects_analysis_access_for_non_owner(): void
    {
        Queue::fake();
        $owner = User::factory()->create();
        $viewer = User::factory()->create();
        $prompt = Prompt::factory()->for($owner)->public()->create();
        $analysis = PromptAnalysis::factory()->for($prompt)->for($owner)->create();
        Sanctum::actingAs($viewer);

        $this->postJson('/api/prompts/'.$prompt->slug.'/analyses')->assertForbidden();
        $this->getJson('/api/prompts/'.$prompt->slug.'/analyses')->assertForbidden();
        $this->getJson('/api/prompts/'.$prompt->slug.'/analyses/'.$analysis->id)->assertForbidden();
    }

    private function successfulProviderResponse(): array
    {
        return [
            'model' => 'test/analyzer-model',
            'choices' => [[
                'message' => [
                    'content' => json_encode([
                        'intent_summary' => 'Summarize source material clearly.',
                        'weaknesses' => ['No audience is specified.', 'No output format is defined.'],
                        'improved_prompt' => 'Rewrite this prompt with an audience and a strict bullet format.',
                    ], JSON_THROW_ON_ERROR),
                ],
            ]],
            'usage' => [
                'prompt_tokens' => 120,
                'completion_tokens' => 80,
            ],
        ];
    }
}
