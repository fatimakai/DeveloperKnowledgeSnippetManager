<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Prompt extends Model
{
    use HasFactory;

    public const VISIBILITY_PRIVATE = 'private';

    public const VISIBILITY_PUBLIC = 'public';

    protected $fillable = [
        'user_id', 'collection_id', 'title', 'slug', 'description', 'prompt_text', 'target_model',
        'example_input', 'example_output', 'visibility',
    ];

    protected static function booted(): void
    {
        static::creating(function (Prompt $prompt): void {
            if (! $prompt->slug) {
                $prompt->slug = static::uniqueSlug($prompt->title);
            }
        });

        static::saving(function (Prompt $prompt): void {
            if ($prompt->collection_id) {
                $prompt->visibility = self::VISIBILITY_PRIVATE;
            }
        });
    }

    public static function uniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'prompt';
        $slug = $base;
        $counter = 2;

        while (static::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function collection(): BelongsTo
    {
        return $this->belongsTo(PromptCollection::class, 'collection_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class)->withTimestamps();
    }

    public function upvotes(): HasMany
    {
        return $this->hasMany(Upvote::class);
    }

    public function bookmarks(): HasMany
    {
        return $this->hasMany(Bookmark::class);
    }

    public function analyses(): HasMany
    {
        return $this->hasMany(PromptAnalysis::class);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(PromptVersion::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(PromptReport::class);
    }

    public function isPublic(): bool
    {
        return $this->visibility === self::VISIBILITY_PUBLIC;
    }
}
