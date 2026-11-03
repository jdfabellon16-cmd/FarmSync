<?php

namespace Tests\Feature;

use App\Models\Farm;
use App\Models\Field;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FarmFieldApiTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->owner = User::factory()->create();
        $this->owner->assignRole('Farm Owner');
    }

    public function test_farm_routes_require_authentication(): void
    {
        $this->getJson('/api/v1/farms')->assertUnauthorized();
    }

    public function test_owner_can_create_and_search_farms(): void
    {
        $this->actingAs($this->owner)
            ->postJson('/api/v1/farms', [
                'name' => 'Willow Creek Farm',
                'location' => 'Salinas, California',
                'latitude' => 36.6777,
                'longitude' => -121.6555,
                'total_area_hectares' => 37.5,
            ])
            ->assertCreated()
            ->assertJsonPath('data.name', 'Willow Creek Farm')
            ->assertJsonPath('data.total_area_hectares', '37.5000');

        $this->getJson('/api/v1/farms?search=Willow')
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Willow Creek Farm');
    }

    public function test_farm_validation_returns_json_errors(): void
    {
        $this->actingAs($this->owner)
            ->postJson('/api/v1/farms', [
                'name' => '',
                'total_area_hectares' => -1,
                'latitude' => 91,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'total_area_hectares', 'latitude']);
    }

    public function test_users_cannot_access_another_owners_farm(): void
    {
        $farm = Farm::factory()->create();

        $this->actingAs($this->owner)
            ->getJson("/api/v1/farms/{$farm->id}")
            ->assertForbidden();

        $this->getJson('/api/v1/farms')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_owner_can_manage_fields_with_status_and_boundary_validation(): void
    {
        $farm = Farm::factory()->create(['owner_id' => $this->owner->id]);
        $payload = [
            'name' => 'East Plot',
            'area_hectares' => 4.25,
            'soil_type' => 'Loam',
            'irrigation_type' => 'Drip',
            'status' => 'Prepared',
            'boundary' => [
                ['lat' => 36.7, 'lng' => -119.8],
                ['lat' => 36.71, 'lng' => -119.8],
                ['lat' => 36.71, 'lng' => -119.79],
            ],
        ];

        $this->actingAs($this->owner)
            ->postJson("/api/v1/farms/{$farm->id}/fields", $payload)
            ->assertCreated()
            ->assertJsonPath('data.status', 'Prepared')
            ->assertJsonPath('data.area_hectares', '4.2500');

        $field = Field::where('farm_id', $farm->id)->firstOrFail();
        $this->assertCount(3, $field->boundary);

        $this->patchJson("/api/v1/farms/{$farm->id}/fields/{$field->id}", [
            'status' => 'Growing',
        ])->assertOk()->assertJsonPath('data.status', 'Growing');

        $this->getJson("/api/v1/farms/{$farm->id}/fields?status=Growing")
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_field_worker_can_view_but_cannot_manage_fields(): void
    {
        $farm = Farm::factory()->create(['owner_id' => $this->owner->id]);
        $field = Field::factory()->create(['farm_id' => $farm->id]);
        $worker = User::factory()->create();
        $worker->assignRole('Field Worker/Farmhand');
        $farm->members()->attach($worker->id, ['role' => 'Field Worker/Farmhand']);

        $this->actingAs($worker)
            ->getJson("/api/v1/farms/{$farm->id}/fields")
            ->assertOk()
            ->assertJsonPath('data.0.id', $field->id);

        $this->postJson("/api/v1/farms/{$farm->id}/fields", [
            'name' => 'Restricted Field',
            'area_hectares' => 1,
        ])->assertForbidden();
    }

    public function test_nested_field_routes_do_not_allow_cross_farm_ids(): void
    {
        $firstFarm = Farm::factory()->create(['owner_id' => $this->owner->id]);
        $secondFarm = Farm::factory()->create(['owner_id' => $this->owner->id]);
        $field = Field::factory()->create(['farm_id' => $secondFarm->id]);

        $this->actingAs($this->owner)
            ->getJson("/api/v1/farms/{$firstFarm->id}/fields/{$field->id}")
            ->assertNotFound();
    }
}
