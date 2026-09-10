<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PromptAnalysis extends Model
{
    use HasFactory;

    protected $fillable = [
        'prompt_id', 'user_id', 'status', 'analysis', 'provider', 'model',
        'failure_reason', 'input_tokens', 'output_tokens',
    ];

    protected function casts(): array
    {
        return ['analysis' => 'array'];
    }

    public function prompt(): BelongsTo
    {
        return $this->belongsTo(Prompt::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
