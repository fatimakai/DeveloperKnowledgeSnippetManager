<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PromptApiResource;
use App\Models\Prompt;
use App\Models\Tag;
use App\Services\PromptVersionService;
use App\Support\PromptInput;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class PromptApiController extends Controller
{
    public function index(Request $request)
    {
        $query = Prompt::query()->where(function ($scope) use ($request): void {
            $scope->where('user_id', $request->user()->id);
            if ($request->user()->isPro()) {
                $scope->orWhereHas('collection.memberships', fn ($members) => $members->where('user_id', $request->user()->id));
            }
        });
        $this->applyFilters($query, $request);

        return PromptApiResource::collection(
            $query->with(['user', 'tags', 'collection'])->withCount(['upvotes', 'versions'])->latest()->paginate(15)
        );
    }

    public function show(Prompt $prompt): PromptApiResource
    {
        Gate::authorize('view', $prompt);

        return new PromptApiResource($prompt->load(['user', 'tags', 'collection'])->loadCount(['upvotes', 'versions']));
    }

    public function store(Request $request, PromptVersionService $versions): JsonResponse
    {
        $data = PromptInput::normalize($request->validate(PromptInput::apiRules()));

        $prompt = DB::transaction(function () use ($data, $request, $versions): Prompt {
            $prompt = Prompt::create([
                ...collect($data)->except('tags')->all(),
                'user_id' => $request->user()->id,
                'visibility' => $data['visibility'] ?? Prompt::VISIBILITY_PRIVATE,
            ]);
            $this->syncTags($prompt, $data['tags'] ?? []);
            $versions->record($prompt, $request->user());

            return $prompt;
        });

        return (new PromptApiResource($prompt->load(['user', 'tags'])->loadCount(['upvotes', 'versions'])))
            ->response()
            ->setStatusCode(201);
    }

    public function update(Request $request, Prompt $prompt, PromptVersionService $versions): PromptApiResource
    {
        Gate::authorize('update', $prompt);
        $data = PromptInput::normalize($request->validate(PromptInput::apiRules(partial: true)));

        DB::transaction(function () use ($data, $prompt, $request, $versions): void {
            $prompt->update(collect($data)->except('tags')->all());
            if (array_key_exists('tags', $data)) {
                $this->syncTags($prompt, $data['tags']);
            }
            $versions->record($prompt, $request->user());
        });

        return new PromptApiResource($prompt->refresh()->load(['user', 'tags'])->loadCount(['upvotes', 'versions']));
    }

    public function destroy(Prompt $prompt)
    {
        Gate::authorize('delete', $prompt);
        $prompt->delete();

        return response()->noContent();
    }

    private function syncTags(Prompt $prompt, array $names): void
    {
        $ids = collect($names)
            ->map(fn (string $name) => trim(mb_strtolower($name)))
            ->filter()
            ->unique()
            ->map(fn (string $name) => Tag::firstOrCreate(['name' => $name])->id);
        $prompt->tags()->sync($ids);
    }

    private function applyFilters($query, Request $request): void
    {
        $query
            ->when($request->string('search')->trim()->isNotEmpty(), fn ($q) => $q->where('title', 'like', '%'.$request->string('search')->trim().'%'))
            ->when($request->filled('target_model'), fn ($q) => $q->where('target_model', $request->input('target_model')))
            ->when($request->filled('tag'), fn ($q) => $q->whereHas('tags', fn ($tags) => $tags->where('name', $request->input('tag'))))
            ->when($request->filled('visibility'), fn ($q) => $q->where('visibility', $request->input('visibility')));
    }
}
