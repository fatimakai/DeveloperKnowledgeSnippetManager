<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Snippet extends Model
{
        use HasFactory;


    protected $fillable = [
    'title',
    'description',
    'code',
    'language',
    'user_id',
    'is_public',
    'slug',
    // 'tag_names',
];
public function getRouteKeyName()
{
    return 'slug';
}

    protected static function booted()
    {
        static::creating(function ($snippet) {
            if (empty($snippet->slug)) {
                $snippet->slug = Str::slug($snippet->title) . '-' . uniqid();
            }
        });
    }
public function tags()
{
    return $this->belongsToMany(Tag::class, 'snippet_tag', 'snippet_id', 'tag_id');
    
}

public function user()
{
    return $this->belongsTo(User::class);
}

public function likes()
{
    return $this->hasMany(Like::class);
}

public function savedSnippets()
{
    return $this->hasMany(SavedSnippet::class);
}

public function likedByUser($userId = null)
{
    if ($userId === null) {
        $userId = auth()->id();
    }

    return $this->likes()->where('user_id', $userId)->exists();
}

public function savedByUser($userId = null)
{
    if ($userId === null) {
        $userId = auth()->id();
    }

    return $this->savedSnippets()->where('user_id', $userId)->exists();
}
}
