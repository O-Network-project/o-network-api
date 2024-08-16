<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Organization;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;

class CommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Organization::all()->each(function (Organization $organization) {
            // Looping through the organizations allows to generate comments
            // with authors from the same organization than the posts authors
            $users = $organization->users;

            $organization->posts->each(function (Post $post) use ($users) {
                // Each seeded post will have between 0 and 6 comments.
                // Using a for loop instead of the count method allows the
                // author to vary for each comment.
                for ($i = 0; $i < rand(0, 6); $i++) {
                    // To get a more realistic set of comments, some will have
                    // the same author than the parent post
                    $usePostAuthor = (bool) rand(0, 1);
                    $author = $usePostAuthor ? $post->author : $users->random();

                    Comment::factory()
                        ->for($post)
                        ->for($author, 'author')
                        ->create();
                }
            });
        });
    }
}
