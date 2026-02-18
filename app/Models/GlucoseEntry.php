<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GlucoseEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'glucose_mmol_l',
        'reading_type',
        'measured_at',
        'notes',
    ];

    protected $casts = [
        'glucose_mmol_l' => 'decimal:2',
        'measured_at' => 'datetime',
    ];

    public const READING_TYPES = [
        'fasting' => 'Fasting',
        'pre_meal' => 'Pre-meal',
        'post_meal' => 'Post-meal',
        'bedtime' => 'Bedtime',
        'other' => 'Other',
    ];

    // UK target ranges (mmol/L)
    public const TARGET_RANGES = [
        'fasting' => ['min' => 4.0, 'max' => 7.0],
        'pre_meal' => ['min' => 4.0, 'max' => 7.0],
        'post_meal' => ['min' => 4.0, 'max' => 8.5],
        'bedtime' => ['min' => 4.0, 'max' => 8.0],
        'other' => ['min' => 4.0, 'max' => 11.0],
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get entries for a specific user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Get entries within a date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('measured_at', [$startDate, $endDate]);
    }

    /**
     * Check if reading is within target range
     */
    public function isInTargetRange(): bool
    {
        $range = self::TARGET_RANGES[$this->reading_type] ?? self::TARGET_RANGES['other'];
        return $this->glucose_mmol_l >= $range['min'] && $this->glucose_mmol_l <= $range['max'];
    }

    /**
     * Get average glucose for a user in a date range
     */
    public static function getAverageGlucose($userId, $startDate, $endDate): ?float
    {
        return static::forUser($userId)
            ->dateRange($startDate, $endDate)
            ->avg('glucose_mmol_l');
    }

    /**
     * Get glucose trend (last 7 days vs previous 7 days)
     */
    public static function getTrend($userId): array
    {
        $now = now();
        $last7Days = static::forUser($userId)
            ->whereBetween('measured_at', [$now->copy()->subDays(7), $now])
            ->avg('glucose_mmol_l');

        $previous7Days = static::forUser($userId)
            ->whereBetween('measured_at', [$now->copy()->subDays(14), $now->copy()->subDays(7)])
            ->avg('glucose_mmol_l');

        if (!$last7Days || !$previous7Days) {
            return ['trend' => 'unknown', 'change' => null];
        }

        $change = $last7Days - $previous7Days;
        $trend = $change > 0.5 ? 'increasing' : ($change < -0.5 ? 'decreasing' : 'stable');

        return [
            'trend' => $trend,
            'change' => round($change, 2),
            'current_avg' => round($last7Days, 2),
            'previous_avg' => round($previous7Days, 2),
        ];
    }
}
