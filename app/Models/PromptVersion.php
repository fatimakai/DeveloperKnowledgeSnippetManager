<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PromptVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'prompt_id', 'created_by', 'version_number', 'title', 'description',
        'prompt_text', 'target_model', 'example_input', 'example_output',
        'visibility', 'tags', 'change_summary',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'version_number' => 'integer',
        ];
    }

    public function prompt(): BelongsTo
    {
        return $this->belongsTo(Prompt::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function snapshot(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'prompt_text' => $this->prompt_text,
            'target_model' => $this->target_model,
            'example_input' => $this->example_input,
            'example_output' => $this->example_output,
            'visibility' => $this->visibility,
            'tags' => collect($this->tags)->sort()->values()->all(),
        ];
    }
}
