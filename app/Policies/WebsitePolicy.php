<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Website;

class WebsitePolicy
{
    /**
     * Determine if user owns the website
     */
    public function own(User $user, Website $website): bool
    {
        return $user->id === $website->created_by;
    }

    /**
     * Determine if user can view
     */
    public function view(User $user, Website $website): bool
    {
        return $this->own($user, $website);
    }

    /**
     * Determine if user can update
     */
    public function update(User $user, Website $website): bool
    {
        return $this->own($user, $website);
    }

    /**
     * Determine if user can delete
     */
    public function delete(User $user, Website $website): bool
    {
        return $this->own($user, $website);
    }
}
