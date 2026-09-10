<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PromptAnalysisApiResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'analysis' => $this->analysis,
            'source_prompt_text' => $this->source_prompt_text,
            'source_target_model' => $this->source_target_model,
            'provider' => $this->provider,
            'model' => $this->model,
            'failure_reason' => $this->failure_reason,
            'input_tokens' => $this->input_tokens,
            'output_tokens' => $this->output_tokens,
            'created_at' => $this->created_at?->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String(),
        ];
    }
}
