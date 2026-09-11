<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_secret' => 'encrypted',
            'two_factor_recovery_codes' => 'encrypted:array',
            'two_factor_confirmed_at' => 'datetime',
            'two_factor_last_used_counter' => 'integer',
        ];
    }

    public function prompts()
    {
        return $this->hasMany(Prompt::class);
    }

    public function upvotes()
    {
        return $this->hasMany(Upvote::class);
    }

    public function bookmarks()
    {
        return $this->hasMany(Bookmark::class);
    }

    public function promptVersions()
    {
        return $this->hasMany(PromptVersion::class, 'created_by');
    }

    public function oauthAccounts(): HasMany
    {
        return $this->hasMany(OAuthAccount::class);
    }

    public function ownedCollections(): HasMany
    {
        return $this->hasMany(PromptCollection::class, 'owner_id');
    }

    public function collectionMemberships(): HasMany
    {
        return $this->hasMany(CollectionMember::class);
    }

    public function collectionAuditLogs(): HasMany
    {
        return $this->hasMany(CollectionAuditLog::class, 'actor_id');
    }

    public function collections()
    {
        return $this->belongsToMany(PromptCollection::class, 'collection_members')
            ->withPivot(['role', 'joined_at'])->withTimestamps();
    }

    public function promptReports(): HasMany
    {
        return $this->hasMany(PromptReport::class, 'reported_by');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function isPro(): bool
    {
        return $this->subscriptions()
            ->where('status', Subscription::STATUS_ACTIVE)
            ->where('current_period_end', '>', now())
            ->exists();
    }

    public function twoFactorEnabled(): bool
    {
        return filled($this->two_factor_secret) && $this->two_factor_confirmed_at !== null;
    }
}
