<?php

namespace Tests\Feature;

use App\Models\Prompt;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PromptApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_manage_prompts_by_slug(): void
    {
        $user = User::factory()->create();
        Subscription::factory()->for($user)->active()->create();
        Sanctum::actingAs($user);

        $created = $this->postJson('/api/prompts', [
            'title' => 'Customer interview synthesis',
            'prompt_text' => 'Summarize these interview notes.',
            'target_model' => 'GPT-4.1',
            'visibility' => 'private',
            'tags' => ['research', 'summary'],
        ])->assertCreated()
            ->assertJsonPath('data.visibility', 'private')
            ->assertJsonPath('data.current_version', 1);

        $slug = $created->json('data.slug');
        $this->assertDatabaseHas('prompt_versions', ['version_number' => 1, 'title' => 'Customer interview synthesis']);
        $this->getJson('/api/prompts/'.$slug)->assertOk()->assertJsonPath('data.title', 'Customer interview synthesis');

        $this->putJson('/api/prompts/'.$slug, [
            'title' => 'Updated research prompt',
            'prompt_text' => 'Find and group recurring themes.',
            'target_model' => 'Claude Sonnet',
            'visibility' => 'public',
            'tags' => ['research'],
        ])->assertOk()
            ->assertJsonPath('data.visibility', 'public')
            ->assertJsonPath('data.current_version', 2);
        $this->assertDatabaseHas('prompt_versions', ['version_number' => 2, 'title' => 'Updated research prompt']);

        $this->deleteJson('/api/prompts/'.$slug)->assertNoContent();
        $this->assertDatabaseMissing('prompts', ['slug' => $slug]);
        $this->assertDatabaseCount('prompt_versions', 0);
    }

    public function test_user_cannot_modify_another_users_prompt(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $prompt = Prompt::factory()->public()->create();

        $this->putJson('/api/prompts/'.$prompt->slug, [
            'title' => 'Unauthorized edit',
            'prompt_text' => 'No',
            'target_model' => 'GPT-4.1',
        ])->assertForbidden();
        $this->deleteJson('/api/prompts/'.$prompt->slug)->assertForbidden();
    }

    public function test_patch_updates_only_supplied_fields_and_keeps_tags(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $prompt = Prompt::factory()->for($user)->private()->create([
            'title' => 'Original title',
            'prompt_text' => 'Original prompt text',
            'target_model' => 'GPT-4.1',
        ]);
        $prompt->tags()->create(['name' => 'research']);

        $this->patchJson('/api/prompts/'.$prompt->slug, ['title' => 'Patched title'])
            ->assertOk()
            ->assertJsonPath('data.title', 'Patched title')
            ->assertJsonPath('data.prompt_text', 'Original prompt text')
            ->assertJsonPath('data.tags.0', 'research');

        $this->assertDatabaseHas('prompt_tag', ['prompt_id' => $prompt->id]);
    }

    public function test_public_api_exposes_only_public_prompts(): void
    {
        $public = Prompt::factory()->public()->create();
        $private = Prompt::factory()->private()->create();

        $this->getJson('/api/public/prompts')->assertOk()->assertJsonCount(1, 'data');
        $this->getJson('/api/public/prompts/'.$public->slug)->assertOk();
        $this->getJson('/api/public/prompts/'.$private->slug)->assertNotFound();
    }

    public function test_private_library_api_requires_authentication(): void
    {
        $this->getJson('/api/prompts')->assertUnauthorized();
        $this->postJson('/api/prompts', [])->assertUnauthorized();
    }
}
