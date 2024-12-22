<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Kosv\RandomUser\Client\{Client, QueryBuilder};

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
            'role_id' => 1
        ];
    }

    public function fromRandomUserMeApi()
    {
        $client = new Client();
        $query = new QueryBuilder();
        $query->setIncludeFields(['email', 'name', 'picture']);

        return $this->state(function (array $attributes) use ($client, $query) {
            $response = $client->request($query);
            $randomUser = $response->getUsers()[0];

            $imageUrl = $randomUser['picture']['large'];
            $imageResponse = Http::get($imageUrl);

            $extension = pathinfo(
                parse_url($imageUrl, PHP_URL_PATH),
                PATHINFO_EXTENSION
            );

            // Same algorithm as Laravel's hashName() method from UploadedFile
            $fileName = Str::random(40).'.'.$extension;

            Storage::disk('public')->put(
                "profile-pictures/$fileName",
                $imageResponse->body()
            );

            return [
                'email' => $randomUser['email'],
                'name' => $randomUser['name']['first'],
                'surname' => $randomUser['name']['last'],
                'profile_picture' => $fileName,
            ];
        });
    }

    public function enabledMember()
    {
        return $this->state(function (array $attributes) {
            return [
                'disabled' => false,
            ];
        });
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
