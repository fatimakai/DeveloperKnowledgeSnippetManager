<?php

namespace App\Http\Controllers;

use App\Models\Prompt;
use App\Models\PromptCollection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PromptCollectionController extends Controller
{
    public function index(Request $request): View
    {
        $collections = $request->user()->collections()
            ->with('owner')->withCount(['prompts', 'members'])->latest('prompt_collections.updated_at')->paginate(12);

        return view('collections.index', compact('collections'));
    }

    public function create(): View
    {
        return view('collections.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        $collection = DB::transaction(function () use ($data, $request): PromptCollection {
            $collection = PromptCollection::create([
                ...$data,
                'description' => $data['description'] ?: null,
                'owner_id' => $request->user()->id,
            ]);
            $collection->memberships()->create([
                'user_id' => $request->user()->id,
                'role' => PromptCollection::ROLE_OWNER,
                'joined_at' => now(),
            ]);

            return $collection;
        });

        return to_route('collections.show', $collection)->with('success', 'Collection created.');
    }

    public function show(Request $request, PromptCollection $collection): View
    {
        Gate::authorize('view', $collection);
        $collection->load(['owner', 'memberships.user', 'prompts.user', 'prompts.tags']);
        $membership = $collection->membershipFor($request->user());
        $personalPrompts = Gate::allows('addPrompt', $collection)
            ? $request->user()->prompts()->whereNull('collection_id')->latest()->get(['id', 'title', 'slug'])
            : collect();

        return view('collections.show', compact('collection', 'membership', 'personalPrompts'));
    }

    public function update(Request $request, PromptCollection $collection): RedirectResponse
    {
        Gate::authorize('update', $collection);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);
        $collection->update([...$data, 'description' => $data['description'] ?: null]);

        return back()->with('success', 'Collection updated.');
    }

    public function destroy(PromptCollection $collection): RedirectResponse
    {
        Gate::authorize('delete', $collection);
        $collection->delete();

        return to_route('collections.index')->with('success', 'Collection deleted. Its prompts are personal again.');
    }

    public function invite(Request $request, PromptCollection $collection): RedirectResponse
    {
        Gate::authorize('manageMembers', $collection);
        $data = $request->validate(['role' => ['required', 'in:editor,viewer']]);
        $token = Str::random(64);
        $collection->update([
            'invite_token_hash' => hash('sha256', $token),
            'invite_role' => $data['role'],
            'invite_expires_at' => now()->addDays(7),
        ]);

        return back()->with('success', 'A new seven-day invite link was created.')
            ->with('invite_url', route('collections.invites.show', $token));
    }

    public function revokeInvite(PromptCollection $collection): RedirectResponse
    {
        Gate::authorize('manageMembers', $collection);
        $collection->update(['invite_token_hash' => null, 'invite_expires_at' => null]);

        return back()->with('success', 'Invite link revoked.');
    }

    public function join(Request $request, string $token): RedirectResponse
    {
        $collection = $this->collectionForToken($token);

        $collection->memberships()->firstOrCreate(
            ['user_id' => $request->user()->id],
            ['role' => $collection->invite_role, 'joined_at' => now()],
        );

        return to_route('collections.show', $collection)->with('success', 'You joined the collection.');
    }

    public function showInvite(string $token): View
    {
        $collection = $this->collectionForToken($token);

        return view('collections.invite', compact('collection', 'token'));
    }

    public function updateMember(Request $request, PromptCollection $collection, int $user): RedirectResponse
    {
        Gate::authorize('manageMembers', $collection);
        abort_if($collection->owner_id === $user, 422, 'The collection owner role cannot be changed.');
        $data = $request->validate(['role' => ['required', 'in:editor,viewer']]);
        $collection->memberships()->where('user_id', $user)->firstOrFail()->update($data);

        return back()->with('success', 'Member role updated.');
    }

    public function removeMember(PromptCollection $collection, int $user): RedirectResponse
    {
        Gate::authorize('manageMembers', $collection);
        abort_if($collection->owner_id === $user, 422, 'The collection owner cannot be removed.');
        $collection->memberships()->where('user_id', $user)->firstOrFail()->delete();

        return back()->with('success', 'Member removed.');
    }

    public function leave(Request $request, PromptCollection $collection): RedirectResponse
    {
        Gate::authorize('view', $collection);
        abort_if($collection->owner_id === $request->user()->id, 422, 'Owners must delete the collection instead.');
        $collection->memberships()->where('user_id', $request->user()->id)->delete();

        return to_route('collections.index')->with('success', 'You left the collection.');
    }

    public function addPrompt(Request $request, PromptCollection $collection): RedirectResponse
    {
        Gate::authorize('addPrompt', $collection);
        $data = $request->validate(['prompt_id' => ['required', 'integer']]);
        $prompt = Prompt::query()->whereKey($data['prompt_id'])
            ->where('user_id', $request->user()->id)->whereNull('collection_id')->firstOrFail();
        $prompt->update(['collection_id' => $collection->id, 'visibility' => Prompt::VISIBILITY_PRIVATE]);

        return back()->with('success', 'Prompt added to the collection.');
    }

    public function removePrompt(Request $request, PromptCollection $collection, Prompt $prompt): RedirectResponse
    {
        Gate::authorize('addPrompt', $collection);
        abort_unless($prompt->collection_id === $collection->id, 404);
        abort_unless($prompt->user_id === $request->user()->id || $collection->owner_id === $request->user()->id, 403);
        $prompt->update(['collection_id' => null]);

        return back()->with('success', 'Prompt removed from the collection.');
    }

    private function collectionForToken(string $token): PromptCollection
    {
        $collection = PromptCollection::query()->where('invite_token_hash', hash('sha256', $token))->first();
        abort_unless($collection && $collection->invite_expires_at?->isFuture(), 404, 'This invite is invalid or expired.');

        return $collection;
    }
}
