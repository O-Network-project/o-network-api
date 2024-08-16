<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\User;
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
        // Each user will have between 0 and 5 posts
        User::all()->each(function (User $user) {
            Post::factory()->for($user, 'author')->count(rand(0, 5))->create();
        });
    }
}
