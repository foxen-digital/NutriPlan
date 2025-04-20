<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\MealPlanDay;
use App\Models\MealAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

class MealPlanDayAssignmentOrderController extends Controller
{
    /**
     * Handle the incoming request to reorder meal assignments for a day.
     *
     * @param Request $request
     * @param MealPlanDay $mealPlanDay
     * @return RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws ValidationException
     */
    public function __invoke(Request $request, MealPlanDay $mealPlanDay): RedirectResponse
    {
        // Authorize: Ensure the user owns the meal plan this day belongs to
        Gate::authorize('update', $mealPlanDay->mealPlan);

        // Validate the request structure and basic types
        $validated = $request->validate([
            'assignment_ids' => 'required|array',
            'assignment_ids.*' => 'required|integer|exists:meal_assignments,id',
        ]);

        // Cast all IDs to integers for consistent comparison
        $requestedAssignmentIds = array_map('intval', $validated['assignment_ids']);

        // Fetch actual assignment IDs belonging to this day from the database
        $actualAssignmentIdsOnDay = MealAssignment::where('meal_plan_day_id', $mealPlanDay->id)
            ->pluck('id')
            ->map(fn($id) => (int) $id)
            ->toArray();

        // Validate that we have the correct number of assignments
        if (count($requestedAssignmentIds) !== count($actualAssignmentIdsOnDay)) {
            throw ValidationException::withMessages([
                'assignment_ids' => 'The provided list does not contain the correct number of assignments for the specified meal plan day.',
            ]);
        }

        // Validate that all IDs belong to this day (exact match after sorting)
        $sortedRequested = $requestedAssignmentIds;
        $sortedActual = $actualAssignmentIdsOnDay;
        sort($sortedRequested);
        sort($sortedActual);
        
        if ($sortedRequested !== $sortedActual) {
            throw ValidationException::withMessages([
                'assignment_ids' => 'One or more assignment IDs are incorrect for the specified meal plan day.',
            ]);
        }

        // Use a transaction to ensure atomicity when updating orders
        DB::transaction(function () use ($requestedAssignmentIds) {
            foreach ($requestedAssignmentIds as $index => $assignmentId) {
                MealAssignment::where('id', $assignmentId)
                    ->update(['order' => $index]);
            }
        });

        return back()->with('success', 'Meal order updated successfully.');
    }
}
