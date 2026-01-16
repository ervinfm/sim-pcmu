<?php

namespace App\Http\Controllers\App\Finance;

use App\Http\Controllers\Controller;
use App\Models\FinanceCoa;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth; // Tambahan
use Illuminate\Support\Facades\Log;  // Tambahan untuk Debugging

class AccountController extends Controller
{
    /**
     * Menampilkan Halaman Index Master Akun
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $unitId = $user->organization_unit_id;

        // 1. Query Utama (Client-side Search enabled via Get All)
        $query = FinanceCoa::query()
            ->with('parent') // Eager load nama induk
            ->where(function($q) use ($unitId) {
                // Tampilkan Akun Global (Pusat)
                $q->whereNull('organization_unit_id'); 
                
                // Jika User adalah Admin Unit, tampilkan juga akun lokal mereka
                if ($unitId) {
                    $q->orWhere('organization_unit_id', $unitId); 
                }
            })
            ->orderBy('code'); // Urutkan berdasarkan Kode agar hierarki terlihat rapi

        // Ambil semua data (tanpa pagination agar search client-side lancar)
        $accounts = $query->get();

        // 2. Query untuk Dropdown Parent
        // Hanya ambil akun yang aktif untuk dijadikan Induk
        $parentOptions = FinanceCoa::query()
            ->select('id', 'code', 'name')
            ->where('is_active', true)
            ->where(function($q) use ($unitId) {
                $q->whereNull('organization_unit_id');
                if ($unitId) {
                    $q->orWhere('organization_unit_id', $unitId);
                }
            })
            ->orderBy('code')
            ->get();

        return Inertia::render('App/Finance/Accounts/Index', [
            'accounts' => $accounts,
            'parentOptions' => $parentOptions,
        ]);
    }

    /**
     * API: Generate Kode Otomatis (Smart Dot Notation)
     * Format: X.XX.XX.XXX (Standard ISAK 35)
     */
    public function generateCode(Request $request)
    {
        // 1. Cek Login (Mencegah Error 500 karena user null)
        if (!Auth::check()) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        try {
            $parentId = $request->parent_id;
            $user = Auth::user();
            $targetUnitId = ($user->role === 'super_admin') ? null : $user->organization_unit_id;

            // --- SKENARIO 1: AKUN INDUK (LEVEL 1 / ROOT) ---
            if (!$parentId) {
                // Ambil semua root
                $rootAccounts = FinanceCoa::whereNull('parent_id')
                    ->where('organization_unit_id', $targetUnitId)
                    ->get();
                
                // Sort manual di PHP (Aman untuk semua DB)
                $sorted = $rootAccounts->sortByDesc(function ($account) {
                    // Ambil angka depan saja. Contoh "1.10" -> 1, "1" -> 1
                    $parts = explode('.', $account->code);
                    return (int) $parts[0];
                });

                $lastRoot = $sorted->first();
                
                // Logic Increment Root: "1" -> "2", "1.10" -> "2" (Ambil digit pertama lalu +1)
                $nextNum = 1;
                if ($lastRoot) {
                    $firstSegment = explode('.', $lastRoot->code)[0];
                    $nextNum = (int)$firstSegment + 1;
                }
                
                return response()->json(['status' => 'success', 'code' => (string)$nextNum]);
            }

            // --- SKENARIO 2: SUB-AKUN (ANAK) ---
            $parent = FinanceCoa::find($parentId);
            if (!$parent) return response()->json(['code' => '']);

            // Ambil anak-anaknya
            $childAccounts = FinanceCoa::where('parent_id', $parentId)
                ->where('organization_unit_id', $targetUnitId)
                ->get();

            // Sort Natural (Versi PHP)
            $sortedChildren = $childAccounts->sort(function ($a, $b) {
                return strnatcmp($a->code, $b->code);
            })->reverse(); 

            $lastChild = $sortedChildren->first();

            if ($lastChild) {
                // Logic: "1.1" punya anak terakhir "1.1.5" -> Generasi "1.1.6"
                $segments = explode('.', $lastChild->code);
                $lastNumStr = end($segments); 
                
                // Pastikan yang diambil adalah angka
                if (!is_numeric($lastNumStr)) {
                    // Fallback jika format kode aneh
                    $newCode = $parent->code . '.' . ($childAccounts->count() + 1);
                } else {
                    $newNum = (int)$lastNumStr + 1;
                    $padLen = strlen($lastNumStr); 
                    // Smart Padding: Jika "005" -> "006", Jika "5" -> "6"
                    $newSegment = ($padLen > 1) ? str_pad($newNum, $padLen, '0', STR_PAD_LEFT) : $newNum;
                    
                    array_pop($segments);
                    $newCode = implode('.', $segments) . '.' . $newSegment;
                }
            } else {
                // Anak Pertama
                $parentCode = $parent->code;
                $dots = substr_count($parentCode, '.');

                // Tentukan format berdasarkan kedalaman
                if ($dots == 0) $suffix = '.10';       // Level 1 -> Level 2 (Kelompok)
                elseif ($dots == 1) $suffix = '.01';   // Level 2 -> Level 3 (Jenis)
                else $suffix = '.001';                 // Level 3 -> Level 4 (Rincian)
                
                $newCode = $parentCode . $suffix;
            }

            return response()->json([
                'status' => 'success',
                'code' => $newCode
            ]);

        } catch (\Exception $e) {
            // Log error ke storage/logs/laravel.log agar bisa dicek
            Log::error('Generate Code Error: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Server Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Simpan Akun Baru
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        
        // LOGIKA ADMIN PCM (PUSAT):
        // Jika Super Admin, set unit_id = NULL (Global Account).
        // Akun ini akan otomatis "terlihat" dan "diwariskan" ke seluruh PRM/AUM.
        $targetUnitId = ($user->role === 'super_admin') ? null : $user->organization_unit_id;

        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:ASSET,LIABILITY,EQUITY,REVENUE,EXPENSE',
            'parent_id' => 'nullable|exists:finance_coas,id',
            'is_cash' => 'boolean',
            // Validasi Unik: Kode tidak boleh kembar DALAM SATU UNIT
            'code' => [
                'required', 
                'string', 
                Rule::unique('finance_coas')->where(function ($query) use ($targetUnitId) {
                    return $query->where('organization_unit_id', $targetUnitId);
                })
            ],
        ]);

        FinanceCoa::create([
            'organization_unit_id' => $targetUnitId, 
            'name' => $request->name,
            'code' => $request->code,
            'type' => $request->type,
            'parent_id' => $request->parent_id,
            'is_cash' => $request->is_cash ?? false,
            'is_active' => true
        ]);

        return back()->with('success', 'Akun berhasil ditambahkan.');
    }

    /**
     * Update Akun
     */
    public function update(Request $request, FinanceCoa $account)
    {
        // Proteksi: Unit tidak boleh edit Akun Pusat
        if ($account->organization_unit_id === null && auth()->user()->role !== 'super_admin') {
            return back()->with('error', 'Akses Ditolak: Anda tidak boleh mengedit Akun Standar Pusat.');
        }

        // Logic validasi unik untuk update
        $targetUnitId = $account->organization_unit_id;

        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:finance_coas,id',
            'is_cash' => 'boolean',
            'code' => [
                'required', 
                'string',
                Rule::unique('finance_coas')->where(function ($query) use ($targetUnitId) {
                    return $query->where('organization_unit_id', $targetUnitId);
                })->ignore($account->id)
            ],
        ]);

        // Proteksi: Akun tidak bisa jadi bapaknya sendiri
        if ($request->parent_id == $account->id) {
            return back()->with('error', 'Validasi Gagal: Akun tidak bisa menjadi induk dirinya sendiri.');
        }

        $account->update([
            'name' => $request->name,
            'code' => $request->code,
            'parent_id' => $request->parent_id,
            'is_cash' => $request->is_cash
        ]);

        return back()->with('success', 'Akun berhasil diperbarui.');
    }

    /**
     * Hapus Akun
     */
    public function destroy(FinanceCoa $account)
    {
        // Proteksi Akun Pusat
        if ($account->organization_unit_id === null && auth()->user()->role !== 'super_admin') {
            return back()->with('error', 'Akses Ditolak: Akun Pusat tidak bisa dihapus oleh Unit.');
        }

        try {
            // Cek apakah punya anak (sub-akun)
            if ($account->children()->exists()) {
                return back()->with('error', 'Gagal: Akun ini memiliki sub-akun. Hapus sub-akun terlebih dahulu.');
            }

            // Cek apakah dipakai di jurnal (Preventif, meski ada FK constraint)
            if ($account->journalDetails()->exists()) {
                return back()->with('error', 'Gagal: Akun ini sudah digunakan dalam transaksi jurnal.');
            }

            $account->delete();
            return back()->with('success', 'Akun berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus akun. Terjadi kesalahan teknis.');
        }
    }
}