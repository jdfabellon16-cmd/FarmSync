<?php

namespace App\Http\Requests;

use App\Models\Field;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFieldRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('field')) ?? false;
    }

    public function rules(): array
    {
        $field = $this->route('field');

        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:100',
                Rule::unique('fields')
                    ->where('farm_id', $this->route('farm')?->id)
                    ->whereNull('deleted_at')
                    ->ignore($field?->id),
            ],
            'area_hectares' => ['sometimes', 'required', 'numeric', 'gt:0', 'max:1000000'],
            'soil_type' => ['sometimes', 'nullable', 'string', 'max:100'],
            'irrigation_type' => ['sometimes', 'nullable', 'string', 'max:100'],
            'status' => ['sometimes', 'string', 'in:'.implode(',', Field::STATUSES)],
            'boundary' => ['sometimes', 'nullable', 'array', 'min:3'],
            'boundary.*.lat' => ['required_with:boundary', 'numeric', 'between:-90,90'],
            'boundary.*.lng' => ['required_with:boundary', 'numeric', 'between:-180,180'],
            'latitude' => ['sometimes', 'nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['sometimes', 'nullable', 'numeric', 'between:-180,180'],
        ];
    }
}
