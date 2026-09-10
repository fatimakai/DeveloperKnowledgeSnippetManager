<x-app-layout>
    <x-slot name="header"><h1 class="text-2xl font-bold text-gray-900 dark:text-white">User administration</h1></x-slot>
    <div class="mx-auto max-w-6xl px-4 py-10 sm:px-6 lg:px-8">
        @if(session('success'))<div class="mb-6 rounded-lg bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('success') }}</div>@endif
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <table class="min-w-full divide-y divide-gray-200 text-left text-sm dark:divide-gray-700"><thead class="bg-gray-50 dark:bg-gray-900"><tr><th class="px-5 py-3">User</th><th class="px-5 py-3">Joined</th><th class="px-5 py-3">Platform role</th></tr></thead><tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach($users as $user)<tr><td class="px-5 py-4"><p class="font-semibold dark:text-white">{{ $user->name }}</p><p class="text-gray-500">{{ $user->email }}</p></td><td class="px-5 py-4 text-gray-500">{{ $user->created_at->format('M j, Y') }}</td><td class="px-5 py-4"><form method="POST" action="{{ route('admin.users.role', $user) }}" class="flex gap-2">@csrf @method('PATCH')<select name="role" class="rounded-lg border-gray-300 py-1 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">@foreach(\App\Services\PlatformRoleService::ROLES as $role)<option value="{{ $role }}" @selected($user->hasRole($role))>{{ $role }}</option>@endforeach</select><button class="font-semibold text-indigo-600">Update</button></form></td></tr>@endforeach
            </tbody></table>
        </div><div class="mt-8">{{ $users->links() }}</div>
    </div>
</x-app-layout>
