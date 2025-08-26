<?php

namespace Database\Seeders;

use App\Classes\Helpers\ConsoleHelper;
use App\Models\Post;
use App\Models\User;
use App\Models\Reaction;
use App\Models\Organization;
use Illuminate\Database\Seeder;

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

        // Looping through the organizations allows to generate reactions
        // with authors from the same organization than the posts authors
        Organization::all()->each(function (Organization $organization) use ($volume) {
            $organization->posts->each(function (Post $post) use ($organization, $volume) {
                // Users can only react once to a single post
                $possibleReactors = $organization->users
                    ->except($post->reactionAuthors->pluck('id')->toArray())
                    ->keyBy('id');

                // To get a more realistic set of reactions, none will have the
                // same author as the parent post
                $possibleReactors->forget($post->author->id);

                $reactionsLimit = rand(0, $volume === 'small'
                    ? min($possibleReactors->count(), 15)
                    : $possibleReactors->count()
                );

                // Using a for loop instead of the count method allows the
                // author to vary for each reaction.
                for ($i = 0; $i < $reactionsLimit; $i++) {
                    $author = $possibleReactors->random();

                    Reaction::factory()
                        ->for($post)
                        ->for($author, 'author')
                        ->create();

                    // Each user can only add one reaction per post; after the
                    // adding, the author must not be used again for the current
                    // post.
                    $possibleReactors->forget($author->id);
                }
            });
        });
    }
}
