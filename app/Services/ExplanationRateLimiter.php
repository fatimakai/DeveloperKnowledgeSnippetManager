<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class ExplanationRateLimiter
{
    /**
     * Maximum explanations per day per user.
     */
    private const MAX_EXPLANATIONS_PER_DAY = 10;

    /**
     * Cache key prefix.
     */
    private const CACHE_KEY_PREFIX = 'explanation_count_';

    /**
     * Check if user can generate an explanation.
     *
     * @param int $userId
     * @return bool
     */
    public function canGenerateExplanation(int $userId): bool
    {
        $count = $this->getCount($userId);
        return $count < self::MAX_EXPLANATIONS_PER_DAY;
    }

    /**
     * Increment the count for a user.
     *
     * @param int $userId
     * @return int The new count
     */
    public function incrementCount(int $userId): int
    {
        $key = $this->getCacheKey($userId);
        $count = Cache::increment($key);

        // Set expiration if this is the first increment
        if ($count === 1) {
            Cache::put($key, $count, now()->addDay());
        }

        return $count;
    }

    /**
     * Get the current count for a user.
     *
     * @param int $userId
     * @return int
     */
    public function getCount(int $userId): int
    {
        return Cache::get($this->getCacheKey($userId), 0);
    }

    /**
     * Get remaining explanations for a user today.
     *
     * @param int $userId
     * @return int
     */
    public function getRemainingExplanations(int $userId): int
    {
        $count = $this->getCount($userId);
        return max(0, self::MAX_EXPLANATIONS_PER_DAY - $count);
    }

    /**
     * Reset the count for a user (admin only).
     *
     * @param int $userId
     * @return void
     */
    public function reset(int $userId): void
    {
        Cache::forget($this->getCacheKey($userId));
    }

    /**
     * Get the cache key for a user.
     */
    private function getCacheKey(int $userId): string
    {
        return self::CACHE_KEY_PREFIX . $userId . '_' . date('Y-m-d');
    }
}
