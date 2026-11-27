<?php

namespace Tests\Feature\Service;

use App\Models\Review;
use App\Models\Service;
use App\Models\User;
use App\Services\ReviewService;
use App\Services\ServiceService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Tests\TestCase;

class ServiceServiceTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_creates_lists_and_finds_services(): void
    {
        $data = $this->serviceData();

        $service = ServiceService::createService([
            ...$data,
            'address' => 'Main street',
            'latitude' => '19.4326000',
            'longitude' => '-99.1332000',
            'agreed_price' => '125.50',
            'service_datetime' => '2026-11-28 12:00:00',
        ]);

        $this->assertDatabaseHas('services', ['id' => $service->id, ...$data, 'status' => 'pending']);
        $this->assertSame($data['client_id'], $service->client->id);
        $this->assertSame($data['provider_id'], $service->provider->id);
        $this->assertSame($data['craft_id'], $service->craft->id);
        $this->assertSame('19.4326000', $service->latitude);
        $this->assertSame('-99.1332000', $service->longitude);
        $this->assertSame('125.50', $service->agreed_price);
        $this->assertSame('2026-11-28 12:00:00', $service->service_datetime->toDateTimeString());
        $this->assertCount(1, ServiceService::getAllServices());
        $this->assertSame($service->id, ServiceService::getServiceById($service->id)->id);
    }

    public function test_updates_a_service_and_clears_optional_fields(): void
    {
        $service = ServiceService::createService([...$this->serviceData(), 'address' => 'Old address']);

        $updated = ServiceService::updateService($service->id, [
            'description' => 'Updated',
            'address' => null,
            'provider_id' => null,
            'status' => 'accepted',
        ]);

        $this->assertSame($service->id, $updated->id);
        $this->assertDatabaseHas('services', [
            'id' => $service->id,
            'description' => 'Updated',
            'address' => null,
            'provider_id' => null,
            'status' => 'accepted',
        ]);
    }

    public function test_rejects_invalid_service_data(): void
    {
        $data = $this->serviceData();

        try {
            ServiceService::createService([
                ...$data,
                'client_id' => 99999,
                'craft_id' => 99999,
                'provider_id' => 99999,
                'description' => '',
                'latitude' => 91,
                'longitude' => -181,
                'status' => 'invalid',
                'agreed_price' => -1,
                'service_datetime' => 'not a date',
            ]);
            $this->fail('Invalid service data must be rejected.');
        } catch (ValidationException $exception) {
            foreach (['client_id', 'craft_id', 'provider_id', 'description', 'latitude', 'longitude', 'status', 'agreed_price', 'service_datetime'] as $field) {
                $this->assertArrayHasKey($field, $exception->errors());
            }
        }

        $this->assertDatabaseCount('services', 0);
    }

    public function test_rejects_invalid_partial_updates(): void
    {
        $service = ServiceService::createService($this->serviceData());

        try {
            ServiceService::updateService($service->id, ['description' => null, 'status' => 'invalid']);
            $this->fail('An invalid update must be rejected.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('description', $exception->errors());
            $this->assertArrayHasKey('status', $exception->errors());
        }

        $this->assertDatabaseHas('services', ['id' => $service->id, 'description' => 'Repair', 'status' => 'pending']);
    }

    public function test_rejects_changes_that_would_invalidate_a_review(): void
    {
        [$service, $review] = $this->reviewedService();
        $otherClient = User::factory()->create();
        $otherProviderId = DB::table('providers')->insertGetId(['user_id' => User::factory()->create()->id]);

        foreach ([
            ['client_id' => $otherClient->id],
            ['provider_id' => $otherProviderId],
            ['provider_id' => null],
            ['status' => 'cancelled'],
        ] as $data) {
            try {
                ServiceService::updateService($service->id, $data);
                $this->fail('A conflicting update must be rejected.');
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey(array_key_first($data), $exception->errors());
            }
        }

        $updated = ServiceService::updateService($service->id, [
            'client_id' => $service->client_id,
            'provider_id' => $service->provider_id,
            'status' => 'completed',
            'description' => 'Updated',
        ]);

        $this->assertSame('Updated', $updated->description);
        $this->assertSame($review->id, $updated->review->id);
        $this->assertDatabaseHas('providers', ['id' => $service->provider_id, 'reviews_count' => 1, 'avg_rating' => 4]);
    }

    public function test_deleting_a_reviewed_service_updates_provider_totals(): void
    {
        [$service, $review] = $this->reviewedService();
        $other = ServiceService::createService([
            'client_id' => $service->client_id,
            'provider_id' => $service->provider_id,
            'craft_id' => $service->craft_id,
            'description' => 'Another repair',
            'status' => 'completed',
        ]);
        ReviewService::createReview([
            'service_id' => $other->id,
            'client_id' => $other->client_id,
            'provider_id' => $other->provider_id,
            'stars' => 2,
        ]);

        $deleted = ServiceService::deleteService($service->id);

        $this->assertSame($service->id, $deleted->id);
        $this->assertDatabaseMissing('services', ['id' => $service->id]);
        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
        $this->assertDatabaseHas('providers', ['id' => $service->provider_id, 'reviews_count' => 1, 'avg_rating' => 2]);
    }

    public function test_deletes_a_service_without_a_review(): void
    {
        $service = ServiceService::createService($this->serviceData());

        $deleted = ServiceService::deleteService($service->id);

        $this->assertSame($service->id, $deleted->id);
        $this->assertDatabaseMissing('services', ['id' => $service->id]);
    }

    public function test_returns_null_for_missing_services(): void
    {
        $this->assertNull(ServiceService::getServiceById(42));
        $this->assertNull(ServiceService::updateService(42, ['description' => 'Updated']));
        $this->assertNull(ServiceService::deleteService(42));
    }

    public function test_cancelled_creation_and_update_are_reported(): void
    {
        $data = $this->serviceData();
        Service::creating(fn () => false);

        try {
            ServiceService::createService($data);
            $this->fail('A cancelled creation must be reported.');
        } catch (RuntimeException $exception) {
            $this->assertSame('The service could not be created.', $exception->getMessage());
        } finally {
            Service::flushEventListeners();
        }

        $this->assertDatabaseCount('services', 0);
        $service = ServiceService::createService($data);
        Service::updating(fn () => false);

        try {
            ServiceService::updateService($service->id, ['description' => 'Updated']);
            $this->fail('A cancelled update must be reported.');
        } catch (RuntimeException $exception) {
            $this->assertSame('The service could not be updated.', $exception->getMessage());
        } finally {
            Service::flushEventListeners();
        }

        $this->assertDatabaseHas('services', ['id' => $service->id, 'description' => 'Repair']);
    }

    public function test_cancelled_service_deletion_rolls_back_review_and_totals(): void
    {
        [$service, $review] = $this->reviewedService();
        Service::deleting(fn () => false);

        try {
            ServiceService::deleteService($service->id);
            $this->fail('A cancelled deletion must be reported.');
        } catch (RuntimeException $exception) {
            $this->assertSame('The service could not be deleted.', $exception->getMessage());
        } finally {
            Service::flushEventListeners();
        }

        $this->assertDatabaseHas('services', ['id' => $service->id]);
        $this->assertDatabaseHas('reviews', ['id' => $review->id]);
        $this->assertDatabaseHas('providers', ['id' => $service->provider_id, 'reviews_count' => 1, 'avg_rating' => 4]);
    }

    public function test_cancelled_review_deletion_prevents_service_deletion(): void
    {
        [$service, $review] = $this->reviewedService();
        Review::deleting(fn () => false);

        try {
            ServiceService::deleteService($service->id);
            $this->fail('A cancelled review deletion must be reported.');
        } catch (RuntimeException $exception) {
            $this->assertSame('The review could not be deleted.', $exception->getMessage());
        } finally {
            Review::flushEventListeners();
        }

        $this->assertDatabaseHas('services', ['id' => $service->id]);
        $this->assertDatabaseHas('reviews', ['id' => $review->id]);
        $this->assertDatabaseHas('providers', ['id' => $service->provider_id, 'reviews_count' => 1, 'avg_rating' => 4]);
    }

    /** @return array{client_id: int, provider_id: int, craft_id: int, description: string} */
    private function serviceData(): array
    {
        $clientId = User::factory()->create()->id;
        $providerId = DB::table('providers')->insertGetId(['user_id' => User::factory()->create()->id]);
        DB::table('crafts')->insertOrIgnore(['name' => 'Plumbing']);
        $craftId = DB::table('crafts')->where('name', 'Plumbing')->value('id');

        return ['client_id' => $clientId, 'provider_id' => $providerId, 'craft_id' => $craftId, 'description' => 'Repair'];
    }

    /** @return array{Service, Review} */
    private function reviewedService(): array
    {
        $service = ServiceService::createService([...$this->serviceData(), 'status' => 'completed']);
        $review = ReviewService::createReview([
            'service_id' => $service->id,
            'client_id' => $service->client_id,
            'provider_id' => $service->provider_id,
            'stars' => 4,
        ]);

        return [$service, $review];
    }
}
