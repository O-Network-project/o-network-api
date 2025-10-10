<?php

namespace Database\Seeders;

use App\Classes\Helpers\ConsoleHelper;
use App\Models\Organization;
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
        $volume = ConsoleHelper::promptForOption(
            $this->command,
            "Organizations dataset volume (small: 3, large: 100)",
            ['s' => 'small', 'l' => 'large']
        );

        Organization::insert(Organization::factory()
            ->count($volume === 'small' ? 3 : 100)
            ->make()
            ->toArray()
        );
    }
}
