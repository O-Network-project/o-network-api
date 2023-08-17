<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // Example data. Must not be generated in production to avoid database
        // pollution.
        $this->call([
            OrganizationSeeder::class,
            UserSeeder::class,
            PostSeeder::class,
            CommentSeeder::class
        ]);
    }
}
