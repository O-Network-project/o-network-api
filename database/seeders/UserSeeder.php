<?php

namespace Database\Seeders;

use App\Classes\Helpers\ConsoleHelper;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class UserSeeder extends Seeder
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
            "Users dataset volume (small: 10 per organization (and from randomuser.me API), large: 300 per organization)",
            ['s' => 'small', 'l' => 'large']
        );

        $users = collect();
        $factory = User::factory();

        // Fetching sample users from the randomuser.me API is slow, so it's
        // only used with small datasets
        if ($volume === 'small') {
            $factory = $factory->fromRandomUserMeApi();
        }

        $organizations = Organization::all();

        // The last organization won't be filled with users, to be able to test
        // the creation of the admin (automatically the first user)
        $organizations->pop();

        // Each seeded organization (except the last one, check the above
        // comment) will have 1 admin and 10 members
        $organizations->each(function (Organization $organization) use ($volume, $users, $factory) {
            // The below conditions allows the UserSeeder to be launched
            // multiple times without ending with multiple admins, as only 1
            // admin should exist in each organization
            if ($organization->users->where('role_id', 2)->count() === 0) {
                $users->push($factory
                    ->admin()
                    ->for($organization)
                    ->make()
                    ->makeVisible('password')
                );
            }

            $users->push(...$factory
                ->for($organization)
                ->count($volume === 'small' ? 9 : 299)
                ->make()
                ->makeVisible('password')
            );
        });

        $users->chunk(1000)->each(function (Collection $usersChunk) {
            User::insert($usersChunk->toArray());
        });
    }
}
