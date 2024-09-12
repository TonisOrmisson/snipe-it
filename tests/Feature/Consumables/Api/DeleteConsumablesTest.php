<?php

namespace Tests\Feature\Consumables\Api;

use App\Models\Company;
use App\Models\Consumable;
use App\Models\User;
use Tests\Concerns\TestsMultipleFullCompanySupport;
use Tests\Concerns\TestsPermissionsRequirement;
use Tests\TestCase;

class DeleteConsumablesTest extends TestCase implements TestsMultipleFullCompanySupport, TestsPermissionsRequirement
{
    public function testRequiresPermission()
    {
        $consumable = Consumable::factory()->create();

        $this->actingAsForApi(User::factory()->create())
            ->deleteJson(route('api.consumables.destroy', $consumable))
            ->assertForbidden();
    }

    public function testCanDeleteConsumables()
    {
        $consumable = Consumable::factory()->create();

        $this->actingAsForApi(User::factory()->deleteConsumables()->create())
            ->deleteJson(route('api.consumables.destroy', $consumable))
            ->assertStatusMessageIs('success');

        $this->assertTrue($consumable->fresh()->trashed());
    }

    public function testAdheresToMultipleFullCompanySupportScoping()
    {
        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $consumableA = Consumable::factory()->for($companyA)->create();
        $consumableB = Consumable::factory()->for($companyB)->create();
        $consumableC = Consumable::factory()->for($companyB)->create();

        $superUser = $companyA->users()->save(User::factory()->superuser()->make());
        $userInCompanyA = $companyA->users()->save(User::factory()->deleteConsumables()->make());
        $userInCompanyB = $companyB->users()->save(User::factory()->deleteConsumables()->make());

        $this->settings->enableMultipleFullCompanySupport();

        $this->actingAsForApi($userInCompanyA)
            ->deleteJson(route('api.consumables.destroy', $consumableB))
            ->assertStatusMessageIs('error');

        $this->actingAsForApi($userInCompanyB)
            ->deleteJson(route('api.consumables.destroy', $consumableA))
            ->assertStatusMessageIs('error');

        $this->actingAsForApi($superUser)
            ->deleteJson(route('api.consumables.destroy', $consumableC))
            ->assertStatusMessageIs('success');

        $this->assertNull($consumableA->fresh()->deleted_at, 'Consumable unexpectedly deleted');
        $this->assertNull($consumableB->fresh()->deleted_at, 'Consumable unexpectedly deleted');
        $this->assertNotNull($consumableC->fresh()->deleted_at, 'Consumable was not deleted');
    }
}
