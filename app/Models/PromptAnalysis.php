<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PromptAnalysis extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'prompt_id', 'user_id', 'status', 'analysis', 'provider', 'model',
        'failure_reason', 'input_tokens', 'output_tokens', 'source_prompt_text',
        'source_target_model', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'analysis' => 'array',
            'completed_at' => 'datetime',
            'input_tokens' => 'integer',
            'output_tokens' => 'integer',
        ];
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
