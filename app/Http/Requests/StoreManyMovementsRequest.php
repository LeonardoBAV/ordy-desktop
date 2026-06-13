<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreManyMovementsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'movements' => ['required', 'array', 'min:1'],
            'movements.*.sku' => ['required', 'string', Rule::exists((new Product)->getTable(), 'sku')],
            'movements.*.movement_uuid' => ['required', 'uuid', 'distinct'],
            'movements.*.qty' => ['required', 'integer'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'movements.*.sku.exists' => __('inventory.products.not_found_for_movement'),
        ];
    }
}
