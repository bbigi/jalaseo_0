<?php

namespace App\Policies;

use App\Models\SocialAsset;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SocialAssetPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, SocialAsset $socialAsset): bool
    {
        return $socialAsset->created_by === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, SocialAsset $socialAsset): bool
    {
        return $socialAsset->created_by === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, SocialAsset $socialAsset): bool
    {
        return $socialAsset->created_by === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, SocialAsset $socialAsset): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, SocialAsset $socialAsset): bool
    {
        return false;
    }
}
