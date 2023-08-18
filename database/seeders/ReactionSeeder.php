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
                // To get a more realistic set of reactions, none will have the
                // same author than the parent post
                $otherUsers = $users->reject(function (User $user) use ($post) {
                    return $user->id === $post->author->id;
                });

                // Each seeded post will have between 0 and 15 reactions.
                // Using a for loop instead of the count method allows the
                // author to vary for each reaction.
                for ($i = 0; $i < rand(0, 15); $i++) {
                    Reaction::factory()
                        ->for($post)
                        ->for($otherUsers->random(), 'author')
                        ->create();
                }
            });
        });
    }
}
