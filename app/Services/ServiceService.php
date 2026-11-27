<?php

namespace App\Services;

use App\Models\Review;
use App\Models\Service;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class ServiceService
{
    /** @return Collection<int, Service> */
    public static function getAllServices(): Collection
    {
        return Service::all();
    }

    public static function getServiceById(int|string $id): ?Service
    {
        return Service::find($id);
    }

    /** @param array<string, mixed> $data */
    public static function createService(array $data): Service
    {
        $service = Service::create(self::validateService($data));

        if (! $service->exists) {
            throw new RuntimeException('The service could not be created.');
        }

        return $service;
    }

    /** @param array<string, mixed> $data */
    public static function updateService(int|string $id, array $data): ?Service
    {
        return DB::transaction(function () use ($id, $data): ?Service {
            $service = Service::whereKey($id)->lockForUpdate()->first();

            if (! $service) {
                return null;
            }

            $data = self::validateService($data, updating: true);
            $review = Review::where('service_id', $service->id)->lockForUpdate()->first();

            if ($review) {
                $clientId = $data['client_id'] ?? $service->client_id;
                $providerId = array_key_exists('provider_id', $data) ? $data['provider_id'] : $service->provider_id;
                $status = $data['status'] ?? $service->status;

                if ((int) $clientId !== (int) $review->client_id) {
                    throw ValidationException::withMessages([
                        'client_id' => 'The client must match the existing review.',
                    ]);
                }

                if ($providerId === null || (int) $providerId !== (int) $review->provider_id) {
                    throw ValidationException::withMessages([
                        'provider_id' => 'The provider must match the existing review.',
                    ]);
                }

                if ($status !== 'completed') {
                    throw ValidationException::withMessages([
                        'status' => 'A reviewed service must remain completed.',
                    ]);
                }
            }

            if (! $service->update($data)) {
                throw new RuntimeException('The service could not be updated.');
            }

            return $service;
        }, 3);
    }

    public static function deleteService(int|string $id): ?Service
    {
        return DB::transaction(function () use ($id): ?Service {
            $service = Service::whereKey($id)->lockForUpdate()->first();

            if (! $service) {
                return null;
            }

            $review = Review::where('service_id', $service->id)->lockForUpdate()->first();

            if ($review && ! ReviewService::deleteReview($review->id)) {
                throw new RuntimeException('The review could not be deleted.');
            }

            if (! $service->delete()) {
                throw new RuntimeException('The service could not be deleted.');
            }

            return $service;
        }, 3);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private static function validateService(array $data, bool $updating = false): array
    {
        $required = $updating ? ['sometimes', 'required'] : ['required'];

        return Validator::make($data, [
            'client_id' => [...$required, 'integer', 'exists:users,id'],
            'provider_id' => ['nullable', 'integer', 'exists:providers,id'],
            'craft_id' => [...$required, 'integer', 'exists:crafts,id'],
            'description' => [...$required, 'string'],
            'address' => ['nullable', 'string', 'max:500'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'status' => ['sometimes', 'required', Rule::in([
                'pending', 'accepted', 'rejected', 'under_negotiation',
                'confirmed', 'in_progress', 'completed', 'cancelled',
            ])],
            'agreed_price' => ['nullable', 'numeric', 'between:0,99999999.99'],
            'service_datetime' => ['nullable', 'date'],
        ])->validate();
    }
}
