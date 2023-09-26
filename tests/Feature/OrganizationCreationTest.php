<?php

namespace Tests\Feature;

use App\Http\Resources\OrganizationResource;
use App\Models\Organization;
use Carbon\Carbon;
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

    public function test_timestamps_cannot_be_forced(): void
    {
        $pastDate = Carbon::create('1970-01-01 00:00:00');

        $this->post(route(self::ROUTE), [
            'name' => "Test",
            'created_at' => $pastDate->toDateTimeString(),
            'updated_at' => $pastDate->toDateTimeString(),
        ]);

        $organization = Organization::first();

        $this->assertFalse($pastDate->equalTo($organization->created_at));
        $this->assertFalse($pastDate->equalTo($organization->updated_at));
    }

    public function test_user_can_create_an_organization(): void
    {
        $response = $this->post(route(self::ROUTE), [
            'name' => "Test"
        ]);

        $response->assertCreated();
        $this->assertCount(1, Organization::all());
    }

    public function test_new_organization_is_returned_after_creation(): void
    {
        $response = $this->post(route(self::ROUTE), [
            'name' => "Test"
        ]);

        $organizationResource = new OrganizationResource(Organization::first());

        // The first parameter of assertExactJson() must be an array, so the
        // toArray() method of resources class is perfect for that purpose. But
        // it needs a Request in parameter, and its mandatory in tests. We don't
        // have one here... so null is a good workaround.
        $response->assertExactJson($organizationResource->toArray(null));
    }
}
