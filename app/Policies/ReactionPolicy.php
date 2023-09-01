<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Reaction;
use Illuminate\Support\Facades\Route;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReactionPolicy extends ContentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any reactions.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can view the reaction.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Reaction  $reaction
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Reaction $reaction)
    {
        return self::sameOrganizationResponse($user, $reaction);
    }

    /**
     * Determine whether the user can create reactions.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        /** @var Post $post */
        $post = Route::current()->parameter('post');
        return self::sameOrganizationResponse($user, $post);
    }

    /**
     * Determine whether the user can update the reaction.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Reaction  $reaction
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Reaction $reaction)
    {
        return self::sameAuthorResponse($user, $reaction);
    }

    /**
     * Determine whether the user can delete the reaction.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Reaction  $reaction
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Reaction $reaction)
    {
        return self::sameAuthorResponse($user, $reaction);
    }
}
