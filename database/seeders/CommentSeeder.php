<?php

namespace Database\Seeders;

use App\Classes\Helpers\ConsoleHelper;
use App\Models\Comment;
use App\Models\Organization;
use App\Models\Post;
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
        $volume = ConsoleHelper::promptForOption(
            $this->command,
            "Comments dataset volume (small: 0 to 6 per post, large: 0 to 100 per post)",
            ['s' => 'small', 'l' => 'large']
        );

        Organization::all()->each(function (Organization $organization) use ($volume) {
            // Looping through the organizations allows to generate comments
            // with authors from the same organization than the posts authors
            $users = $organization->users;

            $organization->posts->each(function (Post $post) use ($users, $volume) {
                $commentsLimit = rand(0, $volume === 'small' ? 6 : 100);

                // Using a for loop instead of the count method allows the
                // author to vary for each comment.
                for ($i = 0; $i < $commentsLimit; $i++) {
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
