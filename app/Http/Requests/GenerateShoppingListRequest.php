<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GenerateShoppingListRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $mealPlan = $this->route('mealPlan');
        return $mealPlan && $this->user()->can('view', $mealPlan);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Determine allowed periods based on the route-bound MealPlan
        $mealPlan = $this->route('mealPlan');
        $allowedPeriods = ['full'];

        if ($mealPlan && $mealPlan->duration === 14) {
            $allowedPeriods = ['full', 'week1', 'week2'];
        }

        return [
            'name' => ['nullable', 'string', 'max:255'], // Optional, default will be generated
            'period' => ['required', 'string', Rule::in($allowedPeriods)],
        ];
    }
}
