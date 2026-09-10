<?php

namespace Tests\Feature;

use App\Livewire\EditPrompt;
use App\Models\Prompt;
use App\Models\PromptCollection;
use App\Models\PromptReport;
use App\Models\User;
use App\Services\PlatformRoleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Livewire\Livewire;
use Tests\TestCase;

class RbacAndCollectionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_assigns_the_default_platform_role(): void
    {
        $this->post('/register', [
            'name' => 'New User', 'email' => 'new@example.com',
            'password' => 'password', 'password_confirmation' => 'password',
        ])->assertRedirect('/dashboard');

        $this->assertTrue(User::where('email', 'new@example.com')->firstOrFail()->hasRole(PlatformRoleService::USER));
    }

    public function test_platform_permissions_gate_admin_and_moderation_routes(): void
    {
        $roles = app(PlatformRoleService::class);
        $roles->ensure();
        $admin = User::factory()->create();
        $moderator = User::factory()->create();
        $user = User::factory()->create();
        $admin->assignRole(PlatformRoleService::ADMIN);
        $moderator->assignRole(PlatformRoleService::MODERATOR);
        $user->assignRole(PlatformRoleService::USER);

        $this->actingAs($admin)->get(route('admin.users.index'))->assertOk();
        $this->actingAs($admin)->get(route('moderation.index'))->assertOk();
        $this->actingAs($moderator)->get(route('moderation.index'))->assertOk();
        $this->actingAs($moderator)->get(route('admin.users.index'))->assertForbidden();
        $this->actingAs($user)->get(route('moderation.index'))->assertForbidden();
    }

    public function test_admin_can_change_a_users_role_but_cannot_demote_self(): void
    {
        app(PlatformRoleService::class)->ensure();
        $admin = User::factory()->create();
        $target = User::factory()->create();
        $admin->assignRole(PlatformRoleService::ADMIN);
        $target->assignRole(PlatformRoleService::USER);

        $this->actingAs($admin)->patch(route('admin.users.role', $target), ['role' => PlatformRoleService::MODERATOR])->assertRedirect();
        $this->assertTrue($target->fresh()->hasRole(PlatformRoleService::MODERATOR));
        $this->actingAs($admin)->patch(route('admin.users.role', $admin), ['role' => PlatformRoleService::USER])->assertStatus(422);
        $this->assertTrue($admin->fresh()->hasRole(PlatformRoleService::ADMIN));
    }

    public function test_collection_creation_always_creates_an_owner_membership(): void
    {
        $owner = User::factory()->create();

        $this->actingAs($owner)->post(route('collections.store'), ['name' => 'Launch prompts', 'description' => 'Shared work'])
            ->assertRedirect();

        $collection = PromptCollection::firstOrFail();
        $this->assertEquals($owner->id, $collection->owner_id);
        $this->assertDatabaseHas('collection_members', ['prompt_collection_id' => $collection->id, 'user_id' => $owner->id, 'role' => 'owner']);
    }

    public function test_invite_link_joins_a_member_with_the_selected_role_and_is_not_stored_in_plaintext(): void
    {
        [$owner, $collection] = $this->collection();
        $member = User::factory()->create();
        $response = $this->actingAs($owner)->post(route('collections.invite', $collection), ['role' => 'editor']);
        $url = $response->getSession()->get('invite_url');
        $token = Str::afterLast($url, '/');

        $this->assertNotEquals($token, $collection->fresh()->invite_token_hash);
        $this->actingAs($member)->get(route('collections.invites.show', $token))->assertOk()->assertSee('Join collection');
        $this->actingAs($member)->post(route('collections.join', $token))->assertRedirect(route('collections.show', $collection));
        $this->assertDatabaseHas('collection_members', ['prompt_collection_id' => $collection->id, 'user_id' => $member->id, 'role' => 'editor']);
    }

    public function test_expired_and_revoked_invites_cannot_be_used(): void
    {
        [$owner, $collection] = $this->collection();
        $token = Str::random(64);
        $collection->update(['invite_token_hash' => hash('sha256', $token), 'invite_expires_at' => now()->subMinute()]);

        $this->actingAs(User::factory()->create())->post(route('collections.join', $token))->assertNotFound();
        $collection->update(['invite_expires_at' => now()->addDay()]);
        $this->actingAs($owner)->delete(route('collections.invite.destroy', $collection));
        $this->actingAs(User::factory()->create())->post(route('collections.join', $token))->assertNotFound();
    }

    public function test_editor_can_modify_shared_prompts_while_viewer_is_read_only(): void
    {
        [$owner, $collection] = $this->collection();
        $editor = User::factory()->create();
        $viewer = User::factory()->create();
        $collection->memberships()->createMany([
            ['user_id' => $editor->id, 'role' => 'editor', 'joined_at' => now()],
            ['user_id' => $viewer->id, 'role' => 'viewer', 'joined_at' => now()],
        ]);
        $prompt = Prompt::factory()->for($owner)->create(['collection_id' => $collection->id]);

        $this->assertTrue($editor->can('view', $prompt));
        $this->assertTrue($editor->can('update', $prompt));
        $this->assertTrue($editor->can('analyze', $prompt));
        $this->assertTrue($viewer->can('view', $prompt));
        $this->assertTrue($viewer->can('viewHistory', $prompt));
        $this->assertFalse($viewer->can('update', $prompt));
        Livewire::actingAs($editor)->test(EditPrompt::class, ['prompt' => $prompt])
            ->set('title', 'Edited by a collection editor')
            ->call('save')
            ->assertHasNoErrors();
        $this->assertEquals('Edited by a collection editor', $prompt->fresh()->title);
        $this->actingAs($viewer)->get(route('prompts.edit', $prompt))->assertForbidden();
    }

    public function test_adding_a_prompt_to_a_collection_makes_it_private_and_one_collection_is_the_schema_model(): void
    {
        [$owner, $collection] = $this->collection();
        $prompt = Prompt::factory()->for($owner)->create(['visibility' => Prompt::VISIBILITY_PUBLIC]);

        $this->actingAs($owner)->post(route('collections.prompts.store', $collection), ['prompt_id' => $prompt->id])->assertRedirect();

        $prompt->refresh();
        $this->assertEquals($collection->id, $prompt->collection_id);
        $this->assertEquals(Prompt::VISIBILITY_PRIVATE, $prompt->visibility);
        $this->assertDatabaseCount('prompts', 1);
    }

    public function test_non_members_cannot_view_private_collection_prompts_and_removed_members_lose_access(): void
    {
        [$owner, $collection] = $this->collection();
        $member = User::factory()->create();
        $outsider = User::factory()->create();
        $collection->memberships()->create(['user_id' => $member->id, 'role' => 'viewer', 'joined_at' => now()]);
        $prompt = Prompt::factory()->for($owner)->create(['collection_id' => $collection->id, 'visibility' => 'public']);

        $this->assertEquals('private', $prompt->fresh()->visibility);
        $this->actingAs($member)->get(route('prompts.show', $prompt))->assertOk();
        $this->actingAs($outsider)->get(route('prompts.show', $prompt))->assertForbidden();
        $this->actingAs($owner)->delete(route('collections.members.destroy', [$collection, $member->id]));
        $this->actingAs($member)->get(route('prompts.show', $prompt))->assertForbidden();
    }

    public function test_prompt_api_lists_shared_prompts_but_keeps_viewers_read_only(): void
    {
        [$owner, $collection] = $this->collection();
        $viewer = User::factory()->create();
        $collection->memberships()->create(['user_id' => $viewer->id, 'role' => 'viewer', 'joined_at' => now()]);
        $prompt = Prompt::factory()->for($owner)->create(['collection_id' => $collection->id]);
        Sanctum::actingAs($viewer);

        $this->getJson(route('api.prompts.index'))
            ->assertOk()
            ->assertJsonPath('data.0.id', $prompt->id)
            ->assertJsonPath('data.0.collection.name', $collection->name);
        $this->patchJson(route('api.prompts.update', $prompt), ['title' => 'Not allowed'])->assertForbidden();
    }

    public function test_public_prompt_reports_are_reviewed_by_moderators(): void
    {
        app(PlatformRoleService::class)->ensure();
        $author = User::factory()->create();
        $reporter = User::factory()->create();
        $moderator = User::factory()->create();
        $moderator->assignRole(PlatformRoleService::MODERATOR);
        $prompt = Prompt::factory()->for($author)->create(['visibility' => Prompt::VISIBILITY_PUBLIC]);

        $this->actingAs($reporter)->post(route('prompts.reports.store', $prompt), ['reason' => 'spam', 'details' => 'Repeated promotion'])->assertRedirect();
        $report = PromptReport::firstOrFail();
        $this->actingAs($moderator)->patch(route('moderation.hide', $report))->assertRedirect();

        $this->assertEquals(Prompt::VISIBILITY_PRIVATE, $prompt->fresh()->visibility);
        $this->assertEquals(PromptReport::STATUS_RESOLVED, $report->fresh()->status);
        $this->assertEquals($moderator->id, $report->fresh()->reviewed_by);
    }

    public function test_authors_cannot_report_their_own_prompt_or_report_private_prompts(): void
    {
        $author = User::factory()->create();
        $other = User::factory()->create();
        $public = Prompt::factory()->for($author)->create(['visibility' => 'public']);
        $private = Prompt::factory()->for($author)->create(['visibility' => 'private']);

        $this->actingAs($author)->post(route('prompts.reports.store', $public), ['reason' => 'other'])->assertForbidden();
        $this->actingAs($other)->post(route('prompts.reports.store', $private), ['reason' => 'other'])->assertForbidden();
    }

    /** @return array{User, PromptCollection} */
    private function collection(): array
    {
        $owner = User::factory()->create();
        $collection = PromptCollection::create(['owner_id' => $owner->id, 'name' => 'Team library']);
        $collection->memberships()->create(['user_id' => $owner->id, 'role' => 'owner', 'joined_at' => now()]);

        return [$owner, $collection];
    }
}
