<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'email' => $this->faker->unique()->safeEmail(),
            'name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'job' => $this->faker->jobTitle(),
            'disabled' => $this->faker->boolean(),
            'role_id' => 1,
        ];
    }

    public function admin()
    {
        return $this->state(function (array $attributes) {
            return [
                'disabled' => false,
                'role_id' => 2,
            ];
        });
    }
}
