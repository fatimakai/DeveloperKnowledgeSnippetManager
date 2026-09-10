<?php

namespace App\Services;

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PlatformRoleService
{
    public const ADMIN = 'Admin';

    public const MODERATOR = 'Moderator';

    public const USER = 'User';

    public const MANAGE_USERS = 'manage users';

    public const MODERATE_PROMPTS = 'moderate prompts';

    public const ROLES = [self::ADMIN, self::MODERATOR, self::USER];

    public function ensure(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $manageUsers = Permission::findOrCreate(self::MANAGE_USERS, 'web');
        $moderatePrompts = Permission::findOrCreate(self::MODERATE_PROMPTS, 'web');

        Role::findOrCreate(self::ADMIN, 'web')->syncPermissions([$manageUsers, $moderatePrompts]);
        Role::findOrCreate(self::MODERATOR, 'web')->syncPermissions([$moderatePrompts]);
        Role::findOrCreate(self::USER, 'web')->syncPermissions([]);
    }

    public function assignDefault(User $user): void
    {
        $this->ensure();

        if (! $user->hasAnyRole(self::ROLES)) {
            $user->assignRole(self::USER);
        }
    }
}
