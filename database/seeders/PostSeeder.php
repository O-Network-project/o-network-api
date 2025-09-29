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

        $posts = collect();
        $factory = Post::factory();

        $organizations = Organization::has('users')
            ->select('id')
            ->with('users:id,organization_id')
            ->get();

        $organizations->each(function (Organization $organization) use ($volume, $posts, $factory) {
            $posts->push(...$factory
                ->count($volume === 'small' ? 15 : 100)
                ->state(function () use ($organization) {
                    return ['author_id' => $organization->users->random()->id];
                })
                ->make()
            );
        });

        $posts->chunk(1000)->each(function (Collection $postsChunk) {
            Post::insert($postsChunk->toArray());
        });
    }
}
