<?php

declare(strict_types=1);

namespace App\Http\Controllers\MealPlan;

use App\Http\Controllers\Controller;
use App\Http\Requests\MealPlanCopyRequest;
use App\Models\MealPlan;
use App\Services\MealPlanCopyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class MealPlanCopyController extends Controller
{
    /**
     * Invoke the controller to copy a meal plan.
     */
    public function __invoke(MealPlanCopyRequest $request, MealPlan $mealPlan, MealPlanCopyService $copyService): RedirectResponse
    {
        $user = Auth::user();
        $validatedData = $request->validated();

        $newMealPlan = $copyService->copy($mealPlan, $user, $validatedData);

        return redirect()->route('meal-plans.show', $newMealPlan)
            ->with('success', 'Meal plan copied successfully.');
    }
}
