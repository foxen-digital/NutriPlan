<?php

declare(strict_types=1);

namespace App\Http\Requests\Ingredient;

use Illuminate\Foundation\Http\FormRequest;

class NormalizeIngredientsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ingredients' => ['required', 'array', 'min:1', 'max:50'],
            'ingredients.*' => ['required', 'string', 'max:255'],
        ];
    }
}
