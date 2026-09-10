<?php

namespace App\Policies;

use App\Models\Prompt;
use App\Models\User;

class PromptPolicy
{
    public function view(?User $user, Prompt $prompt): bool
    {
        return $prompt->isPublic() || $user?->id === $prompt->user_id;
    }

    public function update(User $user, Prompt $prompt): bool
    {
        return $user->id === $prompt->user_id;
    }

    public function viewHistory(User $user, Prompt $prompt): bool
    {
        return $user->id === $prompt->user_id;
    }

    public function restoreVersion(User $user, Prompt $prompt): bool
    {
        return $user->id === $prompt->user_id;
    }

    public function analyze(User $user, Prompt $prompt): bool
    {
        return $user->id === $prompt->user_id;
    }

    public function delete(User $user, Prompt $prompt): bool
    {
        return $user->id === $prompt->user_id;
    }
}
