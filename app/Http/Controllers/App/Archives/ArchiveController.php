<?php

namespace App\Http\Controllers\App\Archives;

use App\Http\Controllers\Controller;
use App\Models\Archive;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class ArchiveController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        $query = Archive::query();
        if ($user->role !== 'super_admin' && $user->organization_unit_id) {
            $query->where('organization_unit_id', $user->organization_unit_id);
        }

        return Inertia::render('App/Archives/Index', [
            'archives' => $query->latest('document_date')->get()
        ]);
    }

    public function create()
    {
        return Inertia::render('App/Archives/Form');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'category' => 'required|in:SURAT_MASUK,SURAT_KELUAR,SK,LAINNYA',
            'reference_number' => 'required|string',
            'title' => 'required|string',
            'document_date' => 'required|date',
            'sender' => 'nullable|string',
            'receiver' => 'nullable|string',
            'file' => 'required|file|mimes:pdf,jpg,png|max:2048', // Max 2MB
        ]);

        // Handle File Upload
        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('archives', 'public');
            $data['file_path'] = $path;
        }

        // Auto-assign Unit ID
        $data['organization_unit_id'] = auth()->user()->organization_unit_id;
        
        // Hapus key 'file' dari array data karena tidak ada di kolom DB
        unset($data['file']); 

        Archive::create($data);

        return redirect()->route('archives.index')->with('success', 'Dokumen berhasil diarsipkan.');
    }
    
    // Tambahkan method destroy/show nanti jika perlu
}