<?php

namespace Tests\Feature\Service;

use App\Models\Provider;
use App\Models\User;
use App\Services\ProviderService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Tests\TestCase;

class ProviderServiceTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_creates_a_provider_with_profile_attributes_and_defaults(): void
    {
        $user = User::factory()->create();
        $data = [
            'user_id' => $user->id,
            'description' => 'Experienced provider',
            'photo_url' => 'https://example.test/photo',
            'ine_url' => 'https://example.test/identity',
            'no_crime_url' => 'https://example.test/certificate',
            'available' => true,
            'km_radius' => 25,
        ];

        $provider = ProviderService::createProvider($data);

        $this->assertDatabaseHas('providers', ['id' => $provider->id, ...$data, 'verified' => false, 'reviews_count' => 0]);
        $this->assertSame($user->id, $provider->user->id);
        $provider->refresh();
        $this->assertTrue($provider->available);
        $this->assertFalse($provider->verified);
        $this->assertSame('0.00', $provider->avg_rating);
        $this->assertSame(25, $provider->km_radius);
        $this->assertSame(0, $provider->reviews_count);
    }

    public function test_rejects_a_second_profile_for_the_same_user(): void
    {
        $user = User::factory()->create();
        DB::table('providers')->insert(['user_id' => $user->id]);

        try {
            ProviderService::createProvider(['user_id' => $user->id]);
            $this->fail('A duplicate provider profile must be rejected.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('user_id', $exception->errors());
        }

        $this->assertDatabaseCount('providers', 1);
    }

    public function test_lists_and_finds_providers(): void
    {
        $user = User::factory()->create();
        $id = DB::table('providers')->insertGetId(['user_id' => $user->id]);

        $providers = ProviderService::getAllProviders();
        $provider = ProviderService::getProviderById($id);

        $this->assertCount(1, $providers);
        $this->assertSame($id, $providers->first()->id);
        $this->assertSame($user->id, $provider->user_id);
        $this->assertNull(ProviderService::getProviderById($id + 1));
    }

    public function test_updates_a_provider(): void
    {
        $user = User::factory()->create();
        $id = DB::table('providers')->insertGetId(['user_id' => $user->id]);

        $provider = ProviderService::updateProvider($id, [
            'user_id' => $user->id,
            'description' => 'Updated',
            'available' => true,
            'km_radius' => 30,
        ]);

        $this->assertSame($id, $provider->id);
        $this->assertDatabaseHas('providers', [
            'id' => $id,
            'description' => 'Updated',
            'available' => true,
            'km_radius' => 30,
            'verified' => false,
            'avg_rating' => 0,
            'reviews_count' => 0,
        ]);
    }

    public function test_rejects_system_managed_fields_on_create_and_update(): void
    {
        $user = User::factory()->create();
        $id = DB::table('providers')->insertGetId(['user_id' => $user->id]);

        foreach (['verified', 'avg_rating', 'reviews_count'] as $field) {
            try {
                ProviderService::createProvider(['user_id' => $user->id, $field => 1]);
                $this->fail('A system-managed field must be rejected on creation.');
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey($field, $exception->errors());
            }

            try {
                ProviderService::updateProvider($id, [$field => 1]);
                $this->fail('A system-managed field must be rejected on update.');
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey($field, $exception->errors());
            }
        }

        $this->assertDatabaseHas('providers', ['id' => $id, 'verified' => false, 'avg_rating' => 0, 'reviews_count' => 0]);
    }

    public function test_rejects_reassigning_a_provider_to_a_user_with_a_profile(): void
    {
        $users = User::factory()->count(2)->create();
        $id = DB::table('providers')->insertGetId(['user_id' => $users[0]->id]);
        DB::table('providers')->insert(['user_id' => $users[1]->id]);

        try {
            ProviderService::updateProvider($id, ['user_id' => $users[1]->id]);
            $this->fail('A duplicate provider profile must be rejected.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('user_id', $exception->errors());
        }

        $this->assertDatabaseHas('providers', ['id' => $id, 'user_id' => $users[0]->id]);
    }

    public function test_deletes_and_returns_a_provider(): void
    {
        $user = User::factory()->create();
        $id = DB::table('providers')->insertGetId(['user_id' => $user->id]);

        $provider = ProviderService::deleteProvider($id);

        $this->assertSame($id, $provider->id);
        $this->assertDatabaseMissing('providers', ['id' => $id]);
        $this->assertModelExists($user);
    }

    public function test_returns_null_when_updating_or_deleting_a_missing_provider(): void
    {
        $this->assertNull(ProviderService::updateProvider(42, ['description' => 'Updated']));
        $this->assertNull(ProviderService::deleteProvider(42));
    }

    public function test_reports_a_cancelled_creation(): void
    {
        $user = User::factory()->create();
        Provider::creating(fn () => false);

        try {
            ProviderService::createProvider(['user_id' => $user->id]);
            $this->fail('A cancelled creation must be reported.');
        } catch (RuntimeException $exception) {
            $this->assertSame('The provider could not be created.', $exception->getMessage());
        } finally {
            Provider::flushEventListeners();
        }

        $this->assertDatabaseCount('providers', 0);
    }

    public function test_reports_a_cancelled_update(): void
    {
        $user = User::factory()->create();
        $id = DB::table('providers')->insertGetId(['user_id' => $user->id]);
        Provider::updating(fn () => false);

        try {
            ProviderService::updateProvider($id, ['description' => 'Updated']);
            $this->fail('A cancelled update must be reported.');
        } catch (RuntimeException $exception) {
            $this->assertSame('The provider could not be updated.', $exception->getMessage());
        } finally {
            Provider::flushEventListeners();
        }

        $this->assertDatabaseHas('providers', ['id' => $id, 'description' => null]);
    }

    public function test_reports_a_cancelled_deletion(): void
    {
        $user = User::factory()->create();
        $id = DB::table('providers')->insertGetId(['user_id' => $user->id]);
        Provider::deleting(fn () => false);

        try {
            ProviderService::deleteProvider($id);
            $this->fail('A cancelled deletion must be reported.');
        } catch (RuntimeException $exception) {
            $this->assertSame('The provider could not be deleted.', $exception->getMessage());
        } finally {
            Provider::flushEventListeners();
        }

        $this->assertDatabaseHas('providers', ['id' => $id]);
    }
}
