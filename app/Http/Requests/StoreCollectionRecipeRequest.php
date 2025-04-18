<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Collection;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class StoreCollectionRecipeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Retrieve the collection model using the input ID
        $collection = Collection::query()->find($this->input('collection_id'));

        // Check if the collection exists and if the user is authorized to update it
        return $collection && Gate::allows('update', $collection);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'collection_id' => ['required', 'exists:collections,id'],
            'recipe_id' => ['required', 'exists:recipes,id'],
        ];
    }
}
