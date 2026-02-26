<?php

namespace Tests\Feature;

use App\Models\Snippet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardLoadTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_loads()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get('/dashboard');
        
        $response->assertOk();
    }

    public function test_snippets_index_loads()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get('/snippets');
        
        $response->assertOk();
    }

    public function test_my_snippets_loads()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get('/snippets/my');
        
        $response->assertOk();
    }
}
