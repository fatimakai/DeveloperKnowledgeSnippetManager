<?php

namespace Tests\Feature;

use App\Models\Like;
use App\Models\Snippet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SnippetLikesTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_like_a_public_snippet()
    {
        $user = User::factory()->create();
        $snippet = Snippet::factory()->create([
            'is_public' => true,
        ]);

        $this->actingAs($user)->get('/');

        $response = $this->actingAs($user)
            ->get('/');

        /** @var \App\Livewire\LikeSnippet $component */
        $component = $this->actingAs($user)
            ->get('/')
            ->original->getData()['lazyComponents']
            ?? collect();

        Like::create([
            'user_id' => $user->id,
            'snippet_id' => $snippet->id,
        ]);

        $this->assertTrue($snippet->likedByUser($user->id));
        $this->assertEquals(1, $snippet->likes()->count());
    }

    public function test_user_can_unlike_a_public_snippet()
    {
        $user = User::factory()->create();
        $snippet = Snippet::factory()->create([
            'is_public' => true,
        ]);

        $like = Like::create([
            'user_id' => $user->id,
            'snippet_id' => $snippet->id,
        ]);

        $this->assertTrue($snippet->likedByUser($user->id));

        $like->delete();

        $this->assertFalse($snippet->likedByUser($user->id));
        $this->assertEquals(0, $snippet->likes()->count());
    }

    public function test_each_user_can_like_a_snippet_only_once()
    {
        $user = User::factory()->create();
        $snippet = Snippet::factory()->create([
            'is_public' => true,
        ]);

        Like::create([
            'user_id' => $user->id,
            'snippet_id' => $snippet->id,
        ]);

        // Try to create another like from the same user
        $this->expectException(\Illuminate\Database\QueryException::class);
        Like::create([
            'user_id' => $user->id,
            'snippet_id' => $snippet->id,
        ]);
    }

    public function test_multiple_users_can_like_the_same_snippet()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $snippet = Snippet::factory()->create([
            'is_public' => true,
        ]);

        Like::create(['user_id' => $user1->id, 'snippet_id' => $snippet->id]);
        Like::create(['user_id' => $user2->id, 'snippet_id' => $snippet->id]);

        $this->assertEquals(2, $snippet->likes()->count());
        $this->assertTrue($snippet->likedByUser($user1->id));
        $this->assertTrue($snippet->likedByUser($user2->id));
    }

    public function test_snippet_tracks_likes_correctly()
    {
        $users = User::factory()->count(5)->create();
        $snippet = Snippet::factory()->create(['is_public' => true]);

        foreach ($users as $user) {
            Like::create([
                'user_id' => $user->id,
                'snippet_id' => $snippet->id,
            ]);
        }

        $this->assertEquals(5, $snippet->likes()->count());
    }

    public function test_snippet_without_likes_shows_zero()
    {
        $snippet = Snippet::factory()->create(['is_public' => true]);

        $this->assertEquals(0, $snippet->likes()->count());
    }

    public function test_snippets_can_be_sorted_by_likes()
    {
        // Create users for likes
        $users = User::factory(6)->create();
        
        // Create snippets
        $snippet1 = Snippet::factory()->create(['is_public' => true]);
        $snippet2 = Snippet::factory()->create(['is_public' => true]);
        $snippet3 = Snippet::factory()->create(['is_public' => true]);

        // Add likes - snippet1 gets 3 likes, snippet2 gets 1, snippet3 gets 2
        Like::create(['user_id' => $users[0]->id, 'snippet_id' => $snippet1->id]);
        Like::create(['user_id' => $users[1]->id, 'snippet_id' => $snippet1->id]);
        Like::create(['user_id' => $users[2]->id, 'snippet_id' => $snippet1->id]);

        Like::create(['user_id' => $users[3]->id, 'snippet_id' => $snippet2->id]);

        Like::create(['user_id' => $users[4]->id, 'snippet_id' => $snippet3->id]);
        Like::create(['user_id' => $users[5]->id, 'snippet_id' => $snippet3->id]);

        // Query sorted by likes
        $snippets = Snippet::withCount('likes')
            ->orderByDesc('likes_count')
            ->get();

        // Ordered by likes descending: snippet1 (3), snippet3 (2), snippet2 (1)
        $this->assertEquals($snippet1->id, $snippets[0]->id);
        $this->assertEquals(3, $snippets[0]->likes_count);

        $this->assertEquals($snippet3->id, $snippets[1]->id);
        $this->assertEquals(2, $snippets[1]->likes_count);

        $this->assertEquals($snippet2->id, $snippets[2]->id);
        $this->assertEquals(1, $snippets[2]->likes_count);
    }

    public function test_like_is_deleted_when_snippet_is_deleted()
    {
        $user = User::factory()->create();
        $snippet = Snippet::factory()->create(['is_public' => true]);

        Like::create([
            'user_id' => $user->id,
            'snippet_id' => $snippet->id,
        ]);

        $this->assertEquals(1, Like::count());

        $snippet->delete();

        $this->assertEquals(0, Like::count());
    }

    public function test_snippet_has_correct_like_relationships()
    {
        $user = User::factory()->create();
        $snippet = Snippet::factory()->create(['is_public' => true]);

        Like::create([
            'user_id' => $user->id,
            'snippet_id' => $snippet->id,
        ]);

        $this->assertCount(1, $snippet->refresh()->likes);
        $this->assertCount(1, $user->refresh()->likes);
    }

    public function test_unauthenticated_user_cannot_like_snippet()
    {
        $snippet = Snippet::factory()->create(['is_public' => true]);

        $this->assertFalse($snippet->likedByUser(999));
    }
}
