<?php

namespace Database\Factories;

use App\Models\ReactionType;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $types = ReactionType::all();

        return [
            'type_id' => $this->faker->randomElement($types)
        ];
    }
}
