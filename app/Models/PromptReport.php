<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PromptReport extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_DISMISSED = 'dismissed';

    public const STATUS_RESOLVED = 'resolved';

    protected $fillable = ['prompt_id', 'reported_by', 'reason', 'details', 'status', 'reviewed_by', 'reviewed_at'];

    protected function casts(): array
    {
        return ['reviewed_at' => 'datetime'];
    }

    public function prompt(): BelongsTo
    {
        return $this->belongsTo(Prompt::class);
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
