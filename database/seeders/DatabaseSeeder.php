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
        // Mandatory and permanent data. Needed for the app to work properly in
        // any environment.
        $this->call([
            RoleSeeder::class
        ]);

        // Example data. Must not be generated in production to avoid database
        // pollution.
        if (!App::environment('production')) {
            $this->call([
                OrganizationSeeder::class,
                UserSeeder::class,
                PostSeeder::class,
                CommentSeeder::class
            ]);
        }
    }
}
