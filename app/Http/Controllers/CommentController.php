<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use App\Http\Requests\UpdateCommentRequest;
use App\Http\Resources\CommentCollection;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Organization;
use Illuminate\Http\Request;
use App\Http\Resources\CommentResource;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // In that app MVP, no user with any role can access the list of all
        // comments of an organization
        return response(null, 403);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Models\Organization  $organization
     * @param  \App\Models\Post  $post
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Organization $organization, Post $post, StoreCommentRequest $request)
    {
        $user = Auth::user();

        // If the post is not in this organization, it's considered as not found
        if ($post->author->organization_id !== $organization->id) {
            return abort(404);
        }

        if ($user->organization_id !== $organization->id) {
            return response()->json(['message' => "The authenticated user doesn't belong to this organization"], 403);
        }

        $comment = new Comment();
        $comment->fill($request->all());
        $comment->author_id = $user->id;
        $comment->post_id = $post->id;
        $comment->save();

        return new CommentResource($comment);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Organization  $organization
     * @param  \App\Models\Comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function show(Organization $organization, Comment $comment)
    {
        // If the comment is not in this organization, it's considered as not
        // found
        if ($comment->author->organization_id !== $organization->id) {
            return abort(404);
        }

        return new CommentResource($comment);
    }

    /**
     * Return all the comments of the specified post.
     *
     * @param  \App\Models\Organization  $organization
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function showPostComments(Organization $organization, Post $post)
    {
        // If the post is not in this organization, it's considered as not
        // found
        if ($post->author->organization_id !== $organization->id) {
            return abort(404);
        }

        return new CommentCollection($post->comments);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Organization  $organization
     * @param  \App\Models\Comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCommentRequest $request, Organization $organization, Comment $comment)
    {
        // If the comment is not in this organization, it's considered as not
        // found
        if ($comment->author->organization_id !== $organization->id) {
            return abort(404);
        }

        if (Auth::user()->organization_id !== $organization->id) {
            return response()->json(['message' => "The authenticated user doesn't belong to this organization"], 403);
        }

        $comment->update($request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Organization  $organization
     * @param  \App\Models\Comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function destroy(Organization $organization, Comment $comment)
    {
        // If the comment is not in this organization, it's considered as not
        // found
        if ($comment->author->organization_id !== $organization->id) {
            return abort(404);
        }

        if (Auth::user()->organization_id !== $organization->id) {
            return response()->json(['message' => "The authenticated user doesn't belong to this organization"], 403);
        }

        $comment->delete();
    }
}
