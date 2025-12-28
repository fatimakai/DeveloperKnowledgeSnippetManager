<?php

namespace App\Http\Controllers;
use App\Models\Snippet; 
use Illuminate\Http\Request;
use App\Models\Tag;


class SnippetController extends Controller
{

    public function index(Request $request)
    {
        // Base query: all public snippets + user's own snippets
        $query = Snippet::where(function ($q) {
            $q->where('is_public', true)
              ->orWhere('user_id', auth()->id());
        });

        // Search by title only (not code - too slow)
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('title', 'like', "%{$search}%");
        }

        // Filter by language
        if ($request->filled('language')) {
            $query->where('language', $request->input('language'));
        }

        // Filter by tag
        if ($request->filled('tag')) {
            $query->whereHas('tags', function ($q) {
                $q->where('name', $request->input('tag'));
            });
        }

        // Filter by visibility (only for user's own snippets)
        if ($request->filled('visibility')) {
            if ($request->input('visibility') === 'public') {
                $query->where('is_public', true);
            } elseif ($request->input('visibility') === 'private') {
                $query->where([
                    ['is_public', false],
                    ['user_id', auth()->id()]
                ]);
            }
        }

        // Eager load relationships to avoid N+1 queries
        $snippets = $query->with('user', 'tags')->paginate(15);
        
        // Get all available languages for filter dropdowns - only those visible to user
        $languages = Snippet::where(function ($q) {
            $q->where('is_public', true)
              ->orWhere('user_id', auth()->id());
        })
        ->select('language')
        ->distinct()
        ->orderBy('language')
        ->pluck('language');
        
        // Get only tags that are actually used (not all tags)
        $tags = Tag::whereHas('snippets', function ($q) {
            $q->where(function ($inner) {
                $inner->where('is_public', true)
                      ->orWhere('user_id', auth()->id());
            });
        })
        ->select('id', 'name')
        ->orderBy('name')
        ->get();

        return view('snippets.index', compact('snippets', 'languages', 'tags'));
    }

    public function mySnippets(Request $request)
    {
        // Base query: only the logged-in user's snippets
        $query = Snippet::where('user_id', auth()->id());

        // Search by title only (not code - too slow)
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('title', 'like', "%{$search}%");
        }

        // Filter by language
        if ($request->filled('language')) {
            $query->where('language', $request->input('language'));
        }

        // Filter by tag
        if ($request->filled('tag')) {
            $query->whereHas('tags', function ($q) {
                $q->where('name', $request->input('tag'));
            });
        }

        // Filter by visibility
        if ($request->filled('visibility')) {
            if ($request->input('visibility') === 'public') {
                $query->where('is_public', true);
            } elseif ($request->input('visibility') === 'private') {
                $query->where('is_public', false);
            }
        }

        // Eager load relationships and paginate
        $snippets = $query->with('user', 'tags')->paginate(15);

        // Get all available languages for this user
        $languages = Snippet::where('user_id', auth()->id())
            ->select('language')
            ->distinct()
            ->orderBy('language')
            ->pluck('language');
        
        // Get only tags used by this user
        $tags = Tag::whereHas('snippets', function ($q) {
            $q->where('user_id', auth()->id());
        })
        ->select('id', 'name')
        ->orderBy('name')
        ->get();

        return view('snippets.my', compact('snippets', 'languages', 'tags'));
    }

    public function create()
    {
        return view('snippets.create');
    }


public function update(Request $request, Snippet $snippet)
{
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

    return redirect()->route('snippets.index')->with('success', 'Snippet updated!');
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
            'user_id'     => auth()->id(),
            'is_public'   => $request->has('is_public'),
        ]);

        if (!empty($validated['tags'])) {
            $tagsArray = array_map('trim', explode(',', $validated['tags']));
            $tagIds = collect($tagsArray)->map(function ($tagName) {
                return Tag::firstOrCreate(['name' => $tagName])->id;
            });
            $snippet->tags()->sync($tagIds);
        }

        return redirect()->route('snippets.index')->with('success', 'Snippet created!');
    }

public function edit(Snippet $snippet)
{
    $snippet->load('tags');
    return view('snippets.edit', compact('snippet'));
}

    public function destroy(string $id)
    {
        //
    }
}
