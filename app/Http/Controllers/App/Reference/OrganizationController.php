<?php

namespace App\Http\Controllers\App\Reference;

use App\Http\Controllers\Controller;
use App\Models\OrganizationUnit;
use App\Models\OrganizationTerritory;
use App\Models\OrganizationStructure;
use App\Models\Member;
use App\Http\Requests\OrganizationRequest;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Illuminate\Http\Request;

class OrganizationController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        
        $category = $request->input('tab', 'STRUKTURAL');
        $search = $request->input('search');
        $sort = $request->input('sort', 'name');

        // Base Query
        $query = OrganizationUnit::query()
            ->with(['parent', 'territories'])
            ->withCount(['members', 'territories']); // Hitung Direct

        // EAGER LOAD CHILDREN COUNTS (PENTING: Untuk menghitung total gabungan)
        // Kita load children beserta jumlah member mereka agar bisa dijumlahkan di PHP
        $query->with(['children' => function($q) {
            $q->withCount(['members', 'territories']);
        }]);

        // Filter Category
        $query->where('category', $category);

        // Filter Search
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('sk_number', 'ilike', "%{$search}%")
                  ->orWhere('type', 'ilike', "%{$search}%")
                  ->orWhereHas('territories', function($subQ) use ($search) {
                      $subQ->where('name', 'ilike', "%{$search}%");
                  });
            });
        }

        // Sorting
        if ($sort === 'newest') {
            $query->latest();
        } else {
            $query->orderBy('name', 'asc');
        }

        $organizations = $query->paginate(9)->withQueryString();

        // TRANSFORM DATA (Menghitung Total Network)
        $organizations->getCollection()->transform(function ($org) {
            // Hitung Total = Milik Sendiri + Milik Anak-anaknya
            $org->network_members_count = $org->members_count + $org->children->sum('members_count');
            $org->network_territories_count = $org->territories_count + $org->children->sum('territories_count');
            return $org;
        });

        return Inertia::render('App/Reference/Organizations/Index', [
            'organizations' => $organizations,
            'filters' => [
                'tab' => $category,
                'search' => $search,
                'sort' => $sort
            ]
        ]);
    }

    public function show($id)
    {
        $organization = OrganizationUnit::with([
            'parent', 
            'territories', 
            // Load struktur aktif
            'structures' => function($q) {
                $q->where('is_active', true)->with('member');
            },
            // Load children beserta jumlah anggotanya (Khusus Struktural)
            'children' => function($q) {
                $q->withCount('members');
            }
        ])->withCount(['members', 'territories'])->findOrFail($id);

        // --- LOGIC STATISTIK ---
        
        // 1. Hitung Total Jejaring (Khusus Struktural)
        // Gabungan anggota sendiri + anggota unit di bawahnya
        $networkMembersCount = $organization->members_count;
        if ($organization->category === 'STRUKTURAL') {
            $networkMembersCount += $organization->children->sum('members_count');
        }

        // 2. Query Daftar Anggota / SDM / Kader
        // - Jika Struktural: Tampilkan semua anggota di jejaringnya (supaya Admin PCM bisa lihat anggota PRM)
        // - Jika AUM/Ortom: Hanya tampilkan anggota unit itu sendiri
        $memberQuery = Member::query()->with('organizationUnit');

        if ($organization->category === 'STRUKTURAL') {
            $unitIds = collect([$organization->id])->merge($organization->children->pluck('id'));
            $memberQuery->whereIn('organization_unit_id', $unitIds);
        } else {
            $memberQuery->where('organization_unit_id', $organization->id);
        }

        $membersList = $memberQuery->latest()->paginate(10);

        return Inertia::render('App/Reference/Organizations/Show', [
            'organization' => $organization,
            'stats' => [
                'direct_count' => $organization->members_count,   // Anggota langsung
                'network_count' => $networkMembersCount,          // Total Basis (Jejaring)
                'territory_count' => $organization->territories_count,
                'children_count' => $organization->children->count(),
            ],
            'members' => $membersList,
        ]);
    }

    public function create()
    {
        return Inertia::render('App/Reference/Organizations/Form', [
            // Kirim daftar calon induk (Hanya PCM dan PRM yang bisa jadi induk)
            'parents' => OrganizationUnit::whereIn('type', ['PCM', 'PRM'])
                ->orderBy('type')
                ->select('id', 'name', 'type')
                ->get(),
        ]);
    }

    public function store(Request $request)
    {
        // Validasi
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string',
            'category' => 'required|string|in:STRUKTURAL,AUM,ORTOM', // Kategori wajib, tapi diisi otomatis di Frontend
            'parent_id' => 'nullable|exists:organization_units,id',
            'sk_number' => 'nullable|string',
            'establishment_date' => 'nullable|date',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'facebook' => 'nullable|string',
            'instagram' => 'nullable|string',
            'website' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'description' => 'nullable|string',
            'logo' => 'nullable|image|max:2048', // Validasi file gambar
        ]);

        // Upload Logo
        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('uploads/logos', 'public');
        }

        // Hapus 'logo' dari array data karena kolom di db namanya 'logo_path'
        unset($data['logo']);

        OrganizationUnit::create($data);

        return redirect()->route('organizations.index')->with('success', 'Unit organisasi berhasil dibuat.');
    }

    public function edit(OrganizationUnit $organization)
    {
        return Inertia::render('App/Reference/Organizations/Form', [
            'organization' => $organization,
            'parents' => OrganizationUnit::whereIn('type', ['PCM', 'PRM'])
                        ->where('id', '!=', $organization->id) // Jangan pilih diri sendiri sebagai induk
                        ->orderBy('type')
                        ->select('id', 'name', 'type')
                        ->get(),
        ]);
    }

    public function update(Request $request, OrganizationUnit $organization)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string',
            'category' => 'required|string',
            'parent_id' => 'nullable|exists:organization_units,id',
            'sk_number' => 'nullable|string',
            'establishment_date' => 'nullable|date',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'facebook' => 'nullable|string',
            'instagram' => 'nullable|string',
            'website' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'description' => 'nullable|string',
            'logo' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            // Hapus logo lama jika ada
            if ($organization->logo_path) {
                Storage::disk('public')->delete($organization->logo_path);
            }
            $data['logo_path'] = $request->file('logo')->store('uploads/logos', 'public');
        }
        
        unset($data['logo']);

        $organization->update($data);

        return redirect()->route('organizations.index')->with('success', 'Profil organisasi diperbarui.');
    }

    public function destroy(OrganizationUnit $organization)
    {
        if ($organization->logo_path) {
            Storage::disk('public')->delete($organization->logo_path);
        }
        $organization->delete();
        return redirect()->route('organizations.index')->with('success', 'Unit dihapus.');
    }

    // ==========================================
    // BAGIAN BARU: MANAJEMEN STRUCTURE
    // ==========================================

    public function editStructure(OrganizationUnit $organization)
    {
        // Load struktur yang sudah ada + data membernya
        $organization->load(['structures.member', 'parent']);

        // Ambil list member untuk dropdown (Simple select)
        // Di aplikasi real, sebaiknya pakai API Search / Async Select jika data ribuan
        $members = Member::select('id', 'full_name', 'nbm')
            ->orderBy('full_name')
            ->get()
            ->map(function($m) {
                return [
                    'value' => $m->id,
                    'label' => $m->full_name . ' - ' . ($m->nbm ?? 'Non-NBM')
                ];
            });

        return Inertia::render('App/Reference/Organizations/ManageStructure', [
            'organization' => $organization,
            'members' => $members
        ]);
    }

    public function storeStructure(Request $request, OrganizationUnit $organization)
    {
        $request->validate([
            'member_id' => 'required|exists:members,id',
            'position_name' => 'required|string',
            'position_type' => 'required|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ]);

        // Cek Double Jabatan di Unit yang SAMA
        $exists = OrganizationStructure::where('organization_unit_id', $organization->id)
            ->where('member_id', $request->member_id)
            ->where('position_name', $request->position_name)
            ->where('is_active', true)
            ->exists();

        if ($exists) {
            return back()->with('error', 'Anggota ini sudah menjabat posisi tersebut.');
        }

        OrganizationStructure::create([
            'organization_unit_id' => $organization->id,
            'member_id' => $request->member_id,
            'position_name' => $request->position_name,
            'position_type' => $request->position_type,
            'sk_number' => $request->sk_number,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'is_active' => true
        ]);

        return back()->with('success', 'Pengurus berhasil ditambahkan.');
    }

    public function destroyStructure($id)
    {
        OrganizationStructure::findOrFail($id)->delete();
        return back()->with('success', 'Pengurus dihapus.');
    }

    // ==========================================
    // BAGIAN BARU: MANAJEMEN WILAYAH
    // ==========================================

    public function editTerritory(OrganizationUnit $organization)
    {
        // Pastikan hanya kategori STRUKTURAL yang bisa akses
        if ($organization->category !== 'STRUKTURAL') {
            return redirect()->route('organizations.index')->with('error', 'Unit ini tidak memiliki wilayah binaan.');
        }

        $organization->load('territories');

        return Inertia::render('App/Reference/Organizations/ManageTerritory', [
            'organization' => $organization
        ]);
    }

    public function storeTerritory(Request $request, OrganizationUnit $organization)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'postal_code' => 'nullable|string|max:10'
        ]);

        OrganizationTerritory::create([
            'organization_unit_id' => $organization->id,
            'name' => $request->name,
            'postal_code' => $request->postal_code
        ]);

        return back()->with('success', 'Wilayah berhasil ditambahkan.');
    }

    public function destroyTerritory($id)
    {
        OrganizationTerritory::findOrFail($id)->delete();
        return back()->with('success', 'Wilayah dihapus.');
    }
}