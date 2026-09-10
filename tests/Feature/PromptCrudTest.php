<?php

namespace Tests\Feature;

use App\Livewire\CreatePrompt;
use App\Livewire\EditPrompt;
use App\Livewire\PromptBrowser;
use App\Models\Bookmark;
use App\Models\Prompt;
use App\Models\Upvote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PromptCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_and_edit_a_prompt(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(CreatePrompt::class)
            ->set('title', 'Release note writer')
            ->set('promptText', 'Write concise release notes for {{changes}}.')
            ->set('targetModel', 'GPT-4.1')
            ->set('visibility', 'private')
            ->set('tags', 'writing, product')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('prompts.mine'));

        $prompt = Prompt::firstOrFail();
        $this->assertSame(['product', 'writing'], $prompt->tags()->orderBy('name')->pluck('name')->all());

        Livewire::test(EditPrompt::class, ['prompt' => $prompt])
            ->set('title', 'Public release note writer')
            ->set('visibility', 'public')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('prompts', ['id' => $prompt->id, 'title' => 'Public release note writer', 'visibility' => 'public']);
    }

    public function test_user_cannot_open_or_update_another_users_private_prompt(): void
    {
        $prompt = Prompt::factory()->private()->create();
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('prompts.show', $prompt))->assertForbidden();
        $this->actingAs($user)->get(route('prompts.edit', $prompt))->assertForbidden();
    }

    public function test_public_prompt_detail_and_export_are_public_but_private_exports_are_protected(): void
    {
        $public = Prompt::factory()->public()->create(['title' => 'Public test prompt']);
        $private = Prompt::factory()->private()->create();

        $this->get(route('prompts.show', $public))->assertOk()->assertSee('Public test prompt');
        $this->get(route('prompts.export', $public))
            ->assertOk()
            ->assertHeader('content-type', 'application/json')
            ->assertJsonPath('title', 'Public test prompt');
        $this->get(route('prompts.show', $private))->assertForbidden();
        $this->get(route('prompts.export', $private))->assertForbidden();
    }

    public function test_account_export_contains_only_the_signed_in_users_prompts(): void
    {
        $user = User::factory()->create();
        Prompt::factory()->for($user)->create(['title' => 'Owned prompt']);
        Prompt::factory()->create(['title' => 'Someone else prompt']);

        $response = $this->actingAs($user)->get(route('prompts.export.all'));

        $response->assertOk()->assertHeader('content-type', 'application/json');
        $payload = json_decode($response->streamedContent(), true, flags: JSON_THROW_ON_ERROR);
        $this->assertCount(1, $payload);
        $this->assertSame('Owned prompt', $payload[0]['title']);
    }

    public function test_public_prompt_can_be_upvoted_and_bookmarked(): void
    {
        $user = User::factory()->create();
        $prompt = Prompt::factory()->public()->create();
        $this->actingAs($user);

        Livewire::test(PromptBrowser::class)
            ->call('toggleUpvote', $prompt->id)
            ->call('toggleBookmark', $prompt->id);

        $this->assertDatabaseHas('upvotes', ['user_id' => $user->id, 'prompt_id' => $prompt->id]);
        $this->assertDatabaseHas('bookmarks', ['user_id' => $user->id, 'prompt_id' => $prompt->id]);

        Livewire::test(PromptBrowser::class)->call('toggleUpvote', $prompt->id)->call('toggleBookmark', $prompt->id);
        $this->assertSame(0, Upvote::count());
        $this->assertSame(0, Bookmark::count());
    }

    public function test_discover_view_does_not_mix_in_private_prompts(): void
    {
        $user = User::factory()->create();
        Prompt::factory()->for($user)->private()->create(['title' => 'Private planning prompt']);
        Prompt::factory()->public()->create(['title' => 'Public discovery prompt']);

        $this->actingAs($user);

        Livewire::test(PromptBrowser::class, ['mode' => 'discover'])
            ->assertSee('Public discovery prompt')
            ->assertDontSee('Private planning prompt');
    }
}
