<?php

namespace App\Http\Controllers\App\Archives;

use App\Http\Controllers\Controller;
use App\Models\Archive;
use App\Models\ArchiveDisposition;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ArchiveDispositionController extends Controller
{
    /**
     * Inbox Disposisi (Daftar Tugas Masuk).
     */
    public function index()
    {
        // Cari Member ID dari User yang login
        $currentMember = Member::where('user_id', Auth::id())->first();

        if (!$currentMember) {
            return to_route('dashboard')->with('error', 'Akun Anda belum terhubung dengan data Keanggotaan.');
        }

        // Ambil disposisi yang ditujukan ke Member ini
        $dispositions = ArchiveDisposition::with(['archive', 'sender', 'sender.organizationUnit'])
            ->where('receiver_member_id', $currentMember->id)
            ->latest()
            ->get();

        return Inertia::render('App/Archives/Dispositions/Index', [
            'dispositions' => $dispositions
        ]);
    }

    /**
     * Form Buat Disposisi Baru.
     */
    public function create(Archive $archive)
    {
        // Ambil daftar anggota aktif untuk tujuan disposisi
        // Optimasi: Select kolom penting saja
        $members = Member::where('status', 'ACTIVE')
            ->with('organizationUnit') // Supaya tahu jabatan/unitnya
            ->get()
            ->map(function ($member) {
                return [
                    'id' => $member->id,
                    'name' => $member->full_name . ' (' . ($member->organizationUnit->name ?? '-') . ')',
                ];
            });
        
        return Inertia::render('App/Archives/Dispositions/Create', [
            'archive' => $archive,
            'members' => $members
        ]);
    }

    /**
     * Simpan Disposisi (Kirim Perintah).
     */
    public function store(Request $request, Archive $archive)
    {
        $validated = $request->validate([
            'receiver_member_id' => 'required|exists:members,id',
            'instruction'        => 'required|string|max:255',
            'due_date'           => 'nullable|date',
            'note'               => 'nullable|string',
        ]);

        $senderMember = Member::where('user_id', Auth::id())->first();

        ArchiveDisposition::create([
            'archive_id'         => $archive->id,
            'sender_member_id'   => $senderMember ? $senderMember->id : null,
            'receiver_member_id' => $validated['receiver_member_id'],
            'instruction'        => $validated['instruction'],
            'note'               => $validated['note'],
            'due_date'           => $validated['due_date'],
        ]);

        return to_route('archives.show', $archive->id)
                ->with('success', 'Disposisi berhasil dikirim.');
    }

    /**
     * Tandai sudah dibaca (Otomatis via API).
     */
    public function markAsRead(ArchiveDisposition $disposition)
    {
        if (is_null($disposition->read_at)) {
            $disposition->update(['read_at' => now()]);
        }
        return response()->json(['status' => 'success']);
    }

    /**
     * Selesaikan Disposisi (Tindak Lanjut).
     */
    public function update(Request $request, ArchiveDisposition $disposition)
    {
        $validated = $request->validate([
            'completion_note' => 'required|string',
        ]);

        $currentMember = Member::where('user_id', Auth::id())->first();
        
        // Security Check
        if ($disposition->receiver_member_id !== $currentMember->id) {
            return back()->with('error', 'Anda tidak berhak menyelesaikan disposisi ini.');
        }

        $disposition->update([
            'completed_at'    => now(),
            'completion_note' => $validated['completion_note'],
        ]);

        return back()->with('success', 'Disposisi telah ditindaklanjuti.');
    }
}