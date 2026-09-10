<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CollectionMember extends Model
{
    use HasFactory;

    protected $fillable = ['prompt_collection_id', 'user_id', 'role', 'joined_at'];

    protected function casts(): array
    {
        return ['joined_at' => 'datetime'];
    }

    public function collection(): BelongsTo
    {
        return $this->belongsTo(PromptCollection::class, 'prompt_collection_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
