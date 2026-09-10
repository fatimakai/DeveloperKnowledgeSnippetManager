<?php

namespace App\Policies;

use App\Models\PromptCollection;
use App\Models\User;

class PromptCollectionPolicy
{
    public function view(User $user, PromptCollection $collection): bool
    {
        return $collection->memberships()->where('user_id', $user->id)->exists();
    }

    public function update(User $user, PromptCollection $collection): bool
    {
        return $collection->owner_id === $user->id;
    }

    public function manageMembers(User $user, PromptCollection $collection): bool
    {
        return $this->update($user, $collection);
    }

    public function addPrompt(User $user, PromptCollection $collection): bool
    {
        return $collection->memberships()
            ->where('user_id', $user->id)
            ->whereIn('role', [PromptCollection::ROLE_OWNER, PromptCollection::ROLE_EDITOR])
            ->exists();
    }

    public function delete(User $user, PromptCollection $collection): bool
    {
        return $this->update($user, $collection);
    }
}
