<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use App\Http\Requests\UpdateCommentRequest;
use App\Http\Resources\CommentCollection;
use App\Models\Post;
use App\Models\Comment;
use App\Http\Resources\CommentResource;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Comment::class, 'comment');
    }

    /**
     * Should return all the comments of the database. But in this app MVP, no
     * user with any role can access that full list.
     * This method is only here to avoid an error when requesting the /comments
     * URI with the GET verb.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response(null, 403);
    }

    /**
     * Store a newly created comment in storage.
     *
     * @param  \App\Models\Post  $post
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Post $post, StoreCommentRequest $request)
    {
        $comment = new Comment();
        $comment->fill($request->validated());
        $comment->author_id = Auth::user()->id;
        $comment->post_id = $post->id;
        $comment->save();

        return new CommentResource($comment);
    }

    /**
     * Return the specified comment.
     *
     * @param  \App\Models\Comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function show(Comment $comment)
    {
        return new CommentResource($comment);
    }

    /**
     * Return all the comments of the specified post.
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function showPostComments(Post $post)
    {
        return new CommentCollection($post->comments);
    }

    /**
     * Update the specified comment in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCommentRequest $request, Comment $comment)
    {
        $comment->update($request->validated());
    }

    /**
     * Remove the specified comment from storage.
     *
     * @param  \App\Models\Comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function destroy(Comment $comment)
    {
        $comment->delete();
    }
}
