<?php

namespace App\Http\Controllers;

use App\Models\Prompt;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PromptController extends Controller
{
    public function index(): View
    {
        return view('prompts.index');
    }

    public function mine(): View
    {
        return view('prompts.mine');
    }

    public function bookmarked(): View
    {
        return view('prompts.bookmarked');
    }

    public function create(): View
    {
        return view('prompts.create');
    }

    public function show(Prompt $prompt): View
    {
        Gate::authorize('view', $prompt);
        $prompt->load(['user', 'tags', 'collection'])->loadCount(['upvotes', 'versions']);

        return view('prompts.show', compact('prompt'));
    }

    public function edit(Prompt $prompt): View
    {
        Gate::authorize('update', $prompt);

        return view('prompts.edit', compact('prompt'));
    }
}
