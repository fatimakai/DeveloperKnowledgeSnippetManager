<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SnippetExplanation extends Model
{
    /** @use HasFactory<\Database\Factories\SnippetExplanationFactory> */
    use HasFactory;

    protected $fillable = [
        'snippet_id',
        'user_id',
        'content',
    ];

    /**
     * Get the snippet this explanation belongs to.
     */
    public function snippet(): BelongsTo
    {
        return $this->belongsTo(Snippet::class);
    }

    /**
     * Get the user who requested this explanation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
