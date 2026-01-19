<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Snippet;
use App\Models\Tag;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create test user
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Create tags
        $tags = Tag::factory(15)->create();

        // Create snippets and attach random tags
        Snippet::factory(50)
            ->for($user)
            ->create()
            ->each(function ($snippet) use ($tags) {
                // Attach 2-5 random tags to each snippet
                $snippet->tags()->attach(
                    $tags->random(rand(2, 5))->pluck('id')->toArray()
                );
            });
    }
}
