<?php

namespace App\Http\Controllers\App\Assets;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\OrganizationUnit;
use App\Http\Requests\AssetRequest;
use Inertia\Inertia;
use Illuminate\Http\Request;

class AssetController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        $query = Asset::with('organizationUnit');

        // --- MULTI ROLE LOGIC ---
        // Jika bukan Super Admin, filter aset berdasarkan unit user
        if ($user->role !== 'super_admin' && $user->organization_unit_id) {
            $query->where('organization_unit_id', $user->organization_unit_id);
        }

        // Ambil Data (Client-side DataTable)
        $assets = $query->latest('acquisition_date')->get();

        // --- STATISTIK ---
        $stats = [
            'total_items' => $assets->count(),
            'total_value' => $assets->sum('acquisition_value'), // Nilai Perolehan
            'good_condition' => $assets->where('condition', 'BAIK')->count(),
        ];

        return Inertia::render('App/Assets/Index', [
            'assets' => $assets,
            'stats' => $stats
        ]);
    }

    public function create()
    {
        $user = auth()->user();

        // Ambil Unit (Untuk Dropdown pilihan pemilik aset)
        // Jika Admin PRM login, dia hanya bisa pilih PRM-nya sendiri
        $units = OrganizationUnit::query()
            ->select('id', 'name', 'type');

        if ($user->role !== 'super_admin' && $user->organization_unit_id) {
            $units->where('id', $user->organization_unit_id);
        }

        return Inertia::render('App/Assets/Form', [
            'units' => $units->get(),
        ]);
    }

    public function store(AssetRequest $request)
    {
        $data = $request->validated();
        
        // Auto-fill Current Value jika kosong (disamakan dengan nilai beli)
        if (empty($data['current_value'])) {
            $data['current_value'] = $data['acquisition_value'];
        }

        Asset::create($data);

        return redirect()->route('assets.index')->with('success', 'Aset berhasil didaftarkan.');
    }

    public function edit(Asset $asset)
    {
        // Security Check
        $user = auth()->user();
        if ($user->role !== 'super_admin' && $asset->organization_unit_id !== $user->organization_unit_id) {
            abort(403);
        }

        // Unit Dropdown
        $units = OrganizationUnit::query()->select('id', 'name', 'type');
        if ($user->role !== 'super_admin' && $user->organization_unit_id) {
            $units->where('id', $user->organization_unit_id);
        }

        return Inertia::render('App/Assets/Form', [
            'asset' => $asset,
            'units' => $units->get(),
        ]);
    }

    public function update(AssetRequest $request, Asset $asset)
    {
        $asset->update($request->validated());

        return redirect()->route('assets.index')->with('success', 'Data aset diperbarui.');
    }

    public function destroy(Asset $asset)
    {
        // Nanti di sini tambahkan logic hapus foto fisik jika ada
        $asset->delete();

        return redirect()->route('assets.index')->with('success', 'Aset dihapus dari inventaris.');
    }
}