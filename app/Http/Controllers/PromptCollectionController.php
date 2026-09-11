<?php

namespace App\Http\Controllers;

use App\Models\Prompt;
use App\Models\PromptCollection;
use App\Models\User;
use App\Rules\SafeText;
use App\Services\CollectionAuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PromptCollectionController extends Controller
{
    public function __construct(private readonly CollectionAuditLogger $audit) {}

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
            'name' => ['required', 'string', 'max:120', new SafeText(multiline: false)],
            'description' => ['nullable', 'string', 'max:2000', new SafeText],
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
            $this->audit->record($collection, $request->user(), 'collection.created');

            return $collection;
        });

        return to_route('collections.show', $collection)->with('success', 'Collection created.');
    }

    public function show(Request $request, PromptCollection $collection): View
    {
        Gate::authorize('view', $collection);
        $this->audit->recordAccess($collection, $request->user());
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
            'name' => ['required', 'string', 'max:120', new SafeText(multiline: false)],
            'description' => ['nullable', 'string', 'max:2000', new SafeText],
        ]);
        DB::transaction(function () use ($collection, $data, $request): void {
            $collection->update([...$data, 'description' => $data['description'] ?: null]);
            $this->audit->record($collection, $request->user(), 'collection.updated');
        });

        return back()->with('success', 'Collection updated.');
    }

    public function destroy(Request $request, PromptCollection $collection): RedirectResponse
    {
        Gate::authorize('delete', $collection);
        DB::transaction(function () use ($collection, $request): void {
            $this->audit->record($collection, $request->user(), 'collection.deleted');
            $collection->delete();
        });

        return to_route('collections.index')->with('success', 'Collection deleted. Its prompts are personal again.');
    }

    public function invite(Request $request, PromptCollection $collection): RedirectResponse
    {
        Gate::authorize('manageMembers', $collection);
        $data = $request->validate(['role' => ['required', 'in:editor,viewer']]);
        $token = Str::random(64);
        DB::transaction(function () use ($collection, $data, $request, $token): void {
            $collection->update([
                'invite_token_hash' => hash('sha256', $token),
                'invite_role' => $data['role'],
                'invite_expires_at' => now()->addDays(7),
            ]);
            $this->audit->record($collection, $request->user(), 'invite.created', metadata: [
                'role' => $data['role'],
                'expires_at' => $collection->invite_expires_at?->toIso8601String(),
            ]);
        });

        return back()->with('success', 'A new seven-day invite link was created.')
            ->with('invite_url', route('collections.invites.show', $token));
    }

    public function revokeInvite(Request $request, PromptCollection $collection): RedirectResponse
    {
        Gate::authorize('manageMembers', $collection);
        DB::transaction(function () use ($collection, $request): void {
            $collection->update(['invite_token_hash' => null, 'invite_expires_at' => null]);
            $this->audit->record($collection, $request->user(), 'invite.revoked');
        });

        return back()->with('success', 'Invite link revoked.');
    }

    public function join(Request $request, string $token): RedirectResponse
    {
        $collection = $this->collectionForToken($token);

        DB::transaction(function () use ($collection, $request): void {
            $membership = $collection->memberships()->firstOrCreate(
                ['user_id' => $request->user()->id],
                ['role' => $collection->invite_role, 'joined_at' => now()],
            );

            if ($membership->wasRecentlyCreated) {
                $this->audit->record(
                    $collection,
                    $request->user(),
                    'member.joined',
                    targetUser: $request->user(),
                    metadata: ['role' => $membership->role],
                );
            }
        });

        return to_route('collections.show', $collection)->with('success', 'You joined the collection.');
    }

    public function showInvite(Request $request, string $token): View
    {
        $collection = $this->collectionForToken($token);
        $this->audit->recordOnce($collection, $request->user(), 'invite.viewed');

        return view('collections.invite', compact('collection', 'token'));
    }

    public function updateMember(Request $request, PromptCollection $collection, int $user): RedirectResponse
    {
        Gate::authorize('manageMembers', $collection);
        abort_if($collection->owner_id === $user, 422, 'The collection owner role cannot be changed.');
        $data = $request->validate(['role' => ['required', 'in:editor,viewer']]);
        DB::transaction(function () use ($collection, $user, $data, $request): void {
            $membership = $collection->memberships()->where('user_id', $user)->firstOrFail();
            $oldRole = $membership->role;
            $membership->update($data);

            if ($oldRole !== $data['role']) {
                $this->audit->record(
                    $collection,
                    $request->user(),
                    'member.role_changed',
                    targetUser: User::findOrFail($user),
                    metadata: ['old_role' => $oldRole, 'new_role' => $data['role']],
                );
            }
        });

        return back()->with('success', 'Member role updated.');
    }

    public function removeMember(Request $request, PromptCollection $collection, int $user): RedirectResponse
    {
        Gate::authorize('manageMembers', $collection);
        abort_if($collection->owner_id === $user, 422, 'The collection owner cannot be removed.');
        DB::transaction(function () use ($collection, $user, $request): void {
            $collection->memberships()->where('user_id', $user)->firstOrFail()->delete();
            $this->audit->record($collection, $request->user(), 'member.removed', targetUser: User::findOrFail($user));
        });

        return back()->with('success', 'Member removed.');
    }

    public function leave(Request $request, PromptCollection $collection): RedirectResponse
    {
        Gate::authorize('view', $collection);
        abort_if($collection->owner_id === $request->user()->id, 422, 'Owners must delete the collection instead.');
        DB::transaction(function () use ($collection, $request): void {
            $collection->memberships()->where('user_id', $request->user()->id)->delete();
            $this->audit->record($collection, $request->user(), 'member.left', targetUser: $request->user());
        });

        return to_route('collections.index')->with('success', 'You left the collection.');
    }

    public function addPrompt(Request $request, PromptCollection $collection): RedirectResponse
    {
        Gate::authorize('addPrompt', $collection);
        $data = $request->validate(['prompt_id' => ['required', 'integer']]);
        $prompt = Prompt::query()->whereKey($data['prompt_id'])
            ->where('user_id', $request->user()->id)->whereNull('collection_id')->firstOrFail();
        DB::transaction(function () use ($prompt, $collection, $request): void {
            $prompt->update(['collection_id' => $collection->id, 'visibility' => Prompt::VISIBILITY_PRIVATE]);
            $this->audit->record($collection, $request->user(), 'prompt.added', prompt: $prompt);
        });

        return back()->with('success', 'Prompt added to the collection.');
    }

    public function removePrompt(Request $request, PromptCollection $collection, Prompt $prompt): RedirectResponse
    {
        Gate::authorize('addPrompt', $collection);
        abort_unless($prompt->collection_id === $collection->id, 404);
        abort_unless($prompt->user_id === $request->user()->id || $collection->owner_id === $request->user()->id, 403);
        DB::transaction(function () use ($collection, $request, $prompt): void {
            $this->audit->record($collection, $request->user(), 'prompt.removed', prompt: $prompt);
            $prompt->update(['collection_id' => null]);
        });

        return back()->with('success', 'Prompt removed from the collection.');
    }

    private function collectionForToken(string $token): PromptCollection
    {
        $collection = PromptCollection::query()->where('invite_token_hash', hash('sha256', $token))->first();
        abort_unless($collection && $collection->invite_expires_at?->isFuture(), 404, 'This invite is invalid or expired.');

        return $collection;
    }
}
