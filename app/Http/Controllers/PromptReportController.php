<?php

namespace App\Http\Controllers;

use App\Models\Prompt;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PromptReportController extends Controller
{
    public function store(Request $request, Prompt $prompt): RedirectResponse
    {
        Gate::authorize('report', $prompt);
        $data = $request->validate([
            'reason' => ['required', 'in:spam,harmful,misleading,copyright,other'],
            'details' => ['nullable', 'string', 'max:2000'],
        ]);
        $prompt->reports()->updateOrCreate(
            ['reported_by' => $request->user()->id],
            [...$data, 'details' => $data['details'] ?: null, 'status' => 'pending', 'reviewed_by' => null, 'reviewed_at' => null],
        );

        return back()->with('success', 'Thanks. The prompt was sent to the moderation queue.');
    }
}
