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
    /**
     * Display a listing of the resource.
     * @param  \App\Models\Organization  $organization
     * @return \Illuminate\Http\Response
     */
    public function index(Organization $organization)
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
     * Store a newly created resource in storage.
     *
     * @param  \App\Models\Organization  $organization
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Organization $organization, StorePostRequest $request)
    {
        $user = Auth::user();

        if ($user->organization_id !== $organization->id) {
            return response()->json(['message' => "The authenticated user doesn't belong to this organization"], 403);
        }

        $post = new Post();
        $post->fill($request->all());
        $post->author_id = $user->id;
        $post->save();

        return new PostResource($post);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Organization  $organization
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function show(Organization $organization, Post $post)
    {
        // If the post is not in this organization, it's considered as not found
        if ($post->author->organization_id !== $organization->id) {
            return abort(404);
        }

        return new PostResource($post);
    }

    /**
     * Return the posts of a specific user.
     *
     * @param  \App\Models\Organization  $organization
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function showUserPosts(Organization $organization, User $user)
    {
        // If the user is not in this organization, it's considered as not found
        if ($user->organization_id !== $organization->id) {
            return abort(404);
        }

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
     * @param  \App\Models\Organization  $organization
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePostRequest $request, Organization $organization, Post $post)
    {
        // If the post is not in this organization, it's considered as not found
        if ($post->author->organization_id !== $organization->id) {
            return abort(404);
        }

        if (Auth::user()->organization_id !== $organization->id) {
            return response()->json(['message' => "The authenticated user doesn't belong to this organization"], 403);
        }

        $post->update($request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function destroy(Organization $organization, Post $post)
    {
        // If the post is not in this organization, it's considered as not found
        if ($post->author->organization_id !== $organization->id) {
            return abort(404);
        }

        if (Auth::user()->organization_id !== $organization->id) {
            return response()->json(['message' => "The authenticated user doesn't belong to this organization"], 403);
        }

        $post->delete();
    }
}
