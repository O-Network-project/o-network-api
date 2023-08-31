<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Resources\PostCollection;
use App\Http\Resources\PostResource;
use App\Models\Organization;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Post::class, 'post');
    }

    /**
     * Should return all the posts of the database. But in this app MVP, no user
     * with any role can access that full list.
     * This method is only here to avoid an error when requesting the /posts URI
     * with the GET verb.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response(null, 403);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorePostRequest $request)
    {
        $post = new Post();
        $post->fill($request->validated());
        $post->author_id = Auth::user()->id;
        $post->save();

        return new PostResource($post);
    }

    /**
     * Return the specified post.
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function show(Post $post)
    {
        return new PostResource($post);
    }

    /**
     * Returns the posts of the provided organization.
     * @param  \App\Models\Organization  $organization
     * @return \Illuminate\Http\Response
     */
    public function showOrganizationPosts(Organization $organization)
    {
        $posts = Post::
            leftJoin('users', 'posts.author_id', '=', 'users.id')
            ->where('users.organization_id', $organization->id)
            ->select('posts.*')
            ->orderBy('posts.created_at', 'desc')
            ->paginate(10);


        return new PostCollection($posts);
    }

    /**
     * Return the posts of a specific user.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function showUserPosts(User $user)
    {
        $posts = Post::
            where('posts.author_id', $user->id)
            ->orderBy('posts.created_at', 'desc')
            ->paginate(10);

        return new PostCollection($posts);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePostRequest $request, Post $post)
    {
        $post->update($request->validated());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function destroy(Post $post)
    {
        $post->delete();
    }
}
