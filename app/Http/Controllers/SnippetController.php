<?php

namespace App\Http\Controllers;
use App\Models\Snippet; 
use Illuminate\Http\Request;
use App\Models\Tag;


class SnippetController extends Controller
{

    public function index()
    {
        $snippets = Snippet::where('user_id', auth()->id())->get();

        return view('snippets.index', compact('snippets'));
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
    ]);

    $snippet->update([
        'title'    => $validated['title'],
        'language' => $validated['language'],
        'code'     => $validated['code'],
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
        'tags'     => 'nullable|string', // just a single string input: "php, laravel"
    ]);

    $snippet = Snippet::create([
        'title'    => $validated['title'],
        'language' => $validated['language'],
        'code'     => $validated['code'],
        'user_id'  => auth()->id(),
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
