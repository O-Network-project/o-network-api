<?php

namespace Database\Seeders;

use App\Models\ReactionType;
use Illuminate\Database\Seeder;

class ReactionTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ReactionType::insert([
            ['tag' => 'like', 'name' => "J'aime"],
            ['tag' => 'love', 'name' => "J'adore"],
            ['tag' => 'haha', 'name' => "Ha ha"],
            ['tag' => 'wow', 'name' => "Wouah"],
            ['tag' => 'sad', 'name' => "Triste"],
            ['tag' => 'angry', 'name' => "En colère"]
        ]);
    }
}
