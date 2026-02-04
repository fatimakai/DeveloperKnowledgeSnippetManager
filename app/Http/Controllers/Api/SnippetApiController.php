<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Snippet;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SnippetApiController extends Controller
{
    public function index(Request $request)
    {
        $query = Snippet::where('user_id', Auth::id());

        if ($request->filled('search')) {
            $query->where('title', 'like', "%{$request->input('search')}%");
        }
        if ($request->filled('language')) {
            $query->where('language', $request->input('language'));
        }
        if ($request->filled('tag')) {
            $query->whereHas('tags', function ($q) use ($request) {
                $q->where('name', $request->input('tag'));
            });
        }
        if ($request->filled('visibility')) {
            if ($request->input('visibility') === 'public') {
                $query->where('is_public', true);
            } elseif ($request->input('visibility') === 'private') {
                $query->where([
                    ['is_public', false],
                    ['user_id', Auth::id()]
                ]);
            }
        }
        $snippets = $query->with('user', 'tags')->paginate(15);
        return response()->json($snippets);
    }

    public function show(Snippet $snippet)
    {
        if (!$snippet->is_public && $snippet->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $snippet->load('user', 'tags');
        return response()->json($snippet);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'language'    => 'required|string|max:50',
            'code'        => 'required|string',
            'tags'        => 'nullable|string',
            'is_public'   => 'nullable|boolean',
        ]);

        $snippet = Snippet::create([
            'title'       => $validated['title'],
            'description' => $validated['description'] ?? null,
            'language'    => $validated['language'],
            'code'        => $validated['code'],
            'user_id'     => Auth::id(),
            'is_public'   => $request->has('is_public'),
        ]);

        if (!empty($validated['tags'])) {
            $tagsArray = array_map('trim', explode(',', $validated['tags']));
            $tagIds = collect($tagsArray)->map(function ($tagName) {
                return Tag::firstOrCreate(['name' => $tagName])->id;
            });
            $snippet->tags()->sync($tagIds);
        }

        $snippet->load('user', 'tags');
        return response()->json($snippet, 201);
    }

    public function update(Request $request, Snippet $snippet)
    {
        $this->authorize('update', $snippet);

        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'language'    => 'required|string|max:50',
            'code'        => 'required|string',
            'tags'        => 'nullable|string',
            'is_public'   => 'nullable|boolean',
        ]);

        $snippet->update([
            'title'       => $validated['title'],
            'description' => $validated['description'] ?? null,
            'language'    => $validated['language'],
            'code'        => $validated['code'],
            'is_public'   => $request->has('is_public'),
        ]);

        if (!empty($validated['tags'])) {
            $tagsArray = array_map('trim', explode(',', $validated['tags']));
            $tagIds = collect($tagsArray)->map(function ($tagName) {
                return Tag::firstOrCreate(['name' => $tagName])->id;
            });
            $snippet->tags()->sync($tagIds);
        } else {
            $snippet->tags()->detach();
        }

        $snippet->load('user', 'tags');
        return response()->json($snippet);
    }

    public function destroy(Snippet $snippet)
    {
        $this->authorize('destroy', $snippet);

        $snippet->tags()->detach();
        $snippet->delete();
        return response()->json(['message' => 'Snippet deleted']);
    }
}