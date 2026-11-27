<?php

namespace Tests\Feature;

use App\Models\Craft;
use App\Services\CraftService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Tests\TestCase;

class CraftServiceTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_creates_a_craft_with_its_attributes(): void
    {
        $data = [
            'name' => 'Plumbing',
            'description' => 'Pipe work',
            'map_color' => '#123456',
            'active' => false,
        ];

        $craft = CraftService::createCraft($data);

        $this->assertDatabaseHas('crafts', ['id' => $craft->id, ...$data]);
    }

    public function test_rejects_a_duplicate_craft_name(): void
    {
        DB::table('crafts')->insert(['name' => 'Plumbing']);

        try {
            CraftService::createCraft(['name' => 'Plumbing']);
            $this->fail('A duplicate name must be rejected.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('name', $exception->errors());
        }

        $this->assertDatabaseCount('crafts', 1);
    }

    public function test_lists_and_finds_crafts(): void
    {
        $id = DB::table('crafts')->insertGetId(['name' => 'Plumbing']);

        $crafts = CraftService::getAllCrafts();
        $craft = CraftService::getCraftById($id);

        $this->assertCount(1, $crafts);
        $this->assertSame($id, $crafts->first()->id);
        $this->assertSame('Plumbing', $craft->name);
        $this->assertNull(CraftService::getCraftById($id + 1));
    }

    public function test_updates_and_returns_a_craft(): void
    {
        $id = DB::table('crafts')->insertGetId(['name' => 'Plumbing']);

        $craft = CraftService::updateCraft($id, ['name' => 'Electrical', 'active' => false]);

        $this->assertSame($id, $craft->id);
        $this->assertDatabaseHas('crafts', ['id' => $id, 'name' => 'Electrical', 'active' => false]);
    }

    public function test_deletes_and_returns_a_craft(): void
    {
        $id = DB::table('crafts')->insertGetId(['name' => 'Plumbing']);

        $craft = CraftService::deleteCraft($id);

        $this->assertSame($id, $craft->id);
        $this->assertDatabaseMissing('crafts', ['id' => $id]);
    }

    public function test_returns_null_when_updating_or_deleting_a_missing_craft(): void
    {
        $this->assertNull(CraftService::updateCraft(42, ['name' => 'Electrical']));
        $this->assertNull(CraftService::deleteCraft(42));
    }

    public function test_reports_a_cancelled_creation(): void
    {
        Craft::creating(fn () => false);

        try {
            CraftService::createCraft(['name' => 'Plumbing']);
            $this->fail('A cancelled creation must be reported.');
        } catch (RuntimeException $exception) {
            $this->assertSame('The craft could not be created.', $exception->getMessage());
        } finally {
            Craft::flushEventListeners();
        }

        $this->assertDatabaseCount('crafts', 0);
    }

    public function test_reports_a_cancelled_update(): void
    {
        $id = DB::table('crafts')->insertGetId(['name' => 'Plumbing']);
        Craft::updating(fn () => false);

        try {
            CraftService::updateCraft($id, ['name' => 'Electrical']);
            $this->fail('A cancelled update must be reported.');
        } catch (RuntimeException $exception) {
            $this->assertSame('The craft could not be updated.', $exception->getMessage());
        } finally {
            Craft::flushEventListeners();
        }

        $this->assertDatabaseHas('crafts', ['id' => $id, 'name' => 'Plumbing']);
    }

    public function test_reports_a_cancelled_deletion(): void
    {
        $id = DB::table('crafts')->insertGetId(['name' => 'Plumbing']);
        Craft::deleting(fn () => false);

        try {
            CraftService::deleteCraft($id);
            $this->fail('A cancelled deletion must be reported.');
        } catch (RuntimeException $exception) {
            $this->assertSame('The craft could not be deleted.', $exception->getMessage());
        } finally {
            Craft::flushEventListeners();
        }

        $this->assertDatabaseHas('crafts', ['id' => $id]);
    }
}
