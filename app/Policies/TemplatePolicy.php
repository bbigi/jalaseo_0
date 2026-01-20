<?php

namespace App\Policies;

use App\Models\Template;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TemplatePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     * User can view: Public templates OR templates they created
     */
    public function view(User $user, Template $template): bool
    {
        return $template->is_public || $template->created_by === $user->id;
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
     * Only creator can update (public templates can't be updated by users)
     */
    public function update(User $user, Template $template): bool
    {
        return $template->created_by === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     * Only creator can delete (public templates can't be deleted by users)
     */
    public function delete(User $user, Template $template): bool
    {
        return $template->created_by === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Template $template): bool
    {
        return $template->created_by === $user->id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Template $template): bool
    {
        return $template->created_by === $user->id;
    }
}
