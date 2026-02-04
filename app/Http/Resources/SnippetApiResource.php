<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SnippetApiResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'        => $this->id,
            'title'     => $this->title,
            'language'  => $this->language,
            'code'      => $this->code,
            'is_public' => $this->is_public,
            'tags'      => $this->tags->pluck('name'),
            'created_at'=> $this->created_at->toDateString(),
        ];
    }
}
