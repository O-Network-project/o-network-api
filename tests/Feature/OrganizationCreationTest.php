<?php

namespace Tests\Feature;

use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Tests\TestCase;

class OrganizationCreationTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    private const ROUTE = 'create_organization';

    private function generateRandomString($length)
    {
        $string = '';

        for ($i = 0; $i < $length; $i++) {
            $string .= $this->faker->randomLetter();
        }

        return $string;
    }

    public function test_name_is_required(): void
    {
        $response = $this->post(route(self::ROUTE));
        $response->assertJsonValidationErrorFor('name');
    }

    public function test_name_must_be_a_string(): void
    {
        $response = $this->post(route(self::ROUTE), [
            'name' => 42
        ]);

        $response->assertJsonValidationErrorFor('name');
    }

    public function test_name_length_must_be_50_at_most(): void
    {
        $response = $this->post(route(self::ROUTE), [
            'name' => $this->generateRandomString(50)
        ]);

        $response->assertCreated();

        $response = $this->post(route(self::ROUTE), [
            'name' => $this->generateRandomString(51)
        ]);

        $response->assertJsonValidationErrorFor('name');
    }

    public function test_name_must_be_unique(): void
    {
        $name = "Test";

        // We create an organization directly in database first...
        Organization::create(['name' => $name]);

        // ... and we use the same name to create a conflict
        $response = $this->post(route(self::ROUTE), [
            'name' => $name
        ]);

        $response->assertStatus(Response::HTTP_CONFLICT);
    }
}
