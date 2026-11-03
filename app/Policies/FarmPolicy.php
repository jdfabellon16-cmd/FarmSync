<?php

namespace App\Policies;

use App\Models\Farm;
use App\Models\User;

class FarmPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Farm $farm): bool
    {
        return $user->hasRole('Admin')
            || $farm->owner_id === $user->id
            || $farm->members()->whereKey($user->id)->exists();
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['Admin', 'Farm Owner']);
    }

    public function update(User $user, Farm $farm): bool
    {
        return $user->hasRole('Admin') || $farm->owner_id === $user->id;
    }

    public function delete(User $user, Farm $farm): bool
    {
        return $this->update($user, $farm);
    }

    public function manageFields(User $user, Farm $farm): bool
    {
        return $user->hasRole('Admin')
            || $farm->owner_id === $user->id
            || $farm->members()
                ->whereKey($user->id)
                ->wherePivotIn('role', ['Farm Manager/Agronomist', 'Farm Owner'])
                ->exists();
    }
}
