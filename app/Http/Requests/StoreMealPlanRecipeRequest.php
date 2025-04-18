<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\MealPlan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class StoreMealPlanRecipeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $mealPlanId = $this->input('meal_plan_id');
        
        if (!$mealPlanId) {
            return false;
        }
        
        $mealPlan = MealPlan::find($mealPlanId);
        
        if (!$mealPlan) {
            return false;
        }
        
        return Gate::allows('update', $mealPlan);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'meal_plan_id' => 'required|exists:meal_plans,id',
            'recipe_id' => 'required|exists:recipes,id',
            'scale_factor' => 'nullable|numeric|min:0.01|max:100',
        ];
    }
    
    /**
     * Handle a failed authorization attempt.
     *
     * @return void
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    protected function failedAuthorization()
    {
        // Only throw an authorization exception if the validation would pass
        if ($this->input('meal_plan_id') && MealPlan::find($this->input('meal_plan_id'))) {
            parent::failedAuthorization();
        }
        // Otherwise let the validation handle it
    }
}
