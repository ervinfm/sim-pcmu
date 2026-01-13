<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $memberId = $this->route('member') ? $this->route('member')->id : null;

        return [
            'organization_unit_id' => ['required', 'exists:organization_units,id'],
            'full_name' => ['required', 'string', 'max:255'], // SESUAIKAN DENGAN DB
            'nbm' => ['nullable', 'string', 'max:20', Rule::unique('members', 'nbm')->ignore($memberId)],
            'nik' => ['nullable', 'string', 'max:16'],
            'phone_number' => ['nullable', 'string', 'max:20'], // SESUAIKAN DENGAN DB
            'address' => ['nullable', 'string'],
            'village' => ['required', 'string', 'max:100'],
            'district' => ['required', 'string', 'max:100'],
            'regency' => ['required', 'string', 'max:100'],
            'birth_place' => ['nullable', 'string', 'max:100'],
            'birth_date' => ['nullable', 'date'],
            'gender' => ['required', 'in:L,P'],
            'job' => ['nullable', 'string', 'max:100'],
            'last_education' => ['nullable', 'string'],
            'education_institution' => ['nullable', 'string'],
            'status' => ['required', 'in:ACTIVE,INACTIVE,MOVED,DECEASED'],
            'photo' => ['nullable', 'image', 'max:2048'], // Validasi file foto
        ];
    }
}