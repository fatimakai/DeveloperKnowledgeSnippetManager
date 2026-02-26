<?php

namespace App\Services;

use App\Models\Snippet;
use App\Models\SnippetExplanation;
use Illuminate\Support\Facades\Http;

class AIExplanationService
{
    private string $apiKey;
    private string $baseUrl;
    private string $model;

    public function __construct()
    {
        $this->apiKey = config('services.openrouter.api_key');
        $this->baseUrl = config('services.openrouter.base_url');
        $this->model = config('services.openrouter.model');
    }

    /**
     * Generate an explanation for a snippet using OpenRouter.
     *
     * @param Snippet $snippet
     * @param int $userId
     * @return string The generated explanation
     * @throws \Exception
     */
    public function generateExplanation(Snippet $snippet, int $userId): string
    {
        $systemPrompt = $this->getSystemPrompt();
        $userPrompt = $this->getUserPrompt($snippet);

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'HTTP-Referer' => config('app.url'),
                'X-Title' => 'Developer Knowledge Snippet Manager',
            ])->post("{$this->baseUrl}/chat/completions", [
                'model' => $this->model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => $systemPrompt,
                    ],
                    [
                        'role' => 'user',
                        'content' => $userPrompt,
                    ],
                ],
                'temperature' => 0.7,
                'max_tokens' => 300,
            ]);

            if (!$response->successful()) {
                throw new \Exception("OpenRouter API error: " . $response->body());
            }

            $explanation = $response->json('choices.0.message.content');

            if (!$explanation) {
                throw new \Exception('No content in API response');
            }

            // Store the explanation in the database
            SnippetExplanation::updateOrCreate(
                ['snippet_id' => $snippet->id],
                [
                    'user_id' => $userId,
                    'content' => $explanation,
                ]
            );

            return $explanation;
        } catch (\Exception $e) {
            \Log::error('OpenRouter API error generating snippet explanation', [
                'snippet_id' => $snippet->id,
                'error' => $e->getMessage(),
            ]);
            throw new \Exception('Failed to generate explanation: ' . $e->getMessage());
        }
    }

    /**
     * Get the system prompt for code explanation.
     */
    private function getSystemPrompt(): string
    {
        return <<<'PROMPT'
You are a code explainer. Provide brief, concise explanations.

Keep your response SHORT - aim for 3-5 sentences max, or bullet points.
Do NOT include step-by-step breakdowns or lengthy descriptions.
Just give a quick overview of what the code does and how.
Use markdown formatting with minimal headers.
PROMPT;
    }

    /**
     * Build the user prompt with the snippet code.
     */
    private function getUserPrompt(Snippet $snippet): string
    {
        return <<<PROMPT
Language: {$snippet->language}

Code to explain:
\`\`\`{$snippet->language}
{$snippet->code}
\`\`\`

Briefly explain this code. Keep it to 3-5 sentences maximum.
PROMPT;
    }

    /**
     * Check if a snippet has an explanation.
     *
     * @param Snippet $snippet
     * @return bool
     */
    public function hasExplanation(Snippet $snippet): bool
    {
        return $snippet->explanation()->exists();
    }

    /**
     * Get the explanation for a snippet.
     *
     * @param Snippet $snippet
     * @return SnippetExplanation|null
     */
    public function getExplanation(Snippet $snippet): ?SnippetExplanation
    {
        return $snippet->explanation;
    }

    /**
     * Delete an explanation for a snippet.
     *
     * @param Snippet $snippet
     * @return bool
     */
    public function deleteExplanation(Snippet $snippet): bool
    {
        return $snippet->explanation()?->delete() ?? false;
    }
}
