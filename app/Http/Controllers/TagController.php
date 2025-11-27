<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tag;

class TagController extends Controller
{
    public function autocomplete(Request $request)
    {
        $search = $request->get('query', '');

        $tags = Tag::where('name', 'like', "%{$search}%")
                    ->limit(10)
                    ->get(['id', 'name']);

        return response()->json($tags);
    }
}
