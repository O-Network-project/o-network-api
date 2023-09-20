<?php

namespace Tests\Feature;

use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Tests\TestCase;

class OrganizationNameValidationTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    private const ROUTE = 'validate_organization';

    private function generateRandomString($length)
    {
        $string = '';

        for ($i = 0; $i < $length; $i++) {
            $string .= $this->faker->randomLetter();
        }

        return $string;
    }

    public function test_valid_organization_name_can_pass(): void
    {
        $response = $this->get(route(self::ROUTE, [
            'name' => "Test"
        ]));

        $response->assertOk();
    }

    public function test_validation_does_not_create_an_organization(): void
    {
        $this->get(route(self::ROUTE, [
            'name' => "Test"
        ]));

        $this->assertCount(0, Organization::all());
    }

    public function test_name_is_required(): void
    {
        $response = $this->get(route(self::ROUTE));
        $response->assertJsonValidationErrorFor('name');
    }

    public function test_name_must_be_a_string(): void
    {
        $response = $this->get(route(self::ROUTE, [
            'name' => ['array of string is not a string']
        ]));

        $response->assertJsonValidationErrorFor('name');
    }

    public function test_name_length_must_be_50_at_most(): void
    {
        $response = $this->get(route(self::ROUTE, [
            'name' => $this->generateRandomString(50)
        ]));
        $response->assertOk();

        $response = $this->get(route(self::ROUTE, [
            'name' => $this->generateRandomString(51)
        ]));
        $response->assertJsonValidationErrorFor('name');
    }

    public function test_name_must_be_unique(): void
    {
        $name = "Test";

        // We create an organization directly in database first...
        Organization::create(['name' => $name]);

        // ... and we use the same name to create a conflict
        $response = $this->get(route(self::ROUTE, [
            'name' => $name
        ]));

        $response->assertStatus(Response::HTTP_CONFLICT);
    }
}
