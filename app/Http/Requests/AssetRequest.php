<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $assetId = $this->route('asset') ? $this->route('asset')->id : null;

        return [
            'organization_unit_id' => ['required', 'exists:organization_units,id'],
            'inventory_code' => [
                'required', 
                'string', 
                'max:50', 
                Rule::unique('assets', 'inventory_code')->ignore($assetId)
            ],
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string'], // TANAH, BANGUNAN, dll
            'acquisition_date' => ['required', 'date'],
            'acquisition_value' => ['required', 'numeric', 'min:0'],
            'current_value' => ['nullable', 'numeric', 'min:0'],
            'condition' => ['required', 'in:BAIK,RUSAK_RINGAN,RUSAK_BERAT,HILANG,PEMUSNAHAN'],
            'location' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ];
    }
}