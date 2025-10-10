<?php

namespace Database\Seeders;

use App\Classes\Helpers\ConsoleHelper;
use App\Models\Comment;
use App\Models\Organization;
use App\Models\Post;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

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

        $comments = collect();
        $factory = Comment::factory();

        $organizations = Organization::has('posts')
            ->select('id')
            ->with([
                'users:id,organization_id',
                'posts:posts.id,posts.author_id'
            ])
            ->get();

        // Looping through the organizations allows to generate comments with
        // authors from the same organization than the posts authors
        $organizations->each(function (Organization $organization) use ($volume, $comments, $factory) {
            $organization->posts->each(function (Post $post) use ($organization, $volume, $comments, $factory) {
                $comments->push(...$factory
                    ->count(rand(0, $volume === 'small' ? 6 : 100))
                    ->state(function () use ($post, $organization) {
                        // To get a more realistic set of comments, some will
                        // have the same author than the parent post
                        $usePostAuthor = (bool) rand(0, 1);

                        return [
                            'author_id' => $usePostAuthor
                                ? $post->author_id
                                : $organization->users->random()->id
                        ];
                    })
                    ->make(['post_id' => $post->id])
                );
            });
        });

        $comments->chunk(1000)->each(function (Collection $commentsChunk) {
            Comment::insert($commentsChunk->toArray());
        });
    }
}
