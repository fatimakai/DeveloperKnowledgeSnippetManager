<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\PromptAnalysisException;
use App\Http\Controllers\Controller;
use App\Http\Resources\PromptAnalysisApiResource;
use App\Models\Prompt;
use App\Models\PromptAnalysis;
use App\Services\PromptAnalysisDispatcher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PromptAnalysisApiController extends Controller
{
    public function index(Prompt $prompt)
    {
        Gate::authorize('analyze', $prompt);

        return PromptAnalysisApiResource::collection(
            $prompt->analyses()->latest()->paginate(15)
        );
    }

    public function store(Request $request, Prompt $prompt, PromptAnalysisDispatcher $dispatcher): JsonResponse
    {
        Gate::authorize('analyze', $prompt);

        try {
            $analysis = $dispatcher->dispatch($prompt, $request->user());
        } catch (PromptAnalysisException $exception) {
            return response()->json(['message' => $exception->getMessage()], $exception->status);
        }

        return (new PromptAnalysisApiResource($analysis->refresh()))
            ->response()
            ->setStatusCode(202);
    }

    public function show(Prompt $prompt, PromptAnalysis $analysis): PromptAnalysisApiResource
    {
        Gate::authorize('analyze', $prompt);
        abort_unless($analysis->prompt_id === $prompt->id, 404);

        return new PromptAnalysisApiResource($analysis);
    }
}
