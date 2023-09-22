<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class OrganizationVisibilityTest extends TestCase
{
    use RefreshDatabase;

    private const LIST_ROUTE = 'organizations';
    private const ITEM_ROUTE = 'organization';

    public function test_viewing_organizations_requires_authentication(): void
    {
        $response = $this->get(route(self::LIST_ROUTE));
        $response->assertUnauthorized();

        $response = $this->get(route(self::ITEM_ROUTE, ['organization' => 1]));
        $response->assertUnauthorized();
    }
}
