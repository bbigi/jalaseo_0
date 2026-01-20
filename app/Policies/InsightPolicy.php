<?php

namespace App\Policies;

use App\Models\Insight;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class InsightPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Insight $insight): bool
    {
        return $insight->created_by === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Insight $insight): bool
    {
        return $insight->created_by === $user->id;
    }

    public function delete(User $user, Insight $insight): bool
    {
        return $insight->created_by === $user->id;
    }
}
