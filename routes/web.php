<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SnippetController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\ExportController;



Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::get('/tags/autocomplete', [TagController::class, 'autocomplete'])->name('tags.autocomplete');

    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Export routes (must come first before resource routes)
    Route::get('/snippets/export-all-json', [ExportController::class, 'exportAllJson'])->name('snippets.export.all.json');
    Route::get('/snippets/export-all-pdf', [ExportController::class, 'exportAllPdf'])->name('snippets.export.all.pdf');
    Route::post('/snippets/export-bulk-json', [ExportController::class, 'exportBulkJson'])->name('snippets.export.bulk.json');
    Route::post('/snippets/export-bulk-pdf', [ExportController::class, 'exportBulkPdf'])->name('snippets.export.bulk.pdf');
    Route::get('/snippets/{snippet}/export-json', [ExportController::class, 'exportSnippetJson'])->name('snippets.export.json');
    Route::get('/snippets/{snippet}/export-pdf', [ExportController::class, 'exportSnippetPdf'])->name('snippets.export.pdf');
    
    // Snippet routes
    Route::get('/snippets/my', [SnippetController::class, 'mySnippets'])->name('snippets.my');
    Route::resource('snippets', SnippetController::class);
});

require __DIR__.'/auth.php';
