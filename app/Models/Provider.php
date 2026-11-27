<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'description', 'photo_url', 'ine_url', 'no_crime_url', 'available', 'km_radius'])]
class Provider extends Model
{
    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'verified' => 'boolean',
            'available' => 'boolean',
            'km_radius' => 'integer',
            'avg_rating' => 'decimal:2',
            'reviews_count' => 'integer',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
