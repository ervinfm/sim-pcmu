<?php

namespace App\Http\Controllers\App\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class SettingController extends Controller
{
    public function edit()
    {
        // Mengambil semua setting dalam format key-value object
        $settings = SiteSetting::all()->pluck('value', 'key');

        return Inertia::render('App/Settings/Form', [
            'settings' => $settings
        ]);
    }

    public function update(Request $request)
    {
        $inputs = $request->all();

        foreach ($inputs as $key => $value) {
            $setting = SiteSetting::where('key', $key)->first();
            if (!$setting) continue;

            // 1. HANDLE FILE UPLOAD
            if ($request->hasFile($key)) {
                if ($setting->value) {
                    Storage::disk('public')->delete($setting->value);
                }
                $path = $request->file($key)->store('uploads/settings', 'public');
                $setting->update(['value' => $path]);
            } 
            // 2. HANDLE NILAI TEXT / BOOLEAN / HTML
            // Kita pastikan tidak menimpa file gambar dengan string kosong dari input file
            else if (!$request->hasFile($key) && !is_file($value)) {
                
                // Konversi Boolean 'true'/'false' dari Javascript ke '1'/'0'
                if ($value === true || $value === 'true') $value = '1';
                if ($value === false || $value === 'false') $value = '0';

                // Update hanya jika value tidak null (atau boleh null jika memang dihapus)
                $setting->update(['value' => $value]);
            }
        }

        // Reset Cache agar perubahan langsung terasa
        Cache::forget('site_settings');

        return back()->with('success', 'Pengaturan sistem berhasil diperbarui.');
    }
}