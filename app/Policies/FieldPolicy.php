<?php

namespace App\Policies;

use App\Models\Farm;
use App\Models\Field;
use App\Models\User;

class FieldPolicy
{
    public function view(User $user, Field $field): bool
    {
        return $user->hasRole('Admin')
            || $field->farm->owner_id === $user->id
            || $field->farm->members()->whereKey($user->id)->exists();
    }

    public function create(User $user, Farm $farm): bool
    {
        return $user->hasRole('Admin')
            || $farm->owner_id === $user->id
            || $farm->members()
                ->whereKey($user->id)
                ->wherePivotIn('role', ['Farm Manager/Agronomist', 'Farm Owner'])
                ->exists();
    }

    public function update(User $user, Field $field): bool
    {
        return $this->create($user, $field->farm);
    }

    public function delete(User $user, Field $field): bool
    {
        return $user->hasRole('Admin') || $field->farm->owner_id === $user->id;
    }
}
