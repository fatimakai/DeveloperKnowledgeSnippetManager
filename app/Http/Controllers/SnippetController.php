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

        // Search by title or code
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
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

        $snippets = $query->with('user', 'tags')->get();
        
        // Get all available languages and tags for filter dropdowns
        $languages = Snippet::where(function ($q) {
            $q->where('is_public', true)
              ->orWhere('user_id', auth()->id());
        })->distinct()->pluck('language')->sort();
        
        $tags = Tag::all();

        return view('snippets.index', compact('snippets', 'languages', 'tags'));
    }

    public function mySnippets(Request $request)
    {
        // Base query: only the logged-in user's snippets
        $query = Snippet::where('user_id', auth()->id());

        // Search by title or code
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
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

        $snippets = $query->with('user', 'tags')->get();

        // Get all available languages and tags for filter dropdowns
        $languages = Snippet::where('user_id', auth()->id())
            ->distinct()
            ->pluck('language')
            ->sort();
        
        $tags = Tag::all();

        return view('snippets.my', compact('snippets', 'languages', 'tags'));
    }

    public function create()
    {
        return view('snippets.create');
    }


public function update(Request $request, Snippet $snippet)
{
    $validated = $request->validate([
        'title'    => 'required|string|max:255',
        'language' => 'required|string|max:50',
        'code'     => 'required|string',
        'tags'     => 'nullable|string',
        'is_public' => 'nullable|boolean',
    ]);

    $snippet->update([
        'title'    => $validated['title'],
        'language' => $validated['language'],
        'code'     => $validated['code'],
        'is_public' => $request->has('is_public'),
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
        'title'    => 'required|string|max:255',
        'language' => 'required|string|max:50',
        'code'     => 'required|string',
        'tags'     => 'nullable|string',
        'is_public' => 'nullable|boolean',
    ]);

    $snippet = Snippet::create([
        'title'    => $validated['title'],
        'language' => $validated['language'],
        'code'     => $validated['code'],
        'user_id'  => auth()->id(),
        'is_public' => $request->has('is_public'),
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

    public function show($id)
    {
        return view('snippets.show');
    }

public function edit(Snippet $snippet)
{        $snippet->load('tags');

    // dd($snippet->tags);


    return view('snippets.edit', compact('snippet'));
}



    /**
     * Update the specified resource in storage.
     */


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
