<?php

namespace Database\Factories;

use App\Models\ReactionType;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReactionFactory extends Factory
{
    protected static $typeIds;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        if (!isset(static::$typeIds)) {
            static::$typeIds = ReactionType::pluck('id')->all();
        }

        return [
            'type_id' => $this->faker->randomElement(static::$typeIds)
        ];
    }
}
