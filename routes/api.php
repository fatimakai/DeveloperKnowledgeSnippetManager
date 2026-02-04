<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SnippetApiController;
use App\Http\Controllers\Api\PublicSnippetApiController;
use App\Http\Controllers\Api\AuthApiController;

// Public auth endpoints
Route::post('login', [AuthApiController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthApiController::class, 'logout']);
    Route::get('snippets', [SnippetApiController::class, 'index']);
    Route::get('snippets/{snippet}', [SnippetApiController::class, 'show']);
    Route::post('snippets', [SnippetApiController::class, 'store']);
    Route::put('snippets/{snippet}', [SnippetApiController::class, 'update']);
    Route::delete('snippets/{snippet}', [SnippetApiController::class, 'destroy']);
});

// Public read-only API
Route::get('public/snippets', [PublicSnippetApiController::class, 'index']);
Route::get('public/snippets/{snippet:slug}', [PublicSnippetApiController::class, 'show']);