<?php

namespace Database\Factories;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tag>
 */
class TagFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $usedTags = [];

        $allTags = [
            'database', 'api', 'authentication', 'validation', 'middleware',
            'testing', 'performance', 'security', 'regex', 'async',
            'error-handling', 'caching', 'pagination', 'search', 'sorting',
            'filtering', 'optimization', 'debugging', 'logging', 'documentation'
        ];

        // Get a tag that hasn't been used yet
        $availableTags = array_diff($allTags, $usedTags);
        
        if (empty($availableTags)) {
            $availableTags = $allTags;
            $usedTags = [];
        }

        $tag = $this->faker->randomElement($availableTags);
        $usedTags[] = $tag;

        return [
            'name' => $tag,
        ];
    }
}
