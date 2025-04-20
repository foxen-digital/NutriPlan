<?php

declare(strict_types=1);

namespace App\Http\Controllers\MealPlan;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReorderMealPlanDayAssignmentsRequest;
use App\Models\MealAssignment;
use App\Models\MealPlanDay;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class MealPlanDayAssignmentOrderController extends Controller
{
    /**
     * Handle the incoming request to reorder meal assignments for a day.
     */
    public function __invoke(ReorderMealPlanDayAssignmentsRequest $request, MealPlanDay $mealPlanDay): RedirectResponse
    {
        $assignmentIds = array_map('intval', $request->validated('assignment_ids'));

        // Use a transaction to ensure atomicity when updating orders
        DB::transaction(function () use ($assignmentIds): void {
            foreach ($assignmentIds as $index => $assignmentId) {
                MealAssignment::where('id', $assignmentId)
                    ->update(['order' => $index]);
            }
        });

        return back()->with('success', 'Meal order updated successfully.');
    }
}
