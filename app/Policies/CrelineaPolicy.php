<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Crelinea;
use Illuminate\Auth\Access\HandlesAuthorization;

class CrelineaPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_crelinea');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Crelinea $crelinea): bool
    {
        return $user->can('view_crelinea');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_crelinea');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Crelinea $crelinea): bool
    {
        return $user->can('update_crelinea');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Crelinea $crelinea): bool
    {
        return $user->can('delete_crelinea');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_crelinea');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, Crelinea $crelinea): bool
    {
        return $user->can('force_delete_crelinea');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_crelinea');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, Crelinea $crelinea): bool
    {
        return $user->can('restore_crelinea');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_crelinea');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, Crelinea $crelinea): bool
    {
        return $user->can('replicate_crelinea');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_crelinea');
    }
}
