<?php

namespace Database\Factories;

use Exception;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Kosv\RandomUser\Client\{Client, QueryBuilder};
use Kosv\RandomUser\Exceptions\TransportRequestException;

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
            try {
                $response = $client->request($query);
            }
            catch (TransportRequestException $e) {
                echo "randomuser.me API is unreachable. Using FakerPHP "
                    . "instead.\n";

                return [];
            }
            catch (\Exception $e) {
                echo "Error when requesting randomuser.me API. Using FakerPHP "
                    . "instead.\n";

                return [];
            }

            $randomUser = $response->getUsers()[0];

            $state = [
                'email' => $randomUser['email'],
                'name' => $randomUser['name']['first'],
                'surname' => $randomUser['name']['last']
            ];

            $imageUrl = $randomUser['picture']['large'];
            $imageResponse = Http::get($imageUrl);

            if (!$imageResponse->successful()) {
                echo "Error while downloading image from randomuser.me API. "
                    . "Sample user {$state['name']} {$state['surname']} won't "
                    . "have a profile picture.\n";

                return $state;
            }

            $extension = pathinfo(
                parse_url($imageUrl, PHP_URL_PATH),
                PATHINFO_EXTENSION
            );

            // Same algorithm as Laravel's hashName() method from UploadedFile
            $fileName = Str::random(40).'.'.$extension;

            try {
                Storage::disk('public')->put(
                    "profile-pictures/$fileName",
                    $imageResponse->body()
                );

                $state['profile_picture'] = $fileName;
            }
            catch (\Exception $e) {
                echo "Error while storing image from randomuser.me API. Sample "
                    . "user {$state['name']} {$state['surname']} won't have a "
                    . "profile picture.\n";
            }

            return $state;
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
