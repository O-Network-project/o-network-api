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
        return $this->state(function (array $attributes) {
            $randomUser = $this->fetchUserFromRandomUserMeApi();

            if ($randomUser === null) {
                return [];
            }

            $state = [
                'email' => $randomUser['email'],
                'name' => $randomUser['name']['first'],
                'surname' => $randomUser['name']['last']
            ];

            $profilePicture = $this->storeRandomUserProfilePicture($randomUser);

            if ($profilePicture !== null) {
                $state['profile_picture'] = $profilePicture;
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

    private function fetchUserFromRandomUserMeApi() {
        $client = new Client();
        $query = new QueryBuilder();
        $query->setIncludeFields(['email', 'name', 'picture']);

        try {
            echo "Fetching a sample user form randomuser.me API... ";
            $response = $client->request($query);
            echo "done.\n";
        }
        catch (TransportRequestException $e) {
            echo "\nrandomuser.me API is unreachable. Using FakerPHP "
                . "instead.\n";

            return null;
        }
        catch (\Exception $e) {
            echo "\nError when requesting randomuser.me API. Using FakerPHP "
                . "instead.\n";

            return null;
        }

        return $response->getUsers()[0];
    }

    private function storeRandomUserProfilePicture(array $randomUser) {
        echo "Downloading the profile picture... ";
        $imageUrl = $randomUser['picture']['large'];
        $imageResponse = Http::get($imageUrl);

        if (!$imageResponse->successful()) {
            echo "\nError while downloading image from randomuser.me API. "
                . "Sample user {$randomUser['name']['first']} "
                . "{$randomUser['name']['last']} won't have a profile "
                . "picture.\n";

            return null;
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
        }
        catch (\Exception $e) {
            echo "\nError while storing image from randomuser.me API. Sample "
                . "user {$randomUser['name']['first']} "
                . "{$randomUser['name']['last']} won't have a profile "
                . "picture.\n";

            return null;
        }

        echo "done.\n";

        return $fileName;
    }
}
