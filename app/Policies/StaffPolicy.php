<?php

namespace App\Policies;

use App\Models\Staff;
use App\Models\User;

class StaffPolicy
{
    public function update(User $user, Staff $staff): bool
    {
        return $user->permits('staff.edit')
            && ($staff->role?->slug !== 'owner' || $user->staff?->role?->slug === 'owner');
    }

    public function delete(User $user, Staff $staff): bool
    {
        return $user->permits('staff.delete')
            && ($staff->role?->slug !== 'owner' || $user->staff?->role?->slug === 'owner');
    }
}
