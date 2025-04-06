<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\MealPlan;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class MealPlanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response|JsonResponse
    {
        $mealPlans = MealPlan::query()
            ->where('user_id', Auth::id())
            ->orderBy('start_date', 'asc')
            ->get();

        // Calculate end dates and sort with current/future plans first
        $sortedMealPlans = $mealPlans->sortBy(function (MealPlan $mealPlan): array {
            $endDate = date('Y-m-d', strtotime($mealPlan->start_date . ' + ' . $mealPlan->duration . ' days'));
            $isPast = $endDate < date('Y-m-d');

            // Return a tuple for sorting: [isPast, start_date]
            // This puts all non-past plans first, then sorts by start date within each group
            return [$isPast, $mealPlan->start_date];
        })->values();

        // Check if this is an AJAX request or if format=json query parameter is present
        if (request()->query('format') === 'json') {
            return response()->json([
                'mealPlans' => $sortedMealPlans
            ]);
        }

        return Inertia::render('MealPlans/Index', [
            'mealPlans' => $sortedMealPlans,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('MealPlans/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'start_date' => 'required|date',
            'duration' => 'required|integer|in:7,14',
            'people_count' => 'required|integer|min:1|max:20',
        ]);

        /** @var User|null $user */
        $user = $request->user();

        $mealPlan = null;
        if ($user !== null) {
            $mealPlan = $user->mealPlans()->create($validated);

            // Create meal plan days
            for ($i = 1; $i <= $mealPlan->duration; $i++) {
                $mealPlan->days()->create(['day_number' => $i]);
            }
        }

        return redirect()->route('meal-plans.index')
            ->with('success', 'Meal plan created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(MealPlan $mealPlan): Response
    {
        if (Gate::denies('view', $mealPlan)) {
            abort(403);
        }

        $mealPlan->load([
            'recipes',
            'days.mealAssignments.mealPlanRecipe.recipe',
        ]);

        // Fetch available meal plans for the current user (excluding the current one)
        $availableMealPlans = MealPlan::query()
            ->where('user_id', Auth::id())
            ->where('id', '!=', $mealPlan->id)
            ->orderBy('name') // Or latest(), start_date, etc.
            ->select('id', 'name', 'start_date') // Select only needed fields
            ->get();

        return Inertia::render('MealPlans/Show', [
            'mealPlan' => $mealPlan,
            'availableMealPlans' => $availableMealPlans, // Pass the data
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): null
    {
        return null;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): null
    {
        return null;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MealPlan $mealPlan): RedirectResponse
    {
        Gate::authorize('delete', $mealPlan);

        $mealPlan->delete();

        return redirect()->route('meal-plans.index')
            ->with('success', 'Meal plan deleted successfully.');
    }
}
