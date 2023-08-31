<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;
use App\Models\Organization;
use Illuminate\Support\Facades\Route;
use Illuminate\Auth\Access\HandlesAuthorization;

class PostPolicy extends ContentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any posts.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can view any posts from an organization.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAnyFromOrganization(User $user)
    {
        /** @var Organization $organization */
        $organization = Route::current()->parameter('organization');
        return self::sameOrganizationResponse($user, $organization);
    }

    /**
     * Determine whether the user can view any posts from a user.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAnyFromUser(User $user)
    {
        /** @var User $postsOwner */
        $postsOwner = Route::current()->parameter('user');
        return self::sameOrganizationResponse($user, $postsOwner);
    }

    /**
     * Determine whether the user can view the post.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Post $post)
    {
        return self::sameOrganizationResponse($user, $post);
    }

    /**
     * Determine whether the user can create posts.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can update the post.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Post $post)
    {
        return self::sameAuthorResponse($user, $post);
    }

    /**
     * Determine whether the user can delete the post.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Post $post)
    {
        return self::sameAuthorOrAdminResponse($user, $post);
    }
}
