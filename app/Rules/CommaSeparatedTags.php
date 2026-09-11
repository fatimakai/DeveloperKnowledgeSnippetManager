<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CommaSeparatedTags implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || trim($value) === '') {
            return;
        }

        $tags = collect(explode(',', $value))->map(fn (string $tag) => trim($tag))->filter();

        if ($tags->count() > 10) {
            $fail('You may add no more than 10 tags.');
        }

        if ($tags->contains(fn (string $tag) => mb_strlen($tag) > 50)) {
            $fail('Each tag may be no more than 50 characters.');
        }
    }
}
