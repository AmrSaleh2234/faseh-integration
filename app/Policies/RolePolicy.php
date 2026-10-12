<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;

class RolePolicy
{
    public function update(User $user, Role $role): bool { return $user->permits('roles.manage') && ! $role->locked; }
    public function delete(User $user, Role $role): bool { return $user->permits('roles.manage') && ! $role->built_in; }
}
