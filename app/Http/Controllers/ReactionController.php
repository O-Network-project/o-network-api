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
    public function __construct()
    {
        $this->authorizeResource(Reaction::class, 'reaction');
    }


    /**
     * Override the default mapping of the resource policies methods to add our
     * custom showPostReactions one (the resourceAbilityMap() method comes
     * from the AuthorizesRequests trait, imported in the Controller parent
     * class).
     *
     * @return array
     */
    protected function resourceAbilityMap()
    {
        return array_merge(parent::resourceAbilityMap(), [
            'showPostReactions' => 'viewAnyFromPost'
        ]);
    }

    /**
     * Override the default list of the policy methods that cannot receive an
     * instantiated model to add our custom showPostReactions method (the
     * resourceMethodsWithoutModels() method comes from the AuthorizesRequests
     * trait, imported in the Controller parent class).
     *
     * @return array
     */
    protected function resourceMethodsWithoutModels()
    {
        return array_merge(parent::resourceMethodsWithoutModels(), [
            'showPostReactions'
        ]);
    }

    /**
     * Return all the reactions of the database. But in this app MVP, no user
     * with any role can access that full list, it's blocked by the
     * ReactionPolicy.
     * This method is only here to avoid an error when requesting the /reactions
     * URI with the GET verb.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return new ReactionCollection(Reaction::all());
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
        $reaction->delete();
        return response()->noContent();
    }
}
