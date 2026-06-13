<?php

namespace App\Http\Requests;

use App\Models\Movement;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DestroyMovementsRequest extends FormRequest
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
            'movements.*' => [
                'required',
                'uuid',
                'distinct'
            ],
        ];
    }
}
