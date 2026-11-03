<?php

namespace App\Http\Requests;

use App\Models\Farm;
use App\Models\Field;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFieldRequest extends FormRequest
{
    public function authorize(): bool
    {
        $farm = $this->route('farm');

        return $farm instanceof Farm && ($this->user()?->can('manageFields', $farm) ?? false);
    }

    public function rules(): array
    {
        $farm = $this->route('farm');

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('fields')->where('farm_id', $farm?->id)->whereNull('deleted_at'),
            ],
            'area_hectares' => ['required', 'numeric', 'gt:0', 'max:1000000'],
            'soil_type' => ['nullable', 'string', 'max:100'],
            'irrigation_type' => ['nullable', 'string', 'max:100'],
            'status' => ['sometimes', 'string', 'in:'.implode(',', Field::STATUSES)],
            'boundary' => ['nullable', 'array', 'min:3'],
            'boundary.*.lat' => ['required_with:boundary', 'numeric', 'between:-90,90'],
            'boundary.*.lng' => ['required_with:boundary', 'numeric', 'between:-180,180'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ];
    }
}
