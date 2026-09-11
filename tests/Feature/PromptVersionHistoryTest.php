<?php

namespace Tests\Feature;

use App\Livewire\CreatePrompt;
use App\Livewire\EditPrompt;
use App\Models\Prompt;
use App\Models\Subscription;
use App\Models\Tag;
use App\Models\User;
use App\Services\PromptVersionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PromptVersionHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_seeder_creates_an_initial_version_for_every_prompt(): void
    {
        $this->seed();

        $this->assertDatabaseCount('prompts', 30);
        $this->assertDatabaseCount('prompt_versions', 30);
    }

    public function test_create_and_meaningful_edits_record_immutable_versions(): void
    {
        $user = User::factory()->create();
        Subscription::factory()->for($user)->active()->create();
        $this->actingAs($user);

        Livewire::test(CreatePrompt::class)
            ->set('title', 'Support reply assistant')
            ->set('promptText', 'Draft a helpful response for {{ticket}}.')
            ->set('targetModel', 'GPT-4.1')
            ->set('tags', 'support, writing')
            ->call('save')
            ->assertHasNoErrors();

        $prompt = Prompt::firstOrFail();
        $first = $prompt->versions()->firstOrFail();
        $this->assertSame(1, $first->version_number);
        $this->assertSame('Initial version', $first->change_summary);
        $this->assertSame(['support', 'writing'], $first->tags);

        Livewire::test(EditPrompt::class, ['prompt' => $prompt])
            ->set('promptText', 'Draft an empathetic and concise response for {{ticket}}.')
            ->set('tags', 'support, customer-success')
            ->call('save')
            ->assertHasNoErrors();

        $second = $prompt->versions()->latest('version_number')->firstOrFail();
        $this->assertSame(2, $second->version_number);
        $this->assertStringContainsString('prompt text', $second->change_summary);
        $this->assertStringContainsString('tags', $second->change_summary);

        Livewire::test(EditPrompt::class, ['prompt' => $prompt->refresh()])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame(2, $prompt->versions()->count());
    }

    public function test_owner_can_compare_and_restore_an_older_version_without_losing_history(): void
    {
        $user = User::factory()->create();
        Subscription::factory()->for($user)->active()->create();
        $prompt = Prompt::factory()->for($user)->private()->create([
            'title' => 'Original prompt',
            'prompt_text' => 'Original instructions',
            'target_model' => 'GPT-4.1',
        ]);
        $prompt->tags()->attach(Tag::create(['name' => 'original']));

        $versions = app(PromptVersionService::class);
        $first = $versions->record($prompt, $user);

        $prompt->update([
            'title' => 'Current prompt',
            'prompt_text' => 'Current instructions',
            'target_model' => 'Claude Sonnet',
        ]);
        $prompt->tags()->sync([Tag::create(['name' => 'current'])->id]);
        $versions->record($prompt, $user);

        $this->actingAs($user)
            ->get(route('prompts.history.index', $prompt))
            ->assertOk()
            ->assertSee('Version 2')
            ->assertSee('Version 1');

        $this->actingAs($user)
            ->get(route('prompts.history.show', [$prompt, $first]))
            ->assertOk()
            ->assertSee('Original instructions')
            ->assertSee('Current instructions');

        $this->actingAs($user)
            ->post(route('prompts.history.restore', [$prompt, $first]))
            ->assertRedirect(route('prompts.show', $prompt));

        $prompt->refresh();
        $this->assertSame('Original prompt', $prompt->title);
        $this->assertSame('Original instructions', $prompt->prompt_text);
        $this->assertSame('GPT-4.1', $prompt->target_model);
        $this->assertSame(['original'], $prompt->tags()->pluck('name')->all());
        $this->assertSame(3, $prompt->versions()->count());
        $this->assertSame('Restored from version 1', $prompt->versions()->latest('version_number')->value('change_summary'));
    }

    public function test_version_history_and_restore_are_owner_only(): void
    {
        $owner = User::factory()->create();
        $viewer = User::factory()->create();
        Subscription::factory()->for($owner)->active()->create();
        Subscription::factory()->for($viewer)->active()->create();
        $prompt = Prompt::factory()->for($owner)->public()->create();
        $version = app(PromptVersionService::class)->record($prompt, $owner);

        $this->actingAs($viewer)
            ->get(route('prompts.history.index', $prompt))
            ->assertForbidden();
        $this->actingAs($viewer)
            ->get(route('prompts.history.show', [$prompt, $version]))
            ->assertForbidden();
        $this->actingAs($viewer)
            ->post(route('prompts.history.restore', [$prompt, $version]))
            ->assertForbidden();
    }

    public function test_a_version_cannot_be_used_through_another_prompts_route(): void
    {
        $user = User::factory()->create();
        Subscription::factory()->for($user)->active()->create();
        $prompt = Prompt::factory()->for($user)->create();
        $other = Prompt::factory()->for($user)->create();
        $otherVersion = app(PromptVersionService::class)->record($other, $user);

        $this->actingAs($user)
            ->get(route('prompts.history.show', [$prompt, $otherVersion]))
            ->assertNotFound();
    }
}
