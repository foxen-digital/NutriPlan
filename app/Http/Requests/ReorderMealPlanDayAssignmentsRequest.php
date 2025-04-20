<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\MealAssignment;
use App\Models\MealPlanDay;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class ReorderMealPlanDayAssignmentsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $mealPlanDay = $this->route('meal_plan_day');

        // For unit tests, we might have the mealPlanDay set directly as a property
        if (!$mealPlanDay && (property_exists($this, 'mealPlanDay') && $this->mealPlanDay !== null)) {
            $mealPlanDay = $this->mealPlanDay;
        }

        // If we can't get the meal plan day, deny authorization
        if (!$mealPlanDay) {
            return false;
        }

        return $this->user()->can('update', $mealPlanDay->mealPlan);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'assignment_ids' => 'required|array',
            'assignment_ids.*' => 'required|integer|exists:meal_assignments,id',
        ];
    }

    /**
     * Handle additional validation after main validation rules pass.
     *
     * @throws ValidationException
     */
    public function after(): array
    {
        return [
            function (): void {
                $this->validateAssignmentIds();
            }
        ];
    }

    /**
     * Validate that all assignment IDs belong to the specified meal plan day.
     *
     * @throws ValidationException
     */
    protected function validateAssignmentIds(): void
    {
        $mealPlanDay = $this->route('meal_plan_day');

        // For unit tests, we might have the mealPlanDay set directly as a property
        if (!$mealPlanDay && (property_exists($this, 'mealPlanDay') && $this->mealPlanDay !== null)) {
            $mealPlanDay = $this->mealPlanDay;
        }

        // If we can't get the meal plan day, fail validation
        if (!$mealPlanDay) {
            throw ValidationException::withMessages([
                'meal_plan_day' => 'The meal plan day is required.',
            ]);
        }

        $requestedAssignmentIds = array_map('intval', $this->validated('assignment_ids'));

        // Fetch all assignment IDs belonging to this meal plan day
        $actualAssignmentIdsOnDay = MealAssignment::where('meal_plan_day_id', $mealPlanDay->id)
            ->pluck('id')
            ->map(fn (mixed $id): int => (int) $id)
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
    }
}
