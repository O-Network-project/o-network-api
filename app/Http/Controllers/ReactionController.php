<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrUpdateReactionRequest;
use App\Http\Resources\ReactionCollection;
use App\Models\Post;
use App\Models\Reaction;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\ReactionResource;

class ReactionController extends Controller
{
    /**
     * Should return all the reactions of the database. But in this app MVP, no
     * user with any role can access that full list.
     * This method is only here to avoid an error when requesting the /reactions
     * URI with the GET verb.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response(null, 403);
    }

    /**
     * Store a newly created reaction in storage.
     *
     * @param  \App\Models\Post  $post
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Post $post, StoreOrUpdateReactionRequest $request)
    {
        $user = Auth::user();

        if ($user->organization_id !== $post->organization->id) {
            return response()->json(['message' => "The authenticated user doesn't belong to this organization"], 403);
        }

        // A user can add only one reaction on a single post
        /** @var bool $conflict */
        $conflict = Reaction::
            where('post_id', $post->id)
            ->where('author_id', $user->id)
            ->exists();

        if ($conflict) {
            return response()->json([
                'message' => "A reaction from the same author already exists on this post."
            ], Response::HTTP_CONFLICT);
        }

        $reaction = new Reaction();
        $reaction->fill($request->validated());
        $reaction->author_id = $user->id;
        $reaction->post_id = $post->id;
        $reaction->save();

        return new ReactionResource($reaction);
    }

    /**
     * Return the specified reaction.
     *
     * @param  \App\Models\Reaction  $reaction
     * @return \Illuminate\Http\Response
     */
    public function show(Reaction $reaction)
    {
        return new ReactionResource($reaction);
    }

    /**
     * Return all the reactions of the specified post.
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function showPostReactions(Post $post)
    {
        return new ReactionCollection($post->reactions);
    }

    /**
     * Update the specified reaction in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Reaction  $reaction
     * @return \Illuminate\Http\Response
     */
    public function update(StoreOrUpdateReactionRequest $request, Reaction $reaction)
    {
        if (Auth::user()->organization_id !== $reaction->organization->id) {
            return response()->json(['message' => "The authenticated user doesn't belong to this organization"], 403);
        }

        $reaction->update($request->validated());
        return new ReactionResource($reaction);
    }

    /**
     * Remove the specified reaction from storage.
     *
     * @param  \App\Models\Reaction  $reaction
     * @return \Illuminate\Http\Response
     */
    public function destroy(Reaction $reaction)
    {
        if (Auth::user()->organization_id !== $reaction->organization->id) {
            return response()->json(['message' => "The authenticated user doesn't belong to this organization"], 403);
        }

        $reaction->delete();
    }
}
