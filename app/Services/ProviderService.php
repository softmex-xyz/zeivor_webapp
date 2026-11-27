<?php

namespace App\Services;

use App\Models\Provider;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class ProviderService
{
    /** @return Collection<int, Provider> */
    public static function getAllProviders(): Collection
    {
        return Provider::all();
    }

    public static function getProviderById(int|string $id): ?Provider
    {
        return Provider::find($id);
    }

    /** @param array<string, mixed> $data */
    public static function createProvider(array $data): Provider
    {
        self::rejectSystemManagedFields($data);

        if (isset($data['user_id']) && Provider::where('user_id', $data['user_id'])->exists()) {
            throw ValidationException::withMessages([
                'user_id' => 'The user already has a provider profile.',
            ]);
        }

        $provider = Provider::create($data);

        if (! $provider->exists) {
            throw new RuntimeException('The provider could not be created.');
        }

        return $provider;
    }

    /** @param array<string, mixed> $data */
    public static function updateProvider(int|string $id, array $data): ?Provider
    {
        $provider = Provider::find($id);

        if ($provider) {
            self::rejectSystemManagedFields($data);

            if (isset($data['user_id']) && Provider::where('user_id', $data['user_id'])->where('id', '!=', $provider->id)->exists()) {
                throw ValidationException::withMessages([
                    'user_id' => 'The user already has a provider profile.',
                ]);
            }

            if (! $provider->update($data)) {
                throw new RuntimeException('The provider could not be updated.');
            }
        }

        return $provider;
    }

    public static function deleteProvider(int|string $id): ?Provider
    {
        $provider = Provider::find($id);

        if ($provider && ! $provider->delete()) {
            throw new RuntimeException('The provider could not be deleted.');
        }

        return $provider;
    }

    /** @param array<string, mixed> $data */
    private static function rejectSystemManagedFields(array $data): void
    {
        foreach (['verified', 'avg_rating', 'reviews_count'] as $field) {
            if (array_key_exists($field, $data)) {
                throw ValidationException::withMessages([
                    $field => 'This field is managed by the system.',
                ]);
            }
        }
    }
}
