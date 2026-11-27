<?php

namespace App\Services;

use App\Models\Provider;
use App\Models\Review;
use App\Models\Service;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class ReviewService
{
    /** @return Collection<int, Review> */
    public static function getAllReviews(): Collection
    {
        return Review::all();
    }

    public static function getReviewById(int|string $id): ?Review
    {
        return Review::find($id);
    }

    /** @param array<string, mixed> $data */
    public static function createReview(array $data): Review
    {
        return DB::transaction(function () use ($data): Review {
            $data = self::validateReview($data);
            self::lockProviders([(int) $data['provider_id']]);

            $review = Review::create($data);

            if (! $review->exists) {
                throw new RuntimeException('The review could not be created.');
            }

            self::updateProviderTotals([(int) $review->provider_id]);

            return $review;
        }, 3);
    }

    /** @param array<string, mixed> $data */
    public static function updateReview(int|string $id, array $data): ?Review
    {
        return DB::transaction(function () use ($id, $data): ?Review {
            $review = Review::whereKey($id)->lockForUpdate()->first();

            if (! $review) {
                return null;
            }

            $data = self::validateReview(array_replace($review->only([
                'service_id', 'client_id', 'provider_id', 'stars', 'comment',
            ]), $data), $review);
            $providerIds = array_values(array_unique([(int) $review->provider_id, (int) $data['provider_id']]));
            self::lockProviders($providerIds);

            if (! $review->update($data)) {
                throw new RuntimeException('The review could not be updated.');
            }

            self::updateProviderTotals($providerIds);

            return $review;
        }, 3);
    }

    public static function deleteReview(int|string $id): ?Review
    {
        return DB::transaction(function () use ($id): ?Review {
            $review = Review::whereKey($id)->lockForUpdate()->first();

            if (! $review) {
                return null;
            }

            $providerIds = [(int) $review->provider_id];
            self::lockProviders($providerIds);

            if (! $review->delete()) {
                throw new RuntimeException('The review could not be deleted.');
            }

            self::updateProviderTotals($providerIds);

            return $review;
        }, 3);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private static function validateReview(array $data, ?Review $review = null): array
    {
        $data = Validator::make($data, [
            'service_id' => ['required', 'integer'],
            'client_id' => ['required', 'integer'],
            'provider_id' => ['required', 'integer'],
            'stars' => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string'],
        ])->validate();

        $service = Service::whereKey($data['service_id'])->lockForUpdate()->first();

        if (! $service || $service->status !== 'completed') {
            throw ValidationException::withMessages([
                'service_id' => 'The service must exist and be completed.',
            ]);
        }

        if ((int) $service->client_id !== (int) $data['client_id']) {
            throw ValidationException::withMessages([
                'client_id' => 'The client must match the service client.',
            ]);
        }

        if ($service->provider_id === null || (int) $service->provider_id !== (int) $data['provider_id']) {
            throw ValidationException::withMessages([
                'provider_id' => 'The provider must match the service provider.',
            ]);
        }

        $existing = Review::where('service_id', $data['service_id']);
        if ($review) {
            $existing->where('id', '!=', $review->id);
        }

        if ($existing->lockForUpdate()->first()) {
            throw ValidationException::withMessages([
                'service_id' => 'The service already has a review.',
            ]);
        }

        return $data;
    }

    /** @param list<int> $providerIds */
    private static function lockProviders(array $providerIds): void
    {
        Provider::whereIn('id', $providerIds)->orderBy('id')->lockForUpdate()->get(['id']);
    }

    /** @param list<int> $providerIds */
    private static function updateProviderTotals(array $providerIds): void
    {
        foreach ($providerIds as $providerId) {
            $totals = DB::table('reviews')
                ->where('provider_id', $providerId)
                ->selectRaw('COUNT(*) AS review_count, COALESCE(AVG(stars), 0) AS average_rating')
                ->first();

            Provider::whereKey($providerId)->update([
                'reviews_count' => $totals->review_count,
                'avg_rating' => round((float) $totals->average_rating, 2),
            ]);
        }
    }
}
