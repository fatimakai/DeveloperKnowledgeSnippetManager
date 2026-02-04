<?php

namespace Database\Factories;

use App\Models\Snippet;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Snippet>
 */
class SnippetFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $languages = ['php', 'javascript', 'python', 'sql', 'html', 'css', 'java', 'go', 'rust', 'typescript'];
        
        $codeSamples = [
            'php' => '<?php\n\nfunction helloWorld() {\n    echo "Hello, World!";\n}\n\nhelloWorld();',
            'javascript' => 'function helloWorld() {\n  console.log("Hello, World!");\n}\n\nhelloWorld();',
            'python' => 'def hello_world():\n    print("Hello, World!")\n\nhello_world()',
            'sql' => 'SELECT * FROM users WHERE is_active = 1 ORDER BY created_at DESC;',
            'html' => '<div class="container">\n  <h1>Hello World</h1>\n  <p>Welcome to my website</p>\n</div>',
            'css' => '.container {\n  max-width: 1200px;\n  margin: 0 auto;\n  padding: 20px;\n}',
            'java' => 'public class HelloWorld {\n  public static void main(String[] args) {\n    System.out.println("Hello, World!");\n  }\n}',
            'go' => 'package main\n\nimport "fmt"\n\nfunc main() {\n  fmt.Println("Hello, World!")\n}',
            'rust' => 'fn main() {\n    println!("Hello, World!");\n}',
            'typescript' => 'function helloWorld(): void {\n  console.log("Hello, World!");\n}\n\nhelloWorld();',
        ];

        $language = $this->faker->randomElement($languages);

        return [
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'code' => $codeSamples[$language] ?? $this->faker->text(),
            'language' => $language,
            'user_id' => User::inRandomOrder()->first()?->id ?? User::factory(),
            'is_public' => $this->faker->boolean(70),
            'slug' => Str::slug($this->faker->sentence(3)) . '-' . $this->faker->unique()->numberBetween(1000, 9999),
        ];
    }
}
