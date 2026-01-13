<?php

namespace App\Http\Controllers\App\Members;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\User;
use App\Models\OrganizationUnit;
use App\Http\Requests\MemberRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use App\Imports\MembersImport; 
use App\Exports\MemberTemplateExport;
use Maatwebsite\Excel\Facades\Excel; 
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class MemberController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Eager load 'assignments' agar kita bisa cek jabatan di unit lain
        $query = Member::with(['organizationUnit', 'assignments.organizationUnit']);

        // --- LOGIKA MULTI ROLE (DIPERBAIKI) ---
        // Jika bukan Super Admin, filter data yang boleh dilihat
        if ($user->role !== 'super_admin' && $user->organization_unit_id) {
            $myUnitId = $user->organization_unit_id;

            $query->where(function($q) use ($myUnitId) {
                // 1. Tampilkan Anggota yang "Home Base"-nya di Unit Saya (Warga Saya)
                $q->where('organization_unit_id', $myUnitId)
                
                // 2. ATAU Anggota dari unit lain TAPI punya Jabatan/Tugas di Unit Saya
                // (Contoh: Warga PRM A, tapi jadi Guru di Sekolah Saya)
                  ->orWhereHas('assignments', function($subQ) use ($myUnitId) {
                      $subQ->where('organization_unit_id', $myUnitId);
                  });
            });
        }

        // Ambil data (Client-side DataTable)
        $members = $query->orderBy('full_name', 'asc')->get();

        return Inertia::render('App/Members/Index', [
            'members' => $members,
        ]);
    }

    public function show(Member $member)
    {
        $user = auth()->user();
        
        // --- CEK HAK AKSES (DIPERBAIKI) ---
        $hasAccess = false;

        // 1. Super Admin boleh lihat semua
        if ($user->role === 'super_admin') {
            $hasAccess = true;
        } 
        // 2. Jika Home Base member sama dengan Unit Admin
        elseif ($member->organization_unit_id === $user->organization_unit_id) {
            $hasAccess = true;
        } 
        // 3. Jika Member punya jabatan/tugas di Unit Admin
        elseif ($user->organization_unit_id) {
            // Cek apakah member ini ada di tabel structure unit saya?
            $isAssigned = $member->assignments()
                                 ->where('organization_unit_id', $user->organization_unit_id)
                                 ->exists();
            if ($isAssigned) $hasAccess = true;
        }

        if (!$hasAccess) {
            abort(403, 'Anda tidak memiliki akses untuk melihat data anggota ini.');
        }

        // --- LOAD DATA ---
        // Muat relasi assignments (jabatan) dan unit-nya untuk ditampilkan di View
        $member->load([
            'organizationUnit', // Home Base
            'user',             // Akun Login
            'assignments.organizationUnit' // Riwayat Jabatan di Organisasi Lain
        ]);

        return Inertia::render('App/Members/Show', [
            'member' => $member
        ]);
    }

    public function create()
    {
        return Inertia::render('App/Members/Form', [
            // Dropdown hanya menampilkan Unit Struktural (PCM/PRM) sebagai "Home Base"
            'prms' => OrganizationUnit::whereIn('type', ['PCM', 'PRM'])
                ->select('id', 'name')
                ->orderBy('type', 'asc')
                ->orderBy('name', 'asc') 
                ->get(),
        ]);
    }

    public function store(MemberRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('uploads/members', 'public');
        }
        unset($data['photo']);
        
        // Create Member (Hanya set Home Base)
        // Penugasan (Assignments) ke unit lain dilakukan terpisah di menu Organisasi/Struktur
        $member = Member::create($data);

        // Opsional: Log Activity jika ada
        // $this->logActivity('CREATE_MEMBER', "Menambahkan anggota: {$member->full_name}");

        return redirect()->route('members.index')->with('success', 'Anggota berhasil ditambahkan.');
    }

    public function edit(Member $member)
    {
        $user = auth()->user();

        // Security: Hanya Admin Home Base atau Super Admin yang boleh EDIT PROFIL UTAMA
        // Admin Unit tempat dia bekerja (Assignments) sebaiknya TIDAK mengedit biodata inti
        if ($user->role !== 'super_admin' && $member->organization_unit_id !== $user->organization_unit_id) {
            abort(403, 'Hanya Admin tempat asal anggota yang boleh mengedit data utama.');
        }

        return Inertia::render('App/Members/Form', [
            'member' => $member,
            'prms' => OrganizationUnit::whereIn('type', ['PCM', 'PRM'])
                ->select('id', 'name')
                ->orderBy('type', 'asc')
                ->orderBy('name', 'asc') 
                ->get(),
        ]);
    }

    public function update(MemberRequest $request, Member $member)
    {
        // Security Check ulang (Best Practice)
        $user = auth()->user();
        if ($user->role !== 'super_admin' && $member->organization_unit_id !== $user->organization_unit_id) {
            abort(403);
        }

        $data = $request->validated();

        if ($request->hasFile('photo')) {
            if ($member->photo_path) {
                Storage::disk('public')->delete($member->photo_path);
            }
            $data['photo_path'] = $request->file('photo')->store('uploads/members', 'public');
        }
        unset($data['photo']);

        $member->update($data);
        
        // $this->logActivity('UPDATE_MEMBER', "Memperbaharui anggota: {$member->full_name}");

        return redirect()->route('members.index')->with('success', 'Data anggota diperbarui.');
    }

    public function destroy(Member $member)
    {
        // Security Check
        $user = auth()->user();
        if ($user->role !== 'super_admin' && $member->organization_unit_id !== $user->organization_unit_id) {
            abort(403);
        }

        if ($member->photo_path) { // Fix typo: photo_path
            Storage::disk('public')->delete($member->photo_path);
        }
        
        $member->delete();
        // $this->logActivity('DELETE_MEMBER', "Menghapus anggota: {$member->full_name}");

        return redirect()->route('members.index')->with('success', 'Anggota dihapus.');
    }

    public function generateAccount(Member $member)
    {
        if ($member->user_id) {
            return back()->with('error', 'Anggota ini sudah memiliki akun login.');
        }

        $uniqueId = $member->nbm ?? 'id_' . $member->id;
        $email = $uniqueId . '@pcmmm.com';

        $user = User::create([
            'name' => $member->full_name,
            'email' => $email,
            'password' => Hash::make('password'),
            'role' => 'member',
            'organization_unit_id' => $member->organization_unit_id,
            'is_active' => true,
        ]);

        $member->update(['user_id' => $user->id]);

        return back()->with('success', "Akun berhasil dibuat. Email: $email | Pass: password");
    }

    // Method baru untuk handle update JSON (Ortom & Training)
    public function updateHistory(Request $request, Member $member)
    {
        $user = auth()->user();
        // Security Check
        if ($user->role !== 'super_admin' && $member->organization_unit_id !== $user->organization_unit_id) {
            abort(403);
        }

        $type = $request->input('type'); // 'ortom' atau 'training'
        $action = $request->input('action'); // 'add' atau 'remove'
        $value = $request->input('value'); // Nilai baru (String)

        if (!$type || !$action || !$value) {
            return back()->with('error', 'Data tidak lengkap.');
        }

        // Logic Manipulasi Array
        $currentData = [];
        if ($type === 'ortom') {
            $currentData = $member->active_ortoms ?? [];
        } else {
            $currentData = $member->training_history ?? [];
        }

        if ($action === 'add') {
            // Cek duplikasi
            if (!in_array($value, $currentData)) {
                $currentData[] = $value;
            }
        } elseif ($action === 'remove') {
            // Hapus value dari array
            $currentData = array_values(array_diff($currentData, [$value]));
        }

        // Simpan kembali
        if ($type === 'ortom') {
            $member->active_ortoms = $currentData;
        } else {
            $member->training_history = $currentData;
            $member->has_training = count($currentData) > 0; // Auto update flag boolean
        }
        
        $member->save();

        return back()->with('success', 'Data riwayat berhasil diperbarui.');
    }

    // --- ADVANCED IMPORT WIZARD ---

    public function downloadTemplate()
    {
        // Format Nama File: template_import_anggota_TAHUN-BULAN-TANGGAL_JAM-MENIT_KODEUNIK.xlsx
        // Contoh: template_import_anggota_2023-10-25_14-30_AbC12.xlsx
        
        $timestamp = date('Y-m-d_H-i');
        $uniqueCode = Str::random(6);
        
        $fileName = "template_import_anggota_{$timestamp}_{$uniqueCode}.xlsx";

        return Excel::download(new MemberTemplateExport, $fileName);
    }

    public function importWizard()
    {
        return Inertia::render('App/Members/ImportWizard', [
            // Kirim data Unit Organisasi untuk Dropdown "Binding Basis"
            'units' => OrganizationUnit::select('id', 'name', 'type')
                ->whereIn('type', ['PCM', 'PRM']) // Basis biasanya PRM/PCM
                ->orderBy('type')
                ->get()
        ]);
    }

    // STEP 2: BACA & VALIDASI
    public function parseImport(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls']);

        $data = Excel::toArray([], $request->file('file'))[0]; 
        
        // Hapus Header
        array_shift($data); 

        $previewData = [];
        $validCount = 0;
        $invalidCount = 0;

        $existingNbms = Member::whereNotNull('nbm')->pluck('nbm')->toArray();

        foreach ($data as $index => $row) {
            // Filter Baris Kosong (Jika Nama & NBM kosong)
            if (empty($row[2]) && empty($row[1])) continue; 

            // Mapping Excel
            // 0:No, 1:NBM, 2:Nama, 3:NIK, 4:TglLahir, 5:JK, 6:HP, 7:Alamat, 8:Pendidikan, 9:Pekerjaan
            $nbm = $row[1] ?? null;
            $name = $row[2] ?? null;
            
            $isValid = true;
            $errors = [];

            if (empty($name)) {
                $isValid = false;
                $errors[] = 'Nama wajib diisi';
            }
            if (!empty($nbm) && in_array((string)$nbm, $existingNbms)) {
                $isValid = false;
                $errors[] = 'NBM sudah terdaftar';
            }

            $status = $isValid ? 'VALID' : 'INVALID';
            if($isValid) $validCount++; else $invalidCount++;

            $previewData[] = [
                'row_index' => $index + 2,
                'nbm' => $nbm,
                'full_name' => $name,
                'nik' => $row[3] ?? null,
                'birth_date' => $row[4] ?? null,
                
                // DATA BARU: TEMPAT LAHIR (Index 5)
                'birth_place' => $row[5] ?? '-', 
                
                // DATA GESER
                'gender' => $row[6] ?? 'L',       // Index geser ke 6
                'phone_number' => $row[7] ?? null,// Index geser ke 7
                'address' => $row[8] ?? '-',      // Index geser ke 8
                'last_education' => $row[9] ?? 'SMA', // Index geser ke 9
                'job' => $row[10] ?? null,        // Index geser ke 10
                
                'status' => $status,
                'errors' => implode(', ', $errors)
            ];
        }

        return response()->json([
            'preview' => $previewData,
            'stats' => ['valid' => $validCount, 'invalid' => $invalidCount]
        ]);
    }

    // STEP 3: EKSEKUSI SIMPAN
    public function executeImport(Request $request)
    {
        $rows = $request->input('rows');
        $targetUnitId = $request->input('organization_unit_id');

        if (empty($rows) || !$targetUnitId) {
            return response()->json(['message' => 'Data tidak valid'], 422);
        }

        DB::beginTransaction();
        try {
            foreach ($rows as $row) {
                // Parsing Tanggal
                $birthDate = '1900-01-01'; 
                if (!empty($row['birth_date'])) {
                    if (is_numeric($row['birth_date'])) {
                        $birthDate = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['birth_date'])->format('Y-m-d');
                    } else {
                        $birthDate = date('Y-m-d', strtotime($row['birth_date']));
                    }
                }

                Member::create([
                    'organization_unit_id' => $targetUnitId,
                    'nbm' => $row['nbm'],
                    'full_name' => $row['full_name'],
                    'nik' => $row['nik'],
                    'birth_date' => $birthDate,
                    'birth_place' => $row['birth_place'] ?? '-', // Menggunakan data dari parseImport
                    'gender' => strtoupper($row['gender']) === 'P' || strtoupper($row['gender']) === 'PEREMPUAN' ? 'P' : 'L',
                    'phone_number' => $row['phone_number'],
                    'address' => $row['address'],
                    'last_education' => $row['last_education'] ?? 'SMA',
                    'job' => $row['job'] ?? null,
                    'status' => 'ACTIVE'
                ]);
            }
            
            DB::commit();
            return response()->json(['message' => 'Chunk processed successfully']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal simpan: ' . $e->getMessage()], 500);
        }
    }
}