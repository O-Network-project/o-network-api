<?php

namespace Tests\Feature;

use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class OrganizationNameValidationTest extends TestCase
{
    use RefreshDatabase;

    private const ROUTE = 'validate_organization';

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
}
