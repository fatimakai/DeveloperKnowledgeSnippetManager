<?php

use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\PromptAnalysisApiController;
use App\Http\Controllers\Api\PromptApiController;
use App\Http\Controllers\Api\PublicPromptApiController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthApiController::class, 'login'])->middleware('throttle:api-login');

Route::get('/public/prompts', [PublicPromptApiController::class, 'index']);
Route::get('/public/prompts/{prompt:slug}', [PublicPromptApiController::class, 'show']);

Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('/logout', [AuthApiController::class, 'logout']);
    Route::get('/prompts/{prompt}/analyses', [PromptAnalysisApiController::class, 'index'])->name('api.prompts.analyses.index');
    Route::post('/prompts/{prompt}/analyses', [PromptAnalysisApiController::class, 'store'])->name('api.prompts.analyses.store');
    Route::get('/prompts/{prompt}/analyses/{analysis}', [PromptAnalysisApiController::class, 'show'])->name('api.prompts.analyses.show');
    Route::apiResource('prompts', PromptApiController::class)
        ->parameters(['prompts' => 'prompt'])
        ->names('api.prompts');
});
