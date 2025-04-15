<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class ImportRecipeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * For API requests, authorization is handled by the auth:sanctum middleware,
     * so this method will always return true.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'url' => [
                'required',
                'url',
                'max:2048',
                function (string $attribute, mixed $value, callable $fail): void {
                    $parsed = parse_url($value);
                    if (empty($parsed['scheme']) || !in_array($parsed['scheme'], ['http', 'https'])) {
                        $fail('The URL must use either http or https protocol.');
                    }
                },
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'url.required' => 'Please provide a recipe URL.',
            'url.url' => 'Please provide a valid URL.',
            'url.max' => 'The URL is too long. Maximum length is 2048 characters.',
        ];
    }
}
