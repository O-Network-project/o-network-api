<?php

namespace Database\Seeders;

use App\Classes\Helpers\ConsoleHelper;
use App\Models\Organization;
use App\Models\Post;
use Illuminate\Database\Seeder;

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

        Organization::has('users')->each(function (Organization $organization) use ($count) {
            // Using a for loop instead of the count method lets each post have
            // a different author
            for ($i = 0; $i < $count; $i++) {
                Post::factory()
                    ->for($organization->users->random(), 'author')
                    ->create();
            }
        });
    }
}
