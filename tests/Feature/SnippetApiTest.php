<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Snippet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SnippetApiTest extends TestCase
{
    use RefreshDatabase;

    protected function authenticate()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['*'], 'sanctum');

        return $user;
    }

    public function test_user_can_list_their_snippets()
{
    $user = $this->authenticate();

    Snippet::factory()->count(3)->create([
        'user_id' => $user->id,
    ]);

    $response = $this->getJson('/api/snippets');

    $response->assertOk()
             ->assertJsonCount(3, 'data');
}

public function test_user_cannot_see_others_private_snippets()
{
    $user = $this->authenticate();
    $other = User::factory()->create();

    Snippet::factory()->create([
        'user_id' => $other->id,
        'is_public' => false,
    ]);

    $response = $this->getJson('/api/snippets');

    $response->assertOk()
             ->assertJsonCount(0, 'data');
}

public function test_user_can_create_snippet()
{
    $this->authenticate();

    $payload = [
        'title' => 'Test Snippet',
        'language' => 'php',
        'code' => '<?php echo "Hi"; ?>',
        'is_public' => true,
    ];

    $response = $this->postJson('/api/snippets', $payload);

    $response->assertCreated()->assertJsonPath('title', 'Test Snippet');

    $this->assertDatabaseHas('snippets', [
        'title' => 'Test Snippet',
        'is_public' => true,
    ]);
}

public function test_user_cannot_update_others_snippet()
{
    $this->authenticate();
    $other = User::factory()->create();

    $snippet = Snippet::factory()->create([
        'user_id' => $other->id,
    ]);

    $response = $this->putJson("/api/snippets/{$snippet->id}", [
        'title' => 'Hacked',
        'language' => 'php',
        'code' => 'evil',
    ]);

    $response->assertNotFound();
}

public function test_public_snippets_are_accessible()
{
    $snippet = Snippet::factory()->create([
        'is_public' => true,
        'slug' => 'my-public-snippet',
    ]);

    $response = $this->getJson("/api/public/snippets/{$snippet->slug}");

    $response->assertOk();
}


public function test_private_snippets_are_not_public()
{
    $snippet = Snippet::factory()->create(['is_public' => false, 'slug' => 'private-snippet',]);
    $response = $this->getJson("/api/public/snippets/{$snippet->slug}");

    $response->assertNotFound();
}


}
