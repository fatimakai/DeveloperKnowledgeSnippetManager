<?php

namespace App\Services;

use App\Models\CollectionAuditLog;
use App\Models\Prompt;
use App\Models\PromptCollection;
use App\Models\User;

class CollectionAuditLogger
{
    /** @param array<string, scalar|null> $metadata */
    public function record(
        PromptCollection $collection,
        User $actor,
        string $action,
        ?User $targetUser = null,
        ?Prompt $prompt = null,
        array $metadata = [],
    ): CollectionAuditLog {
        return CollectionAuditLog::create([
            'prompt_collection_id' => $collection->id,
            'actor_id' => $actor->id,
            'target_user_id' => $targetUser?->id,
            'prompt_id' => $prompt?->id,
            'action' => $this->clean($action, 60),
            'ip_address' => request()?->ip(),
            'user_agent' => $this->clean(request()?->userAgent(), 500),
            'metadata' => $this->metadata($collection, $metadata),
        ]);
    }

    public function recordAccess(PromptCollection $collection, User $actor): void
    {
        $this->recordOnce($collection, $actor, 'collection.viewed');
    }

    /** @param array<string, scalar|null> $metadata */
    public function recordOnce(PromptCollection $collection, User $actor, string $action, array $metadata = []): void
    {
        $alreadyRecorded = CollectionAuditLog::query()
            ->where('prompt_collection_id', $collection->id)
            ->where('actor_id', $actor->id)
            ->where('action', $action)
            ->where('created_at', '>=', now()->subHour())
            ->exists();

        if (! $alreadyRecorded) {
            $this->record($collection, $actor, $action, metadata: $metadata);
        }
    }

    /** @param array<string, scalar|null> $metadata */
    private function metadata(PromptCollection $collection, array $metadata): array
    {
        $safe = ['collection_name' => $this->clean($collection->name, 120)];

        foreach ($metadata as $key => $value) {
            $safe[$this->clean($key, 60)] = is_string($value) ? $this->clean($value, 500) : $value;
        }

        return $safe;
    }

    private function clean(?string $value, int $limit): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = preg_replace('/[\x00-\x1F\x7F]/u', ' ', $value) ?? '';

        return mb_substr($value, 0, $limit);
    }
}
