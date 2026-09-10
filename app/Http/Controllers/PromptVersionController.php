<?php

namespace App\Http\Controllers;

use App\Models\Prompt;
use App\Models\PromptVersion;
use App\Models\Tag;
use App\Services\PromptVersionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PromptVersionController extends Controller
{
    public function index(Prompt $prompt): View
    {
        Gate::authorize('viewHistory', $prompt);

        $versions = $prompt->versions()
            ->with('creator')
            ->latest('version_number')
            ->paginate(20);
        $currentVersionNumber = $prompt->versions()->max('version_number');

        return view('prompts.history.index', compact('prompt', 'versions', 'currentVersionNumber'));
    }

    public function show(Prompt $prompt, PromptVersion $version): View
    {
        Gate::authorize('viewHistory', $prompt);
        $this->ensureVersionBelongsToPrompt($prompt, $version);

        $prompt->load('tags');
        $version->load('creator');
        $current = app(PromptVersionService::class)->snapshot($prompt);
        $isCurrent = $version->snapshot() === $current;

        return view('prompts.history.show', compact('prompt', 'version', 'current', 'isCurrent'));
    }

    public function restore(Prompt $prompt, PromptVersion $version, PromptVersionService $versions): RedirectResponse
    {
        Gate::authorize('restoreVersion', $prompt);
        $this->ensureVersionBelongsToPrompt($prompt, $version);

        DB::transaction(function () use ($prompt, $version, $versions): void {
            $lockedPrompt = Prompt::query()->whereKey($prompt->id)->lockForUpdate()->firstOrFail();
            $snapshot = $version->snapshot();

            $lockedPrompt->update(collect($snapshot)->except('tags')->all());
            $tagIds = collect($snapshot['tags'])
                ->map(fn (string $name) => Tag::firstOrCreate(['name' => $name])->id);
            $lockedPrompt->tags()->sync($tagIds);

            $versions->record(
                $lockedPrompt,
                auth()->user(),
                'Restored from version '.$version->version_number
            );
        });

        return redirect()
            ->route('prompts.show', $prompt)
            ->with('success', 'Version '.$version->version_number.' restored.');
    }

    private function ensureVersionBelongsToPrompt(Prompt $prompt, PromptVersion $version): void
    {
        abort_unless($version->prompt_id === $prompt->id, 404);
    }
}
