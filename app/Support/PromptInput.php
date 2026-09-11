<?php

namespace App\Support;

use App\Rules\CommaSeparatedTags;
use App\Rules\SafeText;

final class PromptInput
{
    /** @return array<string, array<int, mixed>> */
    public static function livewireRules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255', new SafeText(multiline: false)],
            'description' => ['nullable', 'string', 'max:2000', new SafeText],
            'promptText' => ['required', 'string', 'max:50000', new SafeText],
            'targetModel' => ['required', 'string', 'max:100', new SafeText(multiline: false)],
            'exampleInput' => ['nullable', 'string', 'max:10000', new SafeText],
            'exampleOutput' => ['nullable', 'string', 'max:10000', new SafeText],
            'visibility' => ['required', 'in:private,public'],
            'tags' => ['nullable', 'string', 'max:500', new SafeText(multiline: false), new CommaSeparatedTags],
        ];
    }

    /** @return array<string, array<int, mixed>> */
    public static function apiRules(bool $partial = false): array
    {
        $required = $partial ? 'sometimes' : 'required';

        return [
            'title' => [$required, 'string', 'max:255', new SafeText(multiline: false)],
            'description' => ['nullable', 'string', 'max:2000', new SafeText],
            'prompt_text' => [$required, 'string', 'max:50000', new SafeText],
            'target_model' => [$required, 'string', 'max:100', new SafeText(multiline: false)],
            'example_input' => ['nullable', 'string', 'max:10000', new SafeText],
            'example_output' => ['nullable', 'string', 'max:10000', new SafeText],
            'visibility' => ['sometimes', 'in:private,public'],
            'tags' => ['sometimes', 'array', 'max:10'],
            'tags.*' => ['string', 'max:50', new SafeText(multiline: false)],
        ];
    }

    /** @param array<string, mixed> $data */
    public static function normalize(array $data): array
    {
        foreach (['title', 'description', 'promptText', 'prompt_text', 'targetModel', 'target_model', 'exampleInput', 'example_input', 'exampleOutput', 'example_output', 'tags'] as $field) {
            if (array_key_exists($field, $data) && is_string($data[$field])) {
                $data[$field] = str_replace(["\r\n", "\r"], "\n", $data[$field]);
            }
        }

        foreach (['title', 'targetModel', 'target_model', 'tags'] as $field) {
            if (array_key_exists($field, $data) && is_string($data[$field])) {
                $data[$field] = trim($data[$field]);
            }
        }

        if (isset($data['tags']) && is_array($data['tags'])) {
            $data['tags'] = collect($data['tags'])
                ->map(fn (string $tag) => trim(mb_strtolower($tag)))
                ->filter()
                ->unique()
                ->values()
                ->all();
        }

        return $data;
    }
}
