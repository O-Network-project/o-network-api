<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
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

    public function test_list_is_forbidden_for_authenticated_users(): void
    {
        /** @var User $user */
        $user = User::factory()->enabledMember()->for(
            Organization::factory()->create()
        )->create();

        $response = $this->actingAs($user)->get(route(self::LIST_ROUTE));
        $response->assertForbidden();
    }
}
