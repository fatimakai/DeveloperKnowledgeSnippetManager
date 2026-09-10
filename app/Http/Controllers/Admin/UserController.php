<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\PlatformRoleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::query()->with('roles')->latest()->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate(['role' => ['required', Rule::in(PlatformRoleService::ROLES)]]);
        abort_if($request->user()->is($user) && $data['role'] !== PlatformRoleService::ADMIN, 422, 'You cannot remove your own administrator access.');
        app(PlatformRoleService::class)->ensure();
        $user->syncRoles([$data['role']]);

        return back()->with('success', "{$user->name}'s role was updated.");
    }
}
