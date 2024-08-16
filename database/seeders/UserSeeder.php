<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $organizations = Organization::all();

        // The last organization won't be filled with users, to be able to test
        // the creation of the admin (automatically the first user)
        $organizations->pop();

        // Each seeded organization (except the last one, check the above
        // comment) will have 1 admin and 10 members
        $organizations->each(function (Organization $organization) {
            // The below conditions allows the UserSeeder to be launched
            // multiple times without ending with multiple admins, as only 1
            // admin should exist in each organization
            if ($organization->users->where('role_id', 2)->count() === 0) {
                User::factory()->admin()->for($organization)->create();
            }

            User::factory()->for($organization)->count(10)->create();
        });
    }
}
