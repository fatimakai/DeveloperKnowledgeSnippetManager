<?php

namespace App\Http\Controllers;

use App\Models\Prompt;
use App\Models\PromptReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ModerationController extends Controller
{
    public function index(): View
    {
        $reports = PromptReport::query()->where('status', PromptReport::STATUS_PENDING)
            ->with(['prompt.user', 'reporter'])->oldest()->paginate(20);

        return view('moderation.index', compact('reports'));
    }

    public function dismiss(Request $request, PromptReport $report): RedirectResponse
    {
        $report->update(['status' => PromptReport::STATUS_DISMISSED, 'reviewed_by' => $request->user()->id, 'reviewed_at' => now()]);

        return back()->with('success', 'Report dismissed.');
    }

    public function hide(Request $request, PromptReport $report): RedirectResponse
    {
        DB::transaction(function () use ($request, $report): void {
            $report->prompt()->update(['visibility' => Prompt::VISIBILITY_PRIVATE, 'collection_id' => null]);
            PromptReport::query()->where('prompt_id', $report->prompt_id)->where('status', PromptReport::STATUS_PENDING)
                ->update(['status' => PromptReport::STATUS_RESOLVED, 'reviewed_by' => $request->user()->id, 'reviewed_at' => now()]);
        });

        return back()->with('success', 'Prompt hidden and open reports resolved.');
    }
}
