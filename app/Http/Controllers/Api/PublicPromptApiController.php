<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PromptApiResource;
use App\Models\Prompt;

class PublicPromptApiController extends Controller
{
    public function index()
    {
        return PromptApiResource::collection(
            Prompt::query()
                ->where('visibility', Prompt::VISIBILITY_PUBLIC)
                ->with(['user', 'tags'])
                ->withCount('upvotes')
                ->latest()
                ->paginate(15)
        );
    }

    public function show(Prompt $prompt): PromptApiResource
    {
        abort_unless($prompt->isPublic(), 404);

        return new PromptApiResource($prompt->load(['user', 'tags'])->loadCount('upvotes'));
    }
}
