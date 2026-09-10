<?php

use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\PromptApiController;
use App\Http\Controllers\Api\PublicPromptApiController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthApiController::class, 'login'])->middleware('throttle:api-login');

Route::get('/public/prompts', [PublicPromptApiController::class, 'index']);
Route::get('/public/prompts/{prompt:slug}', [PublicPromptApiController::class, 'show']);

Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('/logout', [AuthApiController::class, 'logout']);
    Route::apiResource('prompts', PromptApiController::class)
        ->parameters(['prompts' => 'prompt'])
        ->names('api.prompts');
});
