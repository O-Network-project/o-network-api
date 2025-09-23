<?php

namespace Database\Seeders;

use App\Classes\Helpers\ConsoleHelper;
use App\Models\Organization;
use App\Models\Post;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class PostSeeder extends Seeder
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
            "Posts dataset volume (small: 15 per organization, large: 100 per organization)",
            ['s' => 'small', 'l' => 'large']
        );

        $count = $volume === 'small' ? 15 : 100;

        $posts = collect();
        $factory = Post::factory();

        $organizations = Organization::has('users')
            ->select('id')
            ->with('users:id,organization_id')
            ->get();

        $organizations->each(function (Organization $organization) use ($count, $posts, $factory) {
            // Using a for loop instead of the count method lets each post have
            // a different author
            for ($i = 0; $i < $count; $i++) {
                $posts->push($factory
                    ->for($organization->users->random(), 'author')
                    ->make()
                );
            }
        });

        $posts->chunk(1000)->each(function (Collection $postsChunk) {
            Post::insert($postsChunk->toArray());
        });
    }
}
