<?php

namespace App\Policies;

use App\Models\Prompt;
use App\Models\User;

class PromptPolicy
{
    public function view(?User $user, Prompt $prompt): bool
    {
        return $prompt->isPublic()
            || $user?->id === $prompt->user_id
            || ($user && $this->isCollectionMember($user, $prompt));
    }

    public function update(User $user, Prompt $prompt): bool
    {
        return $this->canEdit($user, $prompt);
    }

    public function viewHistory(User $user, Prompt $prompt): bool
    {
        return $user->isPro() && ($prompt->user_id === $user->id || $this->isCollectionMember($user, $prompt));
    }

    public function restoreVersion(User $user, Prompt $prompt): bool
    {
        return $user->isPro() && $this->canEdit($user, $prompt);
    }

    public function analyze(User $user, Prompt $prompt): bool
    {
        return $this->canEdit($user, $prompt);
    }

    public function delete(User $user, Prompt $prompt): bool
    {
        if (! $prompt->collection_id) {
            return $user->id === $prompt->user_id;
        }

        return $user->id === $prompt->user_id
            || ($user->isPro() && $prompt->collection?->owner_id === $user->id);
    }

    public function report(User $user, Prompt $prompt): bool
    {
        return $prompt->isPublic() && $prompt->user_id !== $user->id;
    }

    private function canEdit(User $user, Prompt $prompt): bool
    {
        if ($prompt->user_id === $user->id) {
            return true;
        }

        return $user->isPro() && ($prompt->collection?->memberships()
            ->where('user_id', $user->id)
            ->whereIn('role', ['owner', 'editor'])
            ->exists() ?? false);
    }

    private function isCollectionMember(User $user, Prompt $prompt): bool
    {
        return $user->isPro()
            && ($prompt->collection?->memberships()->where('user_id', $user->id)->exists() ?? false);
    }
}
