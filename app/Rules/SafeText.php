<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SafeText implements ValidationRule
{
    public function __construct(private readonly bool $multiline = true) {}

    /**
     * Reject control characters that are invalid in user-authored text. Tabs and
     * line endings remain valid for multiline prompt content and code samples.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            return;
        }

        if (preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', $value) !== 0) {
            $fail("The {$attribute} contains unsupported control characters.");

            return;
        }

        if (! $this->multiline && preg_match('/[\r\n]/u', $value) !== 0) {
            $fail("The {$attribute} must be a single line.");
        }
    }
}
