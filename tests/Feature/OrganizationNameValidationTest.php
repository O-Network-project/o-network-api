<?php

namespace Tests\Feature;

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
}
