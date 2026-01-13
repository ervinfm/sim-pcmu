<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use App\Models\User;
use App\Models\Member;
use App\Models\Message;
use App\Models\ActivityLog;

class ProfileController extends Controller
{
    // 1. HALAMAN: PROFIL SAYA (Data Member/Anggota)
    public function myProfile()
    {
        $user = auth()->user();
        
        $member = Member::with('organizationUnit')
                        ->where('user_id', $user->id)
                        ->first();

        // LOGIC: Hitung Kelengkapan Profil
        $completion = 0;
        $totalFields = 8; // Foto, NBM, NIK, Phone, Address, Job, Education, Org Unit
        
        if ($member) {
            if ($member->photo_path) $completion++;
            if ($member->nbm) $completion++;
            if ($member->nik) $completion++;
            if ($member->phone_number) $completion++;
            if ($member->address) $completion++;
            if ($member->job) $completion++;
            if ($member->last_education) $completion++;
            if ($member->organization_unit_id) $completion++;
        }
        
        $percentage = $member ? round(($completion / $totalFields) * 100) : 0;

        return Inertia::render('App/Profile/MyProfile', [
            'member' => $member,
            'user' => $user,
            'completion' => $percentage // Kirim data ini ke View
        ]);
    }

    // 2. HALAMAN: PENGATURAN AKUN (Email, Password)
    public function accountSettings()
    {
        return Inertia::render('App/Profile/AccountSettings', [
            'user' => auth()->user()
        ]);
    }

    // 3. HALAMAN: PESAN (CHAT INTERFACE)
    public function messages(Request $request)
    {
        $currentUser = auth()->user();
        
        // 1. AMBIL SEMUA USER (Kecuali saya sendiri) SEBAGAI KONTAK
        $contacts = User::where('id', '!=', $currentUser->id)
            ->get()
            ->map(function($contact) use ($currentUser) {
                // Cari pesan terakhir antara SAYA dan DIA (Bolak-balik)
                $lastMsg = Message::where(function($q) use ($currentUser, $contact) {
                        $q->where('user_id', $currentUser->id)->where('sender_id', $contact->id);
                    })
                    ->orWhere(function($q) use ($currentUser, $contact) {
                        $q->where('user_id', $contact->id)->where('sender_id', $currentUser->id);
                    })
                    ->latest()
                    ->first();

                // Hitung pesan belum dibaca dari dia
                $unreadCount = Message::where('user_id', $currentUser->id)
                    ->where('sender_id', $contact->id)
                    ->where('is_read', false)
                    ->count();

                $contact->last_message = $lastMsg ? $lastMsg->body : null;
                $contact->last_time = $lastMsg ? $lastMsg->created_at : null;
                $contact->unread = $unreadCount;
                
                return $contact;
            })
            // Urutkan: Yang ada pesan terbaru di atas, sisanya urut nama
            ->sortByDesc('last_time')
            ->values();

        // 2. AMBIL DETAIL CHAT (Jika ada kontak yang dipilih)
        $activeChat = [];
        $activeUser = null;
        $targetUserId = $request->query('user_id'); // Kita pakai ID, bukan nama

        if ($targetUserId) {
            $activeUser = User::find($targetUserId);
            
            if ($activeUser) {
                // Ambil semua chat history (Masuk & Keluar)
                $activeChat = Message::where(function($q) use ($currentUser, $activeUser) {
                        $q->where('user_id', $currentUser->id)->where('sender_id', $activeUser->id);
                    })
                    ->orWhere(function($q) use ($currentUser, $activeUser) {
                        $q->where('user_id', $activeUser->id)->where('sender_id', $currentUser->id);
                    })
                    ->orderBy('created_at', 'asc')
                    ->get();

                // Tandai pesan dari dia sebagai sudah dibaca
                Message::where('user_id', $currentUser->id)
                    ->where('sender_id', $activeUser->id)
                    ->update(['is_read' => true]);
            }
        }

        return Inertia::render('App/Profile/Messages', [
            'contacts' => $contacts, // List User 4 orang itu
            'activeChat' => $activeChat,
            'activeUser' => $activeUser, // Object User yang sedang dichat
            'currentUser' => $currentUser
        ]);
    }

    // 4. HALAMAN: LOG AKTIVITAS (DIPERBAIKI)
    public function activityLogs()
    {
        $user = auth()->user();
        
        $logs = ActivityLog::where('user_id', $user->id)
                        ->latest()
                        ->limit(50)
                        ->get()
                        ->map(function($log) {
                            // Parsing User Agent Sederhana
                            $agent = $log->user_agent;
                            $browser = 'Unknown Browser';
                            $os = 'Unknown OS';

                            if (strpos($agent, 'Firefox') !== false) $browser = 'Firefox';
                            elseif (strpos($agent, 'Chrome') !== false) $browser = 'Chrome';
                            elseif (strpos($agent, 'Safari') !== false) $browser = 'Safari';
                            elseif (strpos($agent, 'Edge') !== false) $browser = 'Edge';

                            if (strpos($agent, 'Windows') !== false) $os = 'Windows';
                            elseif (strpos($agent, 'Mac') !== false) $os = 'MacOS';
                            elseif (strpos($agent, 'Linux') !== false) $os = 'Linux';
                            elseif (strpos($agent, 'Android') !== false) $os = 'Android';
                            elseif (strpos($agent, 'iPhone') !== false) $os = 'iOS';

                            $log->device_info = "$browser on $os";
                            return $log;
                        });

        return Inertia::render('App/Profile/ActivityLogs', [
            'logs' => $logs,
            'user' => $user
        ]);
    }

    // --- ACTION: UPDATE AKUN ---
    public function updateAccount(Request $request)
    {
        $user = auth()->user();

        // ... (Validasi & Upload Foto TETAP SAMA seperti sebelumnya) ...
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'photo' => ['nullable', 'image', 'max:2048'],
            'current_password' => ['nullable', 'required_with:new_password', 'current_password'],
            'new_password' => ['nullable', 'confirmed', 'min:8'],
        ]);

        if ($request->hasFile('photo')) {
            if ($user->photo) Storage::disk('public')->delete($user->photo);
            $user->photo = $request->file('photo')->store('uploads/users', 'public');
        }

        $actionType = 'UPDATE_ACCOUNT'; // Default

        if ($request->filled('new_password')) {
            $user->password = Hash::make($request->new_password);
            $actionType = 'UPDATE_PASSWORD'; // Ubah jika ganti password
        }

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->save();

        // RECORD LOG LENGKAP
        ActivityLog::create([
            'user_id' => $user->id,
            'action' => $actionType,
            'description' => $actionType === 'UPDATE_PASSWORD' 
                ? 'Berhasil mengubah kata sandi akun.' 
                : 'Memperbarui informasi profil pengguna.',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent() // Simpan User Agent Browser
        ]);

        return back()->with('success', 'Pengaturan akun berhasil disimpan.');
    }
}