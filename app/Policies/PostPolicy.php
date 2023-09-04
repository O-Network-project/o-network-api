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
        return false;
    }

    /**
     * Determine whether the user can view any posts from an organization.
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

        return self::sameOrganizationResponse($user, $organization);
    }

    /**
     * Determine whether the user can view any posts from a user.
     * The user can be passed as an argument when used with the authorize()
     * method, or it will be automatically extracted from the route.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\User  $postsOwner
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAnyFromUser(User $user, ?User $postsOwner = null)
    {
        if (!$postsOwner) {
            /** @var User $postsOwner */
            $postsOwner = Route::current()->parameter('user');
        }

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
