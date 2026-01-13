<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OrganizationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Izinkan semua admin yang sudah login
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'in:PCM,PRM,MASJID,SEKOLAH,KLINIK,PANTI,LAINNYA'],
            'parent_id' => ['nullable', 'exists:organization_units,id'],
            
            // Data Administratif
            'sk_number' => ['nullable', 'string', 'max:100'],
            'establishment_date' => ['nullable', 'date'],
            'address' => ['nullable', 'string'],
            'email' => ['nullable', 'email', 'max:100'],
            'phone' => ['nullable', 'string', 'max:50'],
            
            // Sosmed & Web
            'facebook' => ['nullable', 'string', 'max:100'],
            'instagram' => ['nullable', 'string', 'max:100'],
            'website' => ['nullable', 'url', 'max:255'],
            
            // Peta (GIS)
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            
            // Profil Web
            'description' => ['nullable', 'string'],
            
            // Upload Logo (Maksimal 2MB)
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],

            // Khusus PRM: Array ID Desa yang dipilih
            'villages' => ['nullable', 'array'],
            'villages.*' => ['exists:villages,id'],
        ];
    }
}