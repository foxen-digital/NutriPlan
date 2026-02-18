<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HealthMetric;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class HealthMetricController extends Controller
{
    /**
     * Display a listing of health metrics for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $query = HealthMetric::forUser($request->user())
            ->orderBy('metric_date', 'desc');

        // Filter by date range if provided
        if ($request->has(['start_date', 'end_date'])) {
            $query->dateRange($request->start_date, $request->end_date);
        }

        $metrics = $query->paginate($request->get('per_page', 15));

        return response()->json($metrics);
    }

    /**
     * Store a newly created health metric.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'metric_date' => 'required|date|before_or_equal:today',
            'weight_kg' => 'nullable|numeric|min:30|max:300',
            'walking_miles' => 'nullable|numeric|min:0|max:100',
            'water_glasses' => 'nullable|integer|min:0|max:30',
            'notes' => 'nullable|string|max:500',
        ]);

        // Check if entry already exists for this date
        $existing = HealthMetric::forUser($request->user())
            ->where('metric_date', $validated['metric_date'])
            ->first();

        if ($existing) {
            // Update existing entry
            $existing->update($validated);
            return response()->json($existing);
        }

        $metric = HealthMetric::create([
            'user_id' => $request->user()->id,
            ...$validated
        ]);

        return response()->json($metric, 201);
    }

    /**
     * Display the specified health metric.
     */
    public function show(Request $request, HealthMetric $healthMetric): JsonResponse
    {
        // Ensure user owns this metric
        if ($healthMetric->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized');
        }

        return response()->json($healthMetric);
    }

    /**
     * Update the specified health metric.
     */
    public function update(Request $request, HealthMetric $healthMetric): JsonResponse
    {
        // Ensure user owns this metric
        if ($healthMetric->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'metric_date' => 'sometimes|date|before_or_equal:today',
            'weight_kg' => 'nullable|numeric|min:30|max:300',
            'walking_miles' => 'nullable|numeric|min:0|max:100',
            'water_glasses' => 'nullable|integer|min:0|max:30',
            'notes' => 'nullable|string|max:500',
        ]);

        $healthMetric->update($validated);

        return response()->json($healthMetric);
    }

    /**
     * Remove the specified health metric.
     */
    public function destroy(Request $request, HealthMetric $healthMetric): JsonResponse
    {
        // Ensure user owns this metric
        if ($healthMetric->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized');
        }

        $healthMetric->delete();

        return response()->json(null, 204);
    }

    /**
     * Get health summary and progress toward goals.
     */
    public function summary(Request $request): JsonResponse
    {
        $user = $request->user();
        $now = now();

        // Last 30 days metrics
        $last30Days = HealthMetric::forUser($user)
            ->whereBetween('metric_date', [$now->copy()->subDays(30), $now])
            ->get();

        // Weight progress (if user has weight data)
        $weightProgress = null;
        $latestWeight = HealthMetric::getLatestWeight($user->id);
        if ($latestWeight) {
            // Assume starting weight was 25kg more than current (user's goal)
            $targetLoss = 25;
            $weightProgress = [
                'current_weight' => $latestWeight,
                'weight_lost' => round($last30Days->first()?->weight_kg - $latestWeight, 2),
                'trend' => $last30Days->whereNotNull('weight_kg')->count() > 1 ?
                    round($last30Days->whereNotNull('weight_kg')->avg('weight_kg'), 2) : null,
            ];
        }

        // Walking stats
        $walkingStats = [
            'total_miles' => round($last30Days->sum('walking_miles'), 2),
            'average_per_day' => round($last30Days->whereNotNull('walking_miles')->avg('walking_miles'), 2),
            'days_tracked' => $last30Days->whereNotNull('walking_miles')->count(),
        ];

        // Water intake
        $waterStats = [
            'average_per_day' => round($last30Days->whereNotNull('water_glasses')->avg('water_glasses'), 1),
            'days_tracked' => $last30Days->whereNotNull('water_glasses')->count(),
        ];

        return response()->json([
            'weight_progress' => $weightProgress,
            'walking_stats' => $walkingStats,
            'water_stats' => $waterStats,
            'tracking_days' => $last30Days->count(),
        ]);
    }
}
