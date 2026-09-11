<?php

namespace Tests\Feature;

use App\Jobs\AnalyzePromptJob;
use App\Livewire\AnalyzePrompt;
use App\Livewire\CreatePrompt;
use App\Models\CollectionAuditLog;
use App\Models\Prompt;
use App\Models\PromptCollection;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\RateLimiter;
use Laravel\Sanctum\Sanctum;
use Livewire\Livewire;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_browser_responses_include_the_reviewed_security_headers(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('X-XSS-Protection', '0')
            ->assertHeader('Referrer-Policy', 'same-origin')
            ->assertHeader('Cross-Origin-Opener-Policy', 'same-origin-allow-popups')
            ->assertHeader('Content-Security-Policy', "base-uri 'self'; form-action 'self'; frame-ancestors 'none'; object-src 'none'")
            ->assertHeaderMissing('Strict-Transport-Security');
    }

    public function test_authenticated_responses_are_not_stored_in_shared_browser_caches(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('dashboard'))
            ->assertOk()
            ->assertHeader('Cache-Control', 'max-age=0, must-revalidate, no-cache, no-store, private');
    }

    public function test_csrf_is_enforced_on_browser_posts_outside_the_test_environment(): void
    {
        $this->app->detectEnvironment(fn (): string => 'production');

        $this->post('/login', [
            'email' => 'nobody@example.com',
            'password' => 'password',
        ])->assertStatus(419);
    }

    public function test_hsts_is_sent_only_for_secure_production_requests(): void
    {
        $this->app->detectEnvironment(fn (): string => 'production');

        $this->get('https://localhost/')
            ->assertOk()
            ->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
    }

    public function test_prompt_fields_reject_control_characters_and_invalid_tag_shapes(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(CreatePrompt::class)
            ->set('title', "Unsafe\0title")
            ->set('targetModel', "GPT-4.1\nInjected")
            ->set('promptText', 'Valid prompt')
            ->set('tags', implode(',', range(1, 11)))
            ->call('save')
            ->assertHasErrors(['title', 'targetModel', 'tags']);

        Sanctum::actingAs($user);
        $this->postJson(route('api.prompts.store'), [
            'title' => 'API prompt',
            'prompt_text' => "Unsafe\0prompt",
            'target_model' => 'GPT-4.1',
        ])->assertUnprocessable()->assertJsonValidationErrors('prompt_text');

        $this->assertDatabaseCount('prompts', 0);
    }

    public function test_free_form_markup_is_preserved_but_html_escaped_when_rendered(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson(route('api.prompts.store'), [
            'title' => 'Markup prompt',
            'prompt_text' => "Explain <script>alert('xss')</script> safely.\r\nUse examples.",
            'target_model' => 'GPT-4.1',
        ])->assertCreated();

        $prompt = Prompt::firstOrFail();
        $this->assertStringNotContainsString("\r", $prompt->prompt_text);
        $this->assertStringContainsString('<script>', $prompt->prompt_text);

        $this->actingAs($user)->get(route('prompts.show', $prompt))
            ->assertOk()
            ->assertSee("Explain <script>alert('xss')</script> safely.")
            ->assertDontSee("<script>alert('xss')</script>", false);
    }

    public function test_analysis_dispatch_has_a_separate_burst_cost_control(): void
    {
        Queue::fake();
        RateLimiter::clear('prompt-analysis:burst:user:1');
        config([
            'services.openrouter.api_key' => 'test-key',
            'prompt-analysis.per_hour' => 10,
            'prompt-analysis.burst_per_minute' => 2,
        ]);
        $user = User::factory()->create();
        $prompt = Prompt::factory()->for($user)->create();

        for ($attempt = 0; $attempt < 2; $attempt++) {
            Livewire::actingAs($user)->test(AnalyzePrompt::class, ['prompt' => $prompt])
                ->call('analyze')->assertHasNoErrors();
            $prompt->analyses()->update(['status' => 'failed']);
        }

        Livewire::actingAs($user)->test(AnalyzePrompt::class, ['prompt' => $prompt])
            ->call('analyze')->assertHasErrors(['analyze']);

        $this->assertDatabaseCount('prompt_analyses', 2);
        Queue::assertPushed(AnalyzePromptJob::class, 2);
    }

    public function test_collection_access_and_role_changes_are_audited_without_invite_secrets(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $this->makePro($owner);
        $this->makePro($member);
        $collection = PromptCollection::factory()->for($owner, 'owner')->create();
        $collection->memberships()->createMany([
            ['user_id' => $owner->id, 'role' => 'owner', 'joined_at' => now()],
            ['user_id' => $member->id, 'role' => 'viewer', 'joined_at' => now()],
        ]);

        $this->actingAs($member)->get(route('collections.show', $collection))->assertOk();
        $this->actingAs($member)->get(route('collections.show', $collection))->assertOk();
        $this->actingAs($owner)->patch(route('collections.members.update', [$collection, $member]), ['role' => 'editor'])->assertRedirect();
        $inviteResponse = $this->actingAs($owner)->post(route('collections.invite', $collection), ['role' => 'viewer'])->assertRedirect();
        $inviteToken = basename($inviteResponse->getSession()->get('invite_url'));

        $this->assertSame(1, CollectionAuditLog::where('action', 'collection.viewed')->where('actor_id', $member->id)->count());
        $this->assertDatabaseHas('collection_audit_logs', [
            'prompt_collection_id' => $collection->id,
            'actor_id' => $owner->id,
            'target_user_id' => $member->id,
            'action' => 'member.role_changed',
        ]);
        $roleLog = CollectionAuditLog::where('action', 'member.role_changed')->firstOrFail();
        $this->assertSame(['collection_name' => $collection->name, 'old_role' => 'viewer', 'new_role' => 'editor'], $roleLog->metadata);
        $this->assertStringNotContainsString($inviteToken, CollectionAuditLog::all()->toJson());

        $this->actingAs($member)->get(route('collections.audit.index', $collection))->assertForbidden();
        $this->actingAs($owner)->get(route('collections.audit.index', $collection))
            ->assertOk()->assertSee('member.role_changed');
    }

    private function makePro(User $user): void
    {
        Subscription::factory()->for($user)->active()->create();
    }
}
