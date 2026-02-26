<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavedSnippet extends Model
{
    /** @use HasFactory<\Database\Factories\SavedSnippetFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'snippet_id',
    ];

    /**
     * Get the user that saved the snippet.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the snippet that was saved.
     */
    public function snippet(): BelongsTo
    {
        return $this->belongsTo(Snippet::class);
    }
}
