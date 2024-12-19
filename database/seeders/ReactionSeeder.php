<?php

namespace Database\Seeders;

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
        Organization::all()->each(function (Organization $organization) {
            // Looping through the organizations allows to generate reactions
            // with authors from the same organization than the posts authors
            $users = $organization->users;

            $organization->posts->each(function (Post $post) use ($users) {
                $possibleReactors = $users->reject(function (User $user) use ($post) {
                    return (
                        // To get a more realistic set of reactions, none will
                        // have the same author as the parent post
                        $user->id === $post->author->id

                        // Users can only react once to a single post
                        || $post->reactionAuthors->contains($user)
                    );
                });

                $reactionsLimit = rand(0, $possibleReactors->count());

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
                    $possibleReactors = $possibleReactors->reject(function (User $user) use ($author) {
                        return $user->id === $author->id;
                    });
                }
            });
        });
    }
}
