<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class PromptCollection extends Model
{
    use HasFactory;

    public const ROLE_OWNER = 'owner';

    public const ROLE_EDITOR = 'editor';

    public const ROLE_VIEWER = 'viewer';

    public const MEMBER_ROLES = [self::ROLE_EDITOR, self::ROLE_VIEWER];

    protected $fillable = [
        'owner_id', 'name', 'slug', 'description', 'invite_token_hash', 'invite_role', 'invite_expires_at',
    ];

    protected $hidden = ['invite_token_hash'];

    protected function casts(): array
    {
        return ['invite_expires_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::creating(function (PromptCollection $collection): void {
            $collection->slug ??= static::uniqueSlug($collection->name);
        });
    }

    public static function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'collection';
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

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(CollectionMember::class);
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'collection_members')
            ->withPivot(['role', 'joined_at'])->withTimestamps();
    }

    public function prompts(): HasMany
    {
        return $this->hasMany(Prompt::class, 'collection_id');
    }

    public function membershipFor(User $user): ?CollectionMember
    {
        return $this->memberships()->where('user_id', $user->id)->first();
    }
}
