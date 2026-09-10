<?php

namespace Database\Seeders;

use App\Models\Prompt;
use App\Models\Tag;
use App\Models\Upvote;
use App\Models\User;
use App\Services\PromptVersionService;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $demo = User::factory()->create([
            'name' => 'PromptForge Demo',
            'email' => 'demo@promptforge.test',
        ]);
        $creators = User::factory(5)->create()->push($demo);
        $tags = collect(['marketing', 'engineering', 'research', 'support', 'writing', 'analysis'])
            ->map(fn (string $name) => Tag::create(compact('name')));
        $versions = app(PromptVersionService::class);

        Prompt::factory(30)->recycle($creators)->create()->each(function (Prompt $prompt) use ($tags, $creators, $versions): void {
            $prompt->tags()->attach($tags->random(random_int(1, 3))->pluck('id'));
            $versions->record($prompt, $prompt->user);
            if ($prompt->isPublic()) {
                $creators->random(random_int(0, min(4, $creators->count())))
                    ->each(fn (User $user) => Upvote::firstOrCreate(['user_id' => $user->id, 'prompt_id' => $prompt->id]));
            }
        });
    }
}
