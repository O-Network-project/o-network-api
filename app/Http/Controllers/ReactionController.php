<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReactionRequest;
use App\Http\Requests\UpdateReactionRequest;
use App\Http\Resources\ReactionCollection;
use App\Models\Post;
use App\Models\Reaction;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\ReactionResource;

class ReactionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // In that app MVP, no user with any role can access the list of all
        // reactions of an organization
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
    public function store(Organization $organization, Post $post, StoreReactionRequest $request)
    {
        $user = Auth::user();

        // If the post is not in this organization, it's considered as not found
        if ($post->author->organization_id !== $organization->id) {
            return abort(404);
        }

        if ($user->organization_id !== $organization->id) {
            return response()->json(['message' => "The authenticated user doesn't belong to this organization"], 403);
        }

        $reaction = new Reaction();
        $reaction->fill($request->all());
        $reaction->author_id = $user->id;
        $reaction->post_id = $post->id;
        $reaction->save();

        return new ReactionResource($reaction);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Organization  $organization
     * @param  \App\Models\Reaction  $reaction
     * @return \Illuminate\Http\Response
     */
    public function show(Organization $organization, Reaction $reaction)
    {
        // If the reaction is not in this organization, it's considered as not
        // found
        if ($reaction->author->organization_id !== $organization->id) {
            return abort(404);
        }

        return new ReactionResource($reaction);
    }

    /**
     * Return all the reactions of the specified post.
     *
     * @param  \App\Models\Organization  $organization
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function showPostReactions(Organization $organization, Post $post)
    {
        // If the post is not in this organization, it's considered as not
        // found
        if ($post->author->organization_id !== $organization->id) {
            return abort(404);
        }

        return new ReactionCollection($post->reactions);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Organization  $organization
     * @param  \App\Models\Reaction  $reaction
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateReactionRequest $request, Organization $organization, Reaction $reaction)
    {
        // If the reaction is not in this organization, it's considered as not
        // found
        if ($reaction->author->organization_id !== $organization->id) {
            return abort(404);
        }

        if (Auth::user()->organization_id !== $organization->id) {
            return response()->json(['message' => "The authenticated user doesn't belong to this organization"], 403);
        }

        $reaction->update($request->all());
        return new ReactionResource($reaction);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Organization  $organization
     * @param  \App\Models\Reaction  $reaction
     * @return \Illuminate\Http\Response
     */
    public function destroy(Organization $organization, Reaction $reaction)
    {
        // If the reaction is not in this organization, it's considered as not
        // found
        if ($reaction->author->organization_id !== $organization->id) {
            return abort(404);
        }

        if (Auth::user()->organization_id !== $organization->id) {
            return response()->json(['message' => "The authenticated user doesn't belong to this organization"], 403);
        }

        $reaction->delete();
    }
}
