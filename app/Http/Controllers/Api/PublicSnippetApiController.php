<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\SnippetApiResource;
use App\Models\Snippet;

class PublicSnippetApiController extends Controller
{
    public function index()
    {
        $snippets = Snippet::where('is_public', true)
            ->with('user', 'tags')
            ->latest()
            ->paginate(15);

        return SnippetApiResource::collection($snippets);
    }

    public function show(Snippet $snippet)
    {
        abort_if(!$snippet->is_public, 404);

        $snippet->load('user', 'tags');

        return new SnippetApiResource($snippet);
    }
}