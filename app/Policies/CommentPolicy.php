<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;
use App\Models\Comment;
use Illuminate\Support\Facades\Route;
use Illuminate\Auth\Access\HandlesAuthorization;

class CommentPolicy extends ContentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any comments.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can view any comments from a post.
     * The post can be passed as an argument when used with the authorize()
     * method, or it will be automatically extracted from the route.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAnyFromPost(User $user, ?Post $post = null)
    {
        if (!$post) {
            /** @var Post $post */
            $post = Route::current()->parameter('post');
        }

        return self::sameOrganizationResponse($user, $post);
    }

    /**
     * Determine whether the user can view the comment.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Comment  $comment
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Comment $comment)
    {
        return self::sameOrganizationResponse($user, $comment);
    }

    /**
     * Determine whether the user can create comments.
     * The post can be passed as an argument when used with the authorize()
     * method, or it will be automatically extracted from the route.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user, ?Post $post = null)
    {
        if (!$post) {
            /** @var Post $post */
            $post = Route::current()->parameter('post');
        }

        return self::sameOrganizationResponse($user, $post);
    }

    /**
     * Determine whether the user can update the comment.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Comment  $comment
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Comment $comment)
    {
        return self::sameAuthorResponse($user, $comment);
    }

    /**
     * Determine whether the user can delete the comment.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Comment  $comment
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Comment $comment)
    {
        return self::sameAuthorOrAdminResponse($user, $comment);
    }
}
