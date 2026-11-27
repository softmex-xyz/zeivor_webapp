<?php

namespace App\Services;

use App\Models\Craft;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class CraftService
{
    /** @return Collection<int, Craft> */
    public static function getAllCrafts(): Collection
    {
        return Craft::all();
    }

    public static function getCraftById(int|string $id): ?Craft
    {
        return Craft::find($id);
    }

    /** @param array<string, mixed> $data */
    public static function createCraft(array $data): Craft
    {
        if (isset($data['name']) && Craft::where('name', $data['name'])->exists()) {
            throw ValidationException::withMessages([
                'name' => 'The name has already been taken.',
            ]);
        }

        $craft = Craft::create($data);

        if (! $craft->exists) {
            throw new RuntimeException('The craft could not be created.');
        }

        return $craft;
    }

    /** @param array<string, mixed> $data */
    public static function updateCraft(int|string $id, array $data): ?Craft
    {
        $craft = Craft::find($id);

        if ($craft && ! $craft->update($data)) {
            throw new RuntimeException('The craft could not be updated.');
        }

        return $craft;
    }

    public static function deleteCraft(int|string $id): ?Craft
    {
        $craft = Craft::find($id);

        if ($craft && ! $craft->delete()) {
            throw new RuntimeException('The craft could not be deleted.');
        }

        return $craft;
    }
}
