<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PromptApiResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            'description' => $this->description,
            'prompt_text' => $this->prompt_text,
            'target_model' => $this->target_model,
            'example_input' => $this->example_input,
            'example_output' => $this->example_output,
            'visibility' => $this->visibility,
            'collection' => $this->whenLoaded('collection', fn () => $this->collection ? [
                'id' => $this->collection->id,
                'slug' => $this->collection->slug,
                'name' => $this->collection->name,
            ] : null),
            'tags' => $this->whenLoaded('tags', fn () => $this->tags->pluck('name')->values()),
            'author' => $this->whenLoaded('user', fn () => [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ]),
            'upvotes_count' => $this->whenCounted('upvotes'),
            'current_version' => $this->whenCounted('versions'),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
