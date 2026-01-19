<?php

namespace App\Http\Controllers\App\Assets;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetImage;
use App\Models\AssetDocument;
use App\Models\AssetLocation;
use App\Models\AssetUnit;
use App\Models\OrganizationUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class AssetController extends Controller
{
    /**
     * Schema Template untuk Spesifikasi Aset (JSON)
     * Frontend akan menggunakan ini untuk merender form input dinamis.
     */
    private function getCategorySchemas()
    {
        return [
            'LAND' => [
                'label' => 'Tanah',
                'fields' => [
                    ['key' => 'luas', 'label' => 'Luas Tanah (m2)', 'type' => 'number'],
                    ['key' => 'nomor_sertifikat', 'label' => 'No. Sertifikat / AIW', 'type' => 'text'],
                    ['key' => 'alamat', 'label' => 'Alamat Lokasi', 'type' => 'textarea'],
                    ['key' => 'peruntukan', 'label' => 'Peruntukan Lahan', 'type' => 'text'],
                ]
            ],
            'BUILDING' => [
                'label' => 'Bangunan',
                'fields' => [
                    ['key' => 'luas_bangunan', 'label' => 'Luas Bangunan (m2)', 'type' => 'number'],
                    ['key' => 'jumlah_lantai', 'label' => 'Jumlah Lantai', 'type' => 'number'],
                    ['key' => 'imb', 'label' => 'Nomor IMB', 'type' => 'text'],
                    ['key' => 'konstruksi', 'label' => 'Jenis Konstruksi', 'type' => 'text'],
                ]
            ],
            'VEHICLE' => [
                'label' => 'Kendaraan',
                'fields' => [
                    ['key' => 'nopol', 'label' => 'Nomor Polisi', 'type' => 'text'],
                    ['key' => 'merk', 'label' => 'Merk', 'type' => 'text'],
                    ['key' => 'tipe', 'label' => 'Tipe / Model', 'type' => 'text'],
                    ['key' => 'tahun_rakitan', 'label' => 'Tahun Pembuatan', 'type' => 'number'],
                    ['key' => 'no_rangka', 'label' => 'Nomor Rangka', 'type' => 'text'],
                    ['key' => 'no_mesin', 'label' => 'Nomor Mesin', 'type' => 'text'],
                    ['key' => 'pajak_stnk', 'label' => 'Tgl Habis Pajak (STNK)', 'type' => 'date'],
                ]
            ],
            'ELECTRONIC' => [
                'label' => 'Elektronik',
                'fields' => [
                    ['key' => 'brand', 'label' => 'Brand / Merk', 'type' => 'text'],
                    ['key' => 'model', 'label' => 'Model / Seri', 'type' => 'text'],
                    ['key' => 'serial_number', 'label' => 'Serial Number (SN)', 'type' => 'text'],
                    ['key' => 'spesifikasi_teknis', 'label' => 'Spek Teknis (RAM/Processor)', 'type' => 'text'],
                ]
            ],
            'FURNITURE' => ['label' => 'Meubelair', 'fields' => [['key' => 'bahan', 'label' => 'Bahan', 'type' => 'text']]],
            'MACHINERY' => ['label' => 'Mesin', 'fields' => [['key' => 'serial_number', 'label' => 'Serial Number', 'type' => 'text']]],
            'OTHER' => ['label' => 'Lainnya', 'fields' => [['key' => 'keterangan', 'label' => 'Keterangan Spesifik', 'type' => 'text']]],
        ];
    }

    public function index(Request $request)
    {
        $user = auth()->user();

        // 1. Query Dasar (Ambil Relasi)
        $query = Asset::with(['location', 'unit', 'images']);

        // 2. Security: User non-superadmin hanya lihat unitnya sendiri
        if ($user->role !== 'super_admin' && $user->organization_unit_id) {
            $query->where('organization_unit_id', $user->organization_unit_id);
        }

        // 3. GET ALL DATA (Client-side handling)
        // Kita hapus filter server-side & pagination di sini.
        // Biarkan Vue JS yang mengurus filtering dari data mentah ini.
        $assets = $query->latest('acquisition_date')->get(); 

        // 4. Statistik Ringkas
        $stats = [
            'total_assets' => $assets->count(),
            'total_value' => $assets->sum('acquisition_value'),
            'maintenance_count' => $assets->whereIn('status', ['MAINTENANCE', 'BROKEN'])->count(),
        ];

        // 5. Data Referensi Dropdown
        $locations = AssetLocation::query()
            ->when($user->role !== 'super_admin', fn($q) => $q->where('organization_unit_id', $user->organization_unit_id))
            ->get();

        return Inertia::render('App/Assets/Index', [
            'assets' => $assets, // Ini sekarang Array penuh, bukan Object Paginator
            'stats' => $stats,
            'locations' => $locations,
            'categories' => $this->getCategorySchemas(),
        ]);
    }

    public function create()
    {
        $user = auth()->user();

        // 1. Dropdown Unit Organisasi (Untuk Super Admin, user biasa nanti otomatis terkunci di Frontend)
        $units = OrganizationUnit::select('id', 'name')->get();
        
        // 2. Filter Lokasi (Hanya tampilkan lokasi milik unit user tersebut)
        $locations = AssetLocation::query()
            ->when($user->role !== 'super_admin', fn($q) => $q->where('organization_unit_id', $user->organization_unit_id))
            ->get();
            
        // 3. Ambil Semua Data Satuan (Global Reference)
        $assetUnits = AssetUnit::orderBy('name')->get(); 

        return Inertia::render('App/Assets/Form', [
            'schemas' => $this->getCategorySchemas(),
            'organization_units' => $units,
            'locations' => $locations,
            'asset_units' => $assetUnits,
            'user_organization_id' => $user->organization_unit_id // Default value untuk form
        ]);
    }

    public function store(Request $request)
    {
        // 1. Validasi
        $val = $request->validate([
            'organization_unit_id' => 'required|exists:organization_units,id',
            'name' => 'required|string|max:255',
            'category' => 'required|string',
            'asset_unit_id' => 'required|exists:asset_units,id',
            'asset_location_id' => 'nullable|exists:asset_locations,id',
            'acquisition_date' => 'required|date',
            'acquisition_value' => 'required|numeric|min:0',
            'source_of_acquisition' => 'required|string', // PURCHASE, WAKAF, dll
            'condition' => 'required|string',
            'specifications' => 'nullable|array', // JSON Spec
            'images.*' => 'nullable|image|max:2048', // Max 2MB per foto
            'documents.*' => 'nullable|file|max:5120', // Max 5MB per dokumen
        ]);

        DB::beginTransaction();
        try {
            // 2. Generate Inventory Code Otomatis
            // Format: INV/[UNIT_ID]/[TAHUN]/[URUT] -> Contoh: INV/12/2026/0005
            $year = date('Y', strtotime($val['acquisition_date']));
            // Hitung urutan berdasarkan tahun berjalan
            $count = Asset::whereYear('created_at', date('Y'))->count() + 1;
            $sequence = str_pad($count, 4, '0', STR_PAD_LEFT);
            $inventoryCode = "INV/{$val['organization_unit_id']}/{$year}/{$sequence}";

            // 3. Create Asset
            $asset = Asset::create([
                'organization_unit_id' => $val['organization_unit_id'],
                'user_id' => auth()->id(),
                'inventory_code' => $inventoryCode,
                'name' => $val['name'],
                'category' => $val['category'],
                'asset_unit_id' => $val['asset_unit_id'],
                'asset_location_id' => $val['asset_location_id'],
                'acquisition_date' => $val['acquisition_date'],
                'acquisition_value' => $val['acquisition_value'],
                'current_value' => $val['acquisition_value'], // Nilai awal = Nilai beli
                'source_of_acquisition' => $val['source_of_acquisition'],
                'condition' => $val['condition'],
                'status' => 'ACTIVE', // Default status
                'specifications' => $val['specifications'] ?? [],
            ]);

            // 4. Handle Upload Images (Gallery)
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $file) {
                    $path = $file->store('assets/images', 'public');
                    AssetImage::create([
                        'asset_id' => $asset->id,
                        'image_path' => $path,
                        'is_primary' => $index === 0, // Foto pertama otomatis jadi cover
                    ]);
                }
            }

            // 5. Handle Upload Documents
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $file) {
                    $path = $file->store('assets/documents', 'public');
                    AssetDocument::create([
                        'asset_id' => $asset->id,
                        'name' => $file->getClientOriginalName(),
                        'file_path' => $path,
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('assets.index')
                ->with('success', 'Aset berhasil didaftarkan. Kode Inventaris: ' . $inventoryCode);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyimpan aset: ' . $e->getMessage());
        }
    }

    public function show(Asset $asset)
    {
        // Load semua relasi yang dibutuhkan untuk halaman Detail (Passport View)
        $asset->load([
            'organizationUnit', 
            'location', 
            'unit', 
            'images', 
            'documents', 
            'loans.user', // Riwayat Peminjaman
            'activeLoan'  // Peminjaman Aktif (jika ada)
        ]);

        return Inertia::render('App/Assets/Show', [
            'asset' => $asset,
            'qr_code_url' => route('assets.print-label', $asset->id), // Link untuk tombol print
        ]);
    }

    public function edit(Asset $asset)
    {
        $asset->load(['images', 'documents']);
        
        $user = auth()->user();
        $units = OrganizationUnit::select('id', 'name')->get();
        $locations = AssetLocation::query()
            ->when($user->role !== 'super_admin', fn($q) => $q->where('organization_unit_id', $user->organization_unit_id))
            ->get();
        $assetUnits = AssetUnit::all();

        return Inertia::render('App/Assets/Form', [
            'asset' => $asset, // Data existing untuk diedit
            'schemas' => $this->getCategorySchemas(),
            'organization_units' => $units,
            'locations' => $locations,
            'asset_units' => $assetUnits,
            'user_organization_id' => $user->organization_unit_id
        ]);
    }

    public function update(Request $request, Asset $asset)
    {
        // Validasi Update (Tidak validasi file wajib, karena opsional saat edit)
        $val = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string',
            'asset_location_id' => 'nullable|exists:asset_locations,id',
            'condition' => 'required|string',
            'specifications' => 'nullable|array',
            'new_images.*' => 'nullable|image|max:2048',
        ]);

        DB::beginTransaction();
        try {
            $asset->update([
                'name' => $val['name'],
                'category' => $val['category'],
                'asset_location_id' => $val['asset_location_id'],
                'condition' => $val['condition'],
                'specifications' => $val['specifications'],
            ]);

            // Handle Foto Baru Tambahan
            if ($request->hasFile('new_images')) {
                foreach ($request->file('new_images') as $file) {
                    $path = $file->store('assets/images', 'public');
                    AssetImage::create([
                        'asset_id' => $asset->id,
                        'image_path' => $path,
                        'is_primary' => false,
                    ]);
                }
            }

            // Catatan: Penghapusan foto lama biasanya ditangani via endpoint API terpisah (deleteImage)
            // agar lebih interaktif di frontend.

            DB::commit();
            return redirect()->route('assets.show', $asset->id)->with('success', 'Data aset berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal update: ' . $e->getMessage());
        }
    }

    public function destroy(Asset $asset)
    {
        $asset->delete(); // Soft Delete (sesuai model)
        return redirect()->route('assets.index')->with('success', 'Aset berhasil dipindahkan ke sampah.');
    }

    /**
     * GENERATOR LABEL QR CODE
     * Mengembalikan View Blade (Bukan Inertia) agar mudah diprint browser.
     */

    public function printBatch(Request $request)
    {
        // Validasi
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:assets,id',
            'layout' => 'nullable|string'
        ]);

        // Ambil data aset terpilih
        $assets = Asset::whereIn('id', $request->ids)
            ->with('location')
            ->get();

        // Generate QR Code untuk setiap aset
        foreach ($assets as $asset) {
            $url = route('public.assets.show', $asset->id);
            $asset->qr_code = QrCode::size(80)->margin(0)->generate($url); // Size disesuaikan untuk batch
        }

        // AMBIL NAMA ORGANISASI DARI USER LOGIN
        $user = auth()->user();
        // Load relasi jika belum ada (sesuaikan dengan model User Anda)
        // Asumsi: User hasBelongsTo OrganizationUnit
        $orgName = $user->organizationUnit->name ?? 'MUHAMMADIYAH'; 

        return view('print.asset-batch', [
            'assets' => $assets,
            'layout' => $request->layout ?? 'A4_STICKER',
            'orgName' => $orgName // <-- Kirim ke View
        ]);
    }

    public function printLabel(Asset $asset)
    {
        $url = route('public.assets.show', $asset->id);;
        $qrCode = QrCode::size(150)->margin(0)->generate($url);

        // AMBIL NAMA ORGANISASI
        $user = auth()->user();
        $orgName = $user->organizationUnit->name ?? 'Pimpinan Cabang Muhammadiyah Muara Aman';

        return view('print.asset-label', [
            'asset' => $asset,
            'qrCode' => $qrCode,
            'orgName' => $orgName // <-- Kirim ke View
        ]);
    }
    }