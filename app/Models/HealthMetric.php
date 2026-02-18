<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HealthMetric extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'metric_date',
        'weight_kg',
        'walking_miles',
        'water_glasses',
        'notes',
    ];

    protected $casts = [
        'metric_date' => 'date',
        'weight_kg' => 'decimal:2',
        'walking_miles' => 'decimal:2',
        'water_glasses' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get metrics for a specific user within a date range
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Get metrics within a date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('metric_date', [$startDate, $endDate]);
    }

    /**
     * Get the latest weight entry for a user
     */
    public static function getLatestWeight($userId): ?float
    {
        $metric = static::forUser($userId)
            ->whereNotNull('weight_kg')
            ->orderBy('metric_date', 'desc')
            ->first();

        return $metric?->weight_kg;
    }
}
