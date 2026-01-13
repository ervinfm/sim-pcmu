<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\User;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
        // Cek apakah ini proses update (ada user ID di route)
        $userId = $this->route('user') ? $this->route('user')->id : null;
        $passwordRules = $userId ? ['nullable', 'string', 'min:8', 'confirmed'] : ['required', 'string', 'min:8', 'confirmed'];

        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['nullable', 'string', 'max:50', Rule::unique(User::class)->ignore($userId)],
            'email' => ['required', 'email', 'max:255', Rule::unique(User::class)->ignore($userId)],
            'role' => ['required', 'in:super_admin,admin_prm,takmir,member'],
            'organization_unit_id' => ['nullable', 'exists:organization_units,id'],
            'is_active' => ['boolean'],
            'password' => $passwordRules,
        ];
    }
}