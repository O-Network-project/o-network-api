<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrganizationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Organization::factory()
            ->count(2)
            ->has(User::factory()->admin()->count(1)->hasPosts(3))
            ->has(User::factory()->count(10)->hasPosts(3))
            ->has(User::factory()->count(5))
            ->create()
        ;

        // An empty organization to test the creation of the first
        // user, automatically considered as the admin
        Organization::factory()->create();
    }
}
