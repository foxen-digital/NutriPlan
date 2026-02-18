<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GlucoseEntry;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class GlucoseEntryController extends Controller
{
    /**
     * Display a listing of glucose entries for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $query = GlucoseEntry::forUser($request->user())
            ->orderBy('measured_at', 'desc');

        // Filter by date range if provided
        if ($request->has(['start_date', 'end_date'])) {
            $query->dateRange($request->start_date, $request->end_date);
        }

        // Filter by reading type if provided
        if ($request->has('reading_type')) {
            $query->where('reading_type', $request->reading_type);
        }

        $entries = $query->paginate($request->get('per_page', 15));

        return response()->json($entries);
    }

    /**
     * Store a newly created glucose entry.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'glucose_mmol_l' => 'required|numeric|min:1|max:30',
            'reading_type' => 'required|in:' . implode(',', array_keys(GlucoseEntry::READING_TYPES)),
            'measured_at' => 'required|date|before_or_equal:now',
            'notes' => 'nullable|string|max:500',
        ]);

        $entry = GlucoseEntry::create([
            'user_id' => $request->user()->id,
            'glucose_mmol_l' => $validated['glucose_mmol_l'],
            'reading_type' => $validated['reading_type'],
            'measured_at' => $validated['measured_at'],
            'notes' => $validated['notes'] ?? null,
        ]);

        return response()->json($entry, 201);
    }

    /**
     * Display the specified glucose entry.
     */
    public function show(Request $request, GlucoseEntry $glucoseEntry): JsonResponse
    {
        // Ensure user owns this entry
        if ($glucoseEntry->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized');
        }

        return response()->json($glucoseEntry);
    }

    /**
     * Update the specified glucose entry.
     */
    public function update(Request $request, GlucoseEntry $glucoseEntry): JsonResponse
    {
        // Ensure user owns this entry
        if ($glucoseEntry->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'glucose_mmol_l' => 'sometimes|numeric|min:1|max:30',
            'reading_type' => 'sometimes|in:' . implode(',', array_keys(GlucoseEntry::READING_TYPES)),
            'measured_at' => 'sometimes|date|before_or_equal:now',
            'notes' => 'nullable|string|max:500',
        ]);

        $glucoseEntry->update($validated);

        return response()->json($glucoseEntry);
    }

    /**
     * Remove the specified glucose entry.
     */
    public function destroy(Request $request, GlucoseEntry $glucoseEntry): JsonResponse
    {
        // Ensure user owns this entry
        if ($glucoseEntry->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized');
        }

        $glucoseEntry->delete();

        return response()->json(null, 204);
    }

    /**
     * Get glucose statistics and trends.
     */
    public function statistics(Request $request): JsonResponse
    {
        $user = $request->user();
        $now = now();

        // Last 30 days stats
        $last30Days = GlucoseEntry::forUser($user)
            ->whereBetween('measured_at', [$now->copy()->subDays(30), $now])
            ->get();

        // By reading type
        $byType = $last30Days->groupBy('reading_type')->map(function ($entries) {
            return [
                'count' => $entries->count(),
                'average' => round($entries->avg('glucose_mmol_l'), 2),
                'min' => $entries->min('glucose_mmol_l'),
                'max' => $entries->max('glucose_mmol_l'),
                'in_target' => $entries->filter(fn($e) => $e->isInTargetRange())->count(),
            ];
        });

        // Trend analysis
        $trend = GlucoseEntry::getTrend($user->id);

        return response()->json([
            'last_30_days' => [
                'total_entries' => $last30Days->count(),
                'average' => round($last30Days->avg('glucose_mmol_l'), 2),
                'by_type' => $byType,
            ],
            'trend' => $trend,
        ]);
    }
}
