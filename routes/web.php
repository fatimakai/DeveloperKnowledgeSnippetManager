<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PromptController;
use App\Http\Controllers\PromptExportController;
use App\Models\Prompt;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::get('/prompts', [PromptController::class, 'index'])->name('prompts.index');

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('/dashboard', function () {
        $topPrompts = Prompt::query()
            ->where('visibility', Prompt::VISIBILITY_PUBLIC)
            ->with(['user', 'tags'])
            ->withCount('upvotes')
            ->orderByDesc('upvotes_count')
            ->latest()
            ->limit(5)
            ->get();

        $topCreators = User::query()
            ->whereHas('prompts', fn ($query) => $query->where('visibility', Prompt::VISIBILITY_PUBLIC))
            ->withCount(['prompts' => fn ($query) => $query->where('visibility', Prompt::VISIBILITY_PUBLIC)])
            ->orderByDesc('prompts_count')
            ->limit(5)
            ->get();

        return view('dashboard', compact('topPrompts', 'topCreators'));
    })->name('dashboard');

    Route::get('/prompts/mine', [PromptController::class, 'mine'])->name('prompts.mine');
    Route::get('/prompts/bookmarked', [PromptController::class, 'bookmarked'])->name('prompts.bookmarked');
    Route::get('/prompts/create', [PromptController::class, 'create'])->name('prompts.create');
    Route::get('/prompts/{prompt:slug}/edit', [PromptController::class, 'edit'])->name('prompts.edit');
    Route::get('/exports/prompts.json', [PromptExportController::class, 'all'])->name('prompts.export.all');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/prompts/{prompt:slug}', [PromptController::class, 'show'])->name('prompts.show');
Route::get('/prompts/{prompt:slug}/export', [PromptExportController::class, 'show'])->name('prompts.export');

require __DIR__.'/auth.php';
