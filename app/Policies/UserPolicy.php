<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Organization;
use Illuminate\Support\Facades\Route;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any users.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can view any users from an organization.
     * The organization can be passed as an argument when used with the
     * authorize() method, or it will be automatically extracted from the route.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Organization  $organization
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAnyFromOrganization(User $user, ?Organization $organization = null)
    {
        if (!$organization) {
            /** @var Organization $organization */
            $organization = Route::current()->parameter('organization');
        }

        if ($user->organization_id !== $organization->id) {
            return Response::deny("The authenticated user doesn't belong to this organization");
        }

        return true;
    }

    /**
     * Determine whether the user can view the user.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\User  $model
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, User $model)
    {
        if ($user->organization_id !== $model->organization_id) {
            return Response::deny("This user doesn't belong to the authenticated user's organization");
        }

        return true;
    }

    /**
     * Determine whether the user can create users.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can update the user.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\User  $model
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, User $model)
    {
        if (
            $user->id !== $model->id
            && (!$user->isAdmin() || $user->organization_id !== $model->organization_id)
        ) {
            return Response::deny("The authenticated user is not the one with the ID $model->id or the administrator of the organization");
        }

        return true;
    }
}
