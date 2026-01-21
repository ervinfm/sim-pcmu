<?php

namespace App\Http\Controllers\App\Assets;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetLoan;
use App\Models\Member; 
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AssetLoanController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        
        // Eager Load Member agar nama muncul di Index
        $query = AssetLoan::with(['asset.images', 'member', 'approver']); 

        if ($user->role !== 'super_admin' && $user->organization_unit_id) {
            $query->whereHas('asset', function ($q) use ($user) {
                $q->where('organization_unit_id', $user->organization_unit_id);
            });
        }

        $loans = $query->latest()->get();

        return Inertia::render('App/Assets/Loans/Index', [
            'loans' => $loans,
        ]);
    }

    public function create()
    {
        $user = auth()->user();

        // 1. Aset Tersedia
        $assets = Asset::with('images')
            ->select('id', 'name', 'inventory_code', 'condition', 'status', 'organization_unit_id')
            ->when($user->role !== 'super_admin', fn($q) => $q->where('organization_unit_id', $user->organization_unit_id))
            ->whereIn('condition', ['GOOD', 'SLIGHTLY_DAMAGED']) 
            ->where('status', 'ACTIVE')
            ->whereDoesntHave('activeLoan') 
            ->get()
            ->map(function ($asset) {
                return [
                    'id' => (int) $asset->id,
                    'name' => $asset->name ?? 'Tanpa Nama',
                    'code' => $asset->inventory_code,
                    'condition' => $asset->condition,
                    'image_url' => $asset->images->first()->image_path ?? null
                ];
            });

        // 2. Data Member (Mapping full_name ke nama untuk Frontend)
        $membersRaw = Member::query()
            ->when($user->role !== 'super_admin', fn($q) => $q->where('organization_unit_id', $user->organization_unit_id))
            ->orderBy('full_name', 'asc')
            ->get();

        $members = $membersRaw->map(function ($m) {
            return [
                'id' => (int) $m->id,
                'nama' => $m->full_name ?? 'Tanpa Nama', // Mapping penting!
                'nbm' => $m->nbm ?? '-',
                'no_hp' => $m->phone_number ?? '-'
            ];
        });

        return Inertia::render('App/Assets/Loans/Form', [
            'available_assets' => $assets,
            'members' => $members, 
        ]);
    }

    public function store(Request $request)
    {
        $val = $request->validate([
            'asset_id' => 'required|exists:assets,id',
            'borrower_type' => 'required|in:INTERNAL,EXTERNAL',
            'member_id' => 'required_if:borrower_type,INTERNAL|nullable|exists:members,id',
            'borrower_name' => 'required_if:borrower_type,EXTERNAL|nullable|string|max:100',
            'borrower_contact' => 'required_if:borrower_type,EXTERNAL|nullable|string|max:20',
            'loan_date' => 'required|date',
            'return_date_plan' => 'required|date|after_or_equal:loan_date',
            'description' => 'nullable|string',
        ]);

        $isBooked = AssetLoan::where('asset_id', $val['asset_id'])
            ->whereIn('status', ['PENDING', 'APPROVED', 'BORROWED'])
            ->exists();

        if ($isBooked) return back()->with('error', 'Gagal: Aset ini sedang dipinjam.');

        $asset = Asset::find($val['asset_id']);

        DB::beginTransaction();
        try {
            AssetLoan::create([
                'asset_id' => $val['asset_id'],
                'member_id' => $val['borrower_type'] === 'INTERNAL' ? $val['member_id'] : null,
                'borrower_name' => $val['borrower_type'] === 'EXTERNAL' ? $val['borrower_name'] : null,
                'borrower_contact' => $val['borrower_type'] === 'EXTERNAL' ? $val['borrower_contact'] : null,
                'loan_date' => $val['loan_date'],
                'return_date_plan' => $val['return_date_plan'],
                'condition_before' => $asset->condition,
                'status' => 'APPROVED', // Auto-approve jika admin yang input
                'description' => $val['description'],
                'approved_by' => auth()->id(),
            ]);

            DB::commit();
            return redirect()->route('assets.loans.index')->with('success', 'Transaksi berhasil dibuat.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    // FITUR BARU: Update Status Realtime
    public function changeStatus(Request $request, AssetLoan $assetLoan)
    {
        $request->validate(['status' => 'required|in:APPROVED,REJECTED,BORROWED,COMPLETED']);
        
        $status = $request->status;

        // Validasi Alur
        if ($status == 'APPROVED' && $assetLoan->status != 'PENDING') {
            return back()->with('error', 'Hanya status Pending yang bisa disetujui.');
        }
        if ($status == 'BORROWED' && $assetLoan->status != 'APPROVED') {
            return back()->with('error', 'Barang harus disetujui dulu sebelum diambil.');
        }

        $data = ['status' => $status];
        if ($status == 'APPROVED') $data['approved_by'] = auth()->id();
        
        $assetLoan->update($data);

        return back()->with('success', 'Status diperbarui menjadi ' . $status);
    }

    public function checkin(Request $request, AssetLoan $assetLoan)
    {
        // 1. Validasi Input Audit
        $val = $request->validate([
            'return_date_actual' => 'required|date',
            'condition_after' => 'required|string', // GOOD, SLIGHTLY_DAMAGED, HEAVILY_DAMAGED, LOST
            'notes' => 'nullable|string'
        ]);

        DB::beginTransaction();
        try {
            // 2. Update Data Peminjaman -> COMPLETED
            $assetLoan->update([
                'status' => 'COMPLETED',
                'return_date_actual' => $val['return_date_actual'],
                'condition_after' => $val['condition_after'],
                'description' => $assetLoan->description . ($val['notes'] ? "\n[Catatan Kembali]: " . $val['notes'] : '')
            ]);

            // 3. Update Status & Kondisi Aset Induk (Otomatis)
            // Jika rusak/hilang, status aset jadi MAINTENANCE agar tidak bisa dipinjam orang lain
            $newAssetStatus = 'ACTIVE';
            if (in_array($val['condition_after'], ['HEAVILY_DAMAGED', 'LOST'])) {
                $newAssetStatus = 'MAINTENANCE'; 
            }

            $assetLoan->asset()->update([
                'condition' => $val['condition_after'],
                'status' => $newAssetStatus
            ]);

            DB::commit();
            return back()->with('success', 'Barang berhasil dikembalikan. Status aset diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memproses pengembalian: ' . $e->getMessage());
        }
    }

    public function show(AssetLoan $assetLoan)
    {
        $assetLoan->load(['asset.images', 'member', 'approver']);
        return Inertia::render('App/Assets/Loans/Show', ['loan' => $assetLoan]);
    }

    public function destroy(AssetLoan $assetLoan)
    {
        if (in_array($assetLoan->status, ['BORROWED', 'COMPLETED'])) {
            return back()->with('error', 'Transaksi aktif tidak bisa dihapus.');
        }
        $assetLoan->delete();
        return redirect()->route('assets.loans.index')->with('success', 'Pengajuan dihapus.');
    }
}