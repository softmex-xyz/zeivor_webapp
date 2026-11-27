<?php

namespace Tests\Feature;

use App\Http\Controllers\CraftController;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class CraftControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Route::apiResource('crafts', CraftController::class);
    }

    public function test_lists_and_shows_crafts(): void
    {
        $id = DB::table('crafts')->insertGetId(['name' => 'Plumbing']);

        $this->getJson('/crafts')->assertOk()->assertJsonCount(1)->assertJsonPath('0.name', 'Plumbing');
        $this->getJson("/crafts/$id")->assertOk()->assertJsonPath('id', $id)->assertJsonPath('name', 'Plumbing');
    }

    public function test_creates_a_craft_from_validated_attributes(): void
    {
        $response = $this->postJson('/crafts', [
            'name' => 'Plumbing',
            'description' => 'Pipe work',
            'map_color' => '#123456',
            'active' => false,
            'id' => 999,
        ]);

        $response->assertOk()->assertJsonPath('name', 'Plumbing');
        $this->assertDatabaseHas('crafts', [
            'id' => $response->json('id'),
            'name' => 'Plumbing',
            'description' => 'Pipe work',
            'map_color' => '#123456',
            'active' => false,
        ]);
        $this->assertDatabaseMissing('crafts', ['id' => 999]);
    }

    public function test_invalid_attributes_are_rejected_on_creation(): void
    {
        $this->postJson('/crafts', [
            'name' => str_repeat('a', 101),
            'description' => ['not a string'],
            'map_color' => '1234567890',
            'active' => 'invalid',
        ])->assertUnprocessable()->assertJsonValidationErrors(['name', 'description', 'map_color', 'active']);

        $this->postJson('/crafts', [])->assertUnprocessable()->assertJsonValidationErrors(['name']);
        $this->assertDatabaseCount('crafts', 0);
    }

    public function test_duplicate_names_are_rejected_on_creation_and_update(): void
    {
        DB::table('crafts')->insert(['name' => 'Plumbing']);
        $id = DB::table('crafts')->insertGetId(['name' => 'Electrical']);

        $this->postJson('/crafts', ['name' => 'Plumbing'])
            ->assertUnprocessable()->assertJsonValidationErrors(['name']);
        $this->patchJson("/crafts/$id", ['name' => 'Plumbing'])
            ->assertUnprocessable()->assertJsonValidationErrors(['name']);
        $this->assertDatabaseHas('crafts', ['id' => $id, 'name' => 'Electrical']);
        $this->assertDatabaseCount('crafts', 2);
    }

    public function test_updates_a_craft_with_validated_attributes(): void
    {
        $id = DB::table('crafts')->insertGetId(['name' => 'Plumbing', 'description' => 'Old']);

        $this->patchJson("/crafts/$id", [
            'name' => 'Plumbing',
            'description' => null,
            'active' => false,
            'id' => 999,
        ])->assertOk()->assertJsonPath('id', $id)->assertJsonPath('description', null);

        $this->assertDatabaseHas('crafts', ['id' => $id, 'name' => 'Plumbing', 'description' => null, 'active' => false]);
        $this->assertDatabaseMissing('crafts', ['id' => 999]);
    }

    public function test_invalid_attributes_are_rejected_on_update(): void
    {
        $id = DB::table('crafts')->insertGetId(['name' => 'Plumbing']);

        $this->patchJson("/crafts/$id", ['name' => null, 'active' => 'invalid'])
            ->assertUnprocessable()->assertJsonValidationErrors(['name', 'active']);

        $this->assertDatabaseHas('crafts', ['id' => $id, 'name' => 'Plumbing', 'active' => true]);
    }

    public function test_deletes_a_craft(): void
    {
        $id = DB::table('crafts')->insertGetId(['name' => 'Plumbing']);

        $this->deleteJson("/crafts/$id")->assertOk()->assertJsonPath('id', $id);

        $this->assertDatabaseMissing('crafts', ['id' => $id]);
    }

    public function test_missing_crafts_return_not_found(): void
    {
        $this->getJson('/crafts/42')->assertNotFound();
        $this->patchJson('/crafts/42', ['name' => 'Plumbing'])->assertNotFound();
        $this->deleteJson('/crafts/42')->assertNotFound();
    }
}
