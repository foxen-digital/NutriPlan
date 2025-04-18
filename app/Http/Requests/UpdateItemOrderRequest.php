<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateItemOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        /** @var \App\Models\ShoppingList|null $shoppingList */
        $shoppingList = $this->route('shoppingList');

        // Check if the shopping list exists and the authenticated user owns it.
        return $shoppingList && $shoppingList->user_id === $this->user()->id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'item_ids' => ['required', 'array'],
            'item_ids.*' => [
                'required',
                'integer',
                Rule::exists('shopping_list_items', 'id')
                    ->where('shopping_list_id', $this->route('shoppingList')->id)
            ],
        ];
    }
}
