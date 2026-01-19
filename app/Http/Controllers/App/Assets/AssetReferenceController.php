<?php

namespace App\Http\Controllers\App\Assets;

use App\Http\Controllers\Controller;
use App\Models\AssetLocation;
use App\Models\AssetUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AssetReferenceController extends Controller
{
    /**
     * Menampilkan halaman manajemen referensi (Satuan & Lokasi).
     * Biasanya dipisah menggunakan Tab di Frontend.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // 1. Ambil Data Satuan (Global)
        // Fitur pencarian sederhana untuk Satuan
        $units = AssetUnit::query()
            ->when($request->search_unit, function ($q, $search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->get();

        // 2. Ambil Data Lokasi (Spesifik Unit Organisasi User)
        // Super Admin bisa melihat semua, User biasa hanya melihat milik unitnya
        $locations = AssetLocation::query()
            ->with('organizationUnit:id,name,type') // Eager load relasi pemilik
            ->when($user->role !== 'super_admin', function ($q) use ($user) {
                $q->where('organization_unit_id', $user->organization_unit_id);
            })
            ->when($request->search_location, function ($q, $search) {
                $q->where('name', 'like', "%{$search}%");
            })
            ->latest()
            ->get();

        return Inertia::render('App/Assets/References/Index', [
            'units' => $units,
            'locations' => $locations,
            'filters' => [
                'search_unit' => $request->search_unit,
                'search_location' => $request->search_location,
            ]
        ]);
    }

    // =========================================================================
    // SECTION 1: MANAJEMEN SATUAN (ASSET UNITS) - GLOBAL
    // =========================================================================

    public function storeUnit(Request $request)
    {
        // Validasi
        $validated = $request->validate([
            'code' => 'required|string|max:10|unique:asset_units,code|uppercase', // Kode wajib unik & huruf besar
            'name' => 'required|string|max:100',
        ], [
            'code.unique' => 'Kode satuan ini sudah terdaftar.',
            'code.uppercase' => 'Kode harus huruf kapital.'
        ]);

        AssetUnit::create($validated);

        return back()->with('success', 'Satuan barang berhasil ditambahkan.');
    }

    public function updateUnit(Request $request, AssetUnit $unit)
    {
        // Validasi (Ignore ID sendiri saat cek unique)
        $validated = $request->validate([
            'code' => 'required|string|max:10|uppercase|unique:asset_units,code,' . $unit->id,
            'name' => 'required|string|max:100',
        ]);

        $unit->update($validated);

        return back()->with('success', 'Satuan barang diperbarui.');
    }

    public function destroyUnit(AssetUnit $unit)
    {
        // Cek apakah satuan sedang dipakai oleh Aset?
        if ($unit->assets()->exists()) {
            return back()->with('error', 'Gagal: Satuan ini sedang digunakan oleh data aset. Hapus asetnya terlebih dahulu.');
        }

        $unit->delete();

        return back()->with('success', 'Satuan barang dihapus.');
    }

    // =========================================================================
    // SECTION 2: MANAJEMEN LOKASI (ASSET LOCATIONS) - PER UNIT
    // =========================================================================

    public function storeLocation(Request $request)
    {
        $user = auth()->user();

        // Validasi
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'description' => 'nullable|string|max:500',
        ]);

        // Paksa Unit ID sesuai User yang login (Kecuali Super Admin bisa pilih, tapi untuk simplifikasi kita kunci ke user)
        if (!$user->organization_unit_id && $user->role !== 'super_admin') {
            return back()->with('error', 'Anda tidak terasosiasi dengan unit organisasi manapun.');
        }

        AssetLocation::create([
            'organization_unit_id' => $user->organization_unit_id, // Link ke Unit User
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        return back()->with('success', 'Lokasi aset baru berhasil ditambahkan.');
    }

    public function updateLocation(Request $request, AssetLocation $location)
    {
        $user = auth()->user();

        // Authorization Check: Pastikan user hanya edit lokasi miliknya
        if ($user->role !== 'super_admin' && $location->organization_unit_id !== $user->organization_unit_id) {
            abort(403, 'Anda tidak berhak mengedit lokasi unit lain.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'description' => 'nullable|string|max:500',
        ]);

        $location->update($validated);

        return back()->with('success', 'Data lokasi diperbarui.');
    }

    public function destroyLocation(AssetLocation $location)
    {
        $user = auth()->user();

        // Authorization Check
        if ($user->role !== 'super_admin' && $location->organization_unit_id !== $user->organization_unit_id) {
            abort(403, 'Anda tidak berhak menghapus lokasi unit lain.');
        }

        // Cek Dependensi: Jangan hapus jika ada aset di lokasi ini
        if ($location->assets()->exists()) {
            return back()->with('error', 'Gagal: Masih ada aset yang tercatat di lokasi ini. Pindahkan aset terlebih dahulu.');
        }

        $location->delete();

        return back()->with('success', 'Lokasi berhasil dihapus.');
    }
}