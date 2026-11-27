<?php

namespace Tests\Feature\Service;

use App\Models\Review;
use App\Models\User;
use App\Services\ReviewService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Tests\TestCase;

class ReviewServiceTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_review_changes_update_provider_totals(): void
    {
        $service = $this->createService();
        $other = $this->createService($service['client_id'], $service['provider_id']);

        $review = ReviewService::createReview([...$service, 'stars' => 4, 'comment' => 'Good']);
        $secondReview = ReviewService::createReview([...$other, 'stars' => 5]);

        $this->assertDatabaseHas('reviews', ['id' => $review->id, ...$service, 'stars' => 4, 'comment' => 'Good']);
        $this->assertSame($service['client_id'], $review->client->id);
        $this->assertSame($service['provider_id'], $review->provider->id);
        $this->assertSame($service['service_id'], $review->service->id);
        $this->assertCount(2, ReviewService::getAllReviews());
        $this->assertSame(4, ReviewService::getReviewById($review->id)->stars);
        $this->assertProviderTotals($service['provider_id'], 2, 4.5);

        $updated = ReviewService::updateReview($review->id, ['stars' => 3, 'comment' => null]);

        $this->assertSame(3, $updated->stars);
        $this->assertDatabaseHas('reviews', ['id' => $review->id, 'stars' => 3, 'comment' => null]);
        $this->assertProviderTotals($service['provider_id'], 2, 4);

        $deleted = ReviewService::deleteReview($review->id);

        $this->assertSame($review->id, $deleted->id);
        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
        $this->assertProviderTotals($service['provider_id'], 1, 5);

        ReviewService::deleteReview($secondReview->id);
        $this->assertProviderTotals($service['provider_id'], 0, 0);
    }

    public function test_reassignment_updates_both_providers(): void
    {
        $first = $this->createService();
        $second = $this->createService();
        $review = ReviewService::createReview([...$first, 'stars' => 4]);

        ReviewService::updateReview($review->id, $second);

        $this->assertDatabaseHas('reviews', ['id' => $review->id, ...$second, 'stars' => 4]);
        $this->assertProviderTotals($first['provider_id'], 0, 0);
        $this->assertProviderTotals($second['provider_id'], 1, 4);
    }

    public function test_provider_average_is_rounded_to_two_decimal_places(): void
    {
        $first = $this->createService();
        $second = $this->createService($first['client_id'], $first['provider_id']);
        $third = $this->createService($first['client_id'], $first['provider_id']);

        ReviewService::createReview([...$first, 'stars' => 1]);
        ReviewService::createReview([...$second, 'stars' => 1]);
        ReviewService::createReview([...$third, 'stars' => 2]);

        $this->assertProviderTotals($first['provider_id'], 3, 1.33);
    }

    public function test_only_completed_services_with_matching_parties_can_be_reviewed(): void
    {
        $completed = $this->createService();
        $pending = $this->createService(status: 'pending');
        $other = $this->createService();

        foreach ([
            [[...$pending, 'stars' => 5], 'service_id'],
            [[...$completed, 'service_id' => 99999, 'stars' => 5], 'service_id'],
            [[...$completed, 'client_id' => $other['client_id'], 'stars' => 5], 'client_id'],
            [[...$completed, 'provider_id' => $other['provider_id'], 'stars' => 5], 'provider_id'],
        ] as [$data, $field]) {
            try {
                ReviewService::createReview($data);
                $this->fail('An ineligible review must be rejected.');
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey($field, $exception->errors());
            }
        }

        $this->assertDatabaseCount('reviews', 0);
        $this->assertProviderTotals($completed['provider_id'], 0, 0);
    }

    public function test_rejects_invalid_ratings_and_duplicate_service_reviews(): void
    {
        $service = $this->createService();

        foreach ([0, 6, 2.5] as $stars) {
            try {
                ReviewService::createReview([...$service, 'stars' => $stars]);
                $this->fail('An invalid rating must be rejected.');
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey('stars', $exception->errors());
            }
        }

        $review = ReviewService::createReview([...$service, 'stars' => 5]);

        try {
            ReviewService::createReview([...$service, 'stars' => 3]);
            $this->fail('A duplicate review must be rejected.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('service_id', $exception->errors());
        }

        try {
            ReviewService::updateReview($review->id, ['stars' => 0]);
            $this->fail('An invalid updated rating must be rejected.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('stars', $exception->errors());
        }

        $this->assertDatabaseCount('reviews', 1);
        $this->assertProviderTotals($service['provider_id'], 1, 5);
    }

    public function test_rejects_reassignment_to_a_service_that_already_has_a_review(): void
    {
        $first = $this->createService();
        $second = $this->createService();
        $review = ReviewService::createReview([...$first, 'stars' => 3]);
        ReviewService::createReview([...$second, 'stars' => 5]);

        try {
            ReviewService::updateReview($review->id, $second);
            $this->fail('A duplicate service review must be rejected.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('service_id', $exception->errors());
        }

        $this->assertDatabaseHas('reviews', ['id' => $review->id, ...$first]);
        $this->assertProviderTotals($first['provider_id'], 1, 3);
        $this->assertProviderTotals($second['provider_id'], 1, 5);
    }

    public function test_missing_reviews_return_null(): void
    {
        $this->assertNull(ReviewService::getReviewById(42));
        $this->assertNull(ReviewService::updateReview(42, ['stars' => 4]));
        $this->assertNull(ReviewService::deleteReview(42));
    }

    public function test_cancelled_writes_do_not_change_reviews_or_totals(): void
    {
        $service = $this->createService();
        Review::creating(fn () => false);

        try {
            ReviewService::createReview([...$service, 'stars' => 5]);
            $this->fail('A cancelled creation must be reported.');
        } catch (RuntimeException $exception) {
            $this->assertSame('The review could not be created.', $exception->getMessage());
        } finally {
            Review::flushEventListeners();
        }

        $this->assertDatabaseCount('reviews', 0);
        $this->assertProviderTotals($service['provider_id'], 0, 0);

        $review = ReviewService::createReview([...$service, 'stars' => 5]);
        Review::updating(fn () => false);

        try {
            ReviewService::updateReview($review->id, ['stars' => 2]);
            $this->fail('A cancelled update must be reported.');
        } catch (RuntimeException $exception) {
            $this->assertSame('The review could not be updated.', $exception->getMessage());
        } finally {
            Review::flushEventListeners();
        }

        Review::deleting(fn () => false);

        try {
            ReviewService::deleteReview($review->id);
            $this->fail('A cancelled deletion must be reported.');
        } catch (RuntimeException $exception) {
            $this->assertSame('The review could not be deleted.', $exception->getMessage());
        } finally {
            Review::flushEventListeners();
        }

        $this->assertDatabaseHas('reviews', ['id' => $review->id, 'stars' => 5]);
        $this->assertProviderTotals($service['provider_id'], 1, 5);
    }

    /** @return array{service_id: int, client_id: int, provider_id: int} */
    private function createService(?int $clientId = null, ?int $providerId = null, string $status = 'completed'): array
    {
        $clientId ??= User::factory()->create()->id;
        $providerId ??= DB::table('providers')->insertGetId(['user_id' => User::factory()->create()->id]);
        DB::table('crafts')->insertOrIgnore(['name' => 'Plumbing']);
        $craftId = DB::table('crafts')->where('name', 'Plumbing')->value('id');
        $serviceId = DB::table('services')->insertGetId([
            'client_id' => $clientId,
            'provider_id' => $providerId,
            'craft_id' => $craftId,
            'description' => 'Service',
            'status' => $status,
        ]);

        return ['service_id' => $serviceId, 'client_id' => $clientId, 'provider_id' => $providerId];
    }

    private function assertProviderTotals(int $id, int $count, float $average): void
    {
        $this->assertDatabaseHas('providers', [
            'id' => $id,
            'reviews_count' => $count,
            'avg_rating' => $average,
        ]);
    }
}
