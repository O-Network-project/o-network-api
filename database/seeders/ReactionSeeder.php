<?php

namespace Database\Seeders;

use App\Classes\Helpers\ConsoleHelper;
use App\Models\Post;
use App\Models\User;
use App\Models\Reaction;
use App\Models\Organization;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class ReactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $volume = ConsoleHelper::promptForOption(
            $this->command,
            "Reaction dataset volume (small: 0 to 15 per post, large: 0 to maximum per post)",
            ['s' => 'small', 'l' => 'large']
        );

        $reactions = collect();
        $factory = Reaction::factory();

        $organizations = Organization::has('posts')
            ->select('id')
            ->with([
                'posts:posts.id,posts.author_id',
                'posts.reactionAuthors:users.id,reactions.author_id',
                'users:id,organization_id'
            ])
            ->get();

        // Looping through the organizations allows to generate reactions
        // with authors from the same organization than the posts authors
        $organizations->each(function (Organization $organization) use ($volume, $reactions, $factory) {
            $organization->posts->each(function (Post $post) use ($organization, $volume, $reactions, $factory) {
                // Users can only react once to a single post
                $possibleReactors = $organization->users
                    ->except($post->reactionAuthors->pluck('id')->toArray())
                    ->keyBy('id');

                // To get a more realistic set of reactions, none will have the
                // same author as the parent post
                $possibleReactors->forget($post->author_id);

                $reactions->push(...$factory
                    ->count(rand(0, $volume === 'small'
                        ? min($possibleReactors->count(), 15)
                        : $possibleReactors->count()
                    ))
                    ->state(function () use ($possibleReactors) {
                        $author = $possibleReactors->random();

                        // Each user can only react once per post, so the author
                        // of a reaction must not be selected again on the same
                        // post.
                        $possibleReactors->forget($author->id);

                        return ['author_id' => $author->id];
                    })
                    ->make(['post_id' => $post->id])
                );
            });
        });

        $reactions->chunk(1000)->each(function (Collection $reactionsChunk) {
            Reaction::insert($reactionsChunk->toArray());
        });
    }
}
