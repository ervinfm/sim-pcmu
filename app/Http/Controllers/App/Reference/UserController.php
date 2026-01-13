<?php

namespace App\Http\Controllers\App\Reference;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\OrganizationUnit;
use App\Http\Requests\UserRequest;
use Illuminate\Http\Request;
use App\Models\Member;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index()
    {
        if (auth()->user()->role !== 'super_admin') {
            abort(403);
        }

        $users = User::with(['organizationUnit', 'member'])->latest()->get();

        // VALIDASI KETAT: Hanya ambil member yang BENAR-BENAR belum punya akun
        $availableMembers = Member::whereNull('user_id')
            ->select('id', 'full_name', 'nbm', 'organization_unit_id')
            ->with('organizationUnit:id,name')
            ->orderBy('full_name')
            ->get()
            ->map(function($m) {
                return [
                    'id' => $m->id,
                    'label' => "{$m->full_name} - NBM: " . ($m->nbm ?? 'Non-NBM'),
                    'sub_label' => $m->organizationUnit->name
                ];
            });

        return Inertia::render('App/Reference/Users/Index', [
            'users' => $users,
            'availableMembers' => $availableMembers
        ]);
    }

    // Helper untuk Grouping Unit
    private function getGroupedUnits()
    {
        return [
            [
                'label' => 'Pimpinan (Struktural)',
                'code' => 'STRUKTURAL',
                'items' => OrganizationUnit::where('category', 'STRUKTURAL')->select('id', 'name', 'type')->orderBy('name')->get()
            ],
            [
                'label' => 'Amal Usaha (AUM)',
                'code' => 'AUM',
                'items' => OrganizationUnit::where('category', 'AUM')->select('id', 'name', 'type')->orderBy('name')->get()
            ],
            [
                'label' => 'Organisasi Otonom (Ortom)',
                'code' => 'ORTOM',
                'items' => OrganizationUnit::where('category', 'ORTOM')->select('id', 'name', 'type')->orderBy('name')->get()
            ]
        ];
    }

    public function create()
    {
        return Inertia::render('App/Reference/Users/Form', [
            'groupedUnits' => $this->getGroupedUnits(),
        ]);
    }

    public function store(UserRequest $request)
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);

        // Jika Role Super Admin, pastikan organization_unit_id NULL
        if ($data['role'] === 'super_admin') {
            $data['organization_unit_id'] = null;
        }

        User::create($data);

        return redirect()->route('users.index')->with('success', 'User baru berhasil dibuat.');
    }

    public function edit(User $user)
    {
        return Inertia::render('App/Reference/Users/Form', [
            'user' => $user,
            'groupedUnits' => $this->getGroupedUnits(),
        ]);
    }

    public function update(UserRequest $request, User $user)
    {
        // Validasi sudah di UserRequest, tapi pastikan rules-nya:
        // 'password' => 'nullable|min:6|confirmed'
        
        $data = $request->validated();

        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }

        // Logic Super Admin
        if ($data['role'] === 'super_admin') {
            $data['organization_unit_id'] = null;
        }

        $user->update($data);

        return redirect()->route('users.index')->with('success', 'Data user diperbarui.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) return back()->with('error', 'Tidak bisa menghapus akun sendiri.');
        $user->delete();
        return redirect()->route('users.index')->with('success', 'User dihapus.');
    }

    public function linkMember(Request $request, User $user)
    {
        $request->validate(['member_id' => 'required|exists:members,id']);

        // Cek 1: Apakah User ini sudah punya member?
        if ($user->member) {
            return back()->with('error', 'User ini sudah terhubung dengan anggota lain. Lepas dulu tautannya.');
        }

        // Cek 2: Apakah Member yang dipilih sudah punya akun lain? (PENTING)
        $targetMember = Member::find($request->member_id);
        if ($targetMember->user_id) {
            return back()->with('error', 'Anggota tersebut sudah memiliki akun login lain!');
        }
        
        $targetMember->update(['user_id' => $user->id]);
        return back()->with('success', 'Akun berhasil dihubungkan.');
    }

    public function unlinkMember(User $user)
    {
        Member::where('user_id', $user->id)->update(['user_id' => null]);
        return back()->with('success', 'Tautan dilepas.');
    }

    public function toggleStatus(User $user)
    {
        // Cegah disable diri sendiri
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak bisa menonaktifkan akun sendiri.');
        }

        $user->update(['is_active' => !$user->is_active]);
        
        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "User berhasil $status.");
    }
}