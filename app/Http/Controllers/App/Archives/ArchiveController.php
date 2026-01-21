<?php

namespace App\Http\Controllers\App\Archives;

use App\Http\Controllers\Controller;
use App\Models\Archive;
use App\Models\OrganizationUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ArchiveController extends Controller
{
    /**
     * Menampilkan daftar arsip.
     * Mengirim FULL DATA untuk diproses Client-side (Vue).
     */
    public function index()
    {
        // Eager load relasi uploader dan unit untuk ditampilkan di tabel
        $archives = Archive::with(['uploader', 'organizationUnit'])
            ->latest()
            ->get(); 

        return Inertia::render('App/Archives/Index', [
            'archives' => $archives
        ]);
    }

    /**
     * Form upload arsip baru.
     */
    public function create()
    {
        // Kirim data unit untuk dropdown pilihan
        $units = OrganizationUnit::select('id', 'name', 'type')->get();
        
        // Menggunakan view Form.vue yang sama untuk Create & Edit
        return Inertia::render('App/Archives/Form', [
            'units' => $units
        ]);
    }

    /**
     * Proses simpan arsip ke Database & Storage.
     */
    public function store(Request $request)
    {
        // 1. Validasi
        $validated = $request->validate([
            'organization_unit_id' => 'required|exists:organization_units,id',
            'title'                => 'required|string|max:255',
            'category'             => 'required|string',
            'file'                 => 'required|file|max:10240',
            // ... field lain ...
            'document_date'        => 'nullable|date_format:Y-m-d', // Pastikan format ini
            'received_date'        => 'nullable|date_format:Y-m-d',
        ]);

        // LANGSUNG EKSEKUSI TANPA TRY-CATCH
        // Agar jika error, muncul layar merah Laravel
        
        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension();
        $fileSize = (int) ceil($file->getSize() / 1024);
        $type = $this->determineFileType($extension);

        $slug = Str::slug($request->title) . '-' . time();
        $fileName = $slug . '.' . $extension;
        
        // Simpan file
        $path = $file->storeAs('archives/' . date('Y/m'), $fileName, 'public');

        Archive::create([
            'organization_unit_id' => $request->organization_unit_id,
            'user_id'              => Auth::id(),
            'title'                => $request->title,
            'slug'                 => $slug,
            'description'          => $request->description,
            'category'             => $request->category,
            'type'                 => $type,
            'file_path'            => $path,
            'file_extension'       => $extension,
            'file_size'            => $fileSize,
            'status'               => 'published',
            'reference_number'     => $request->reference_number,
            'classification_code'  => $request->classification_code,
            'document_date'        => $request->document_date,
            'received_date'        => $request->received_date,
            'sender'               => $request->sender,
            'receiver'             => $request->receiver,
            'confidentiality'      => $request->confidentiality ?? 'BIASA',
            'published_at'         => now(),
        ]);

        return to_route('archives.index')->with('success', 'Arsip berhasil disimpan.');
    }

    /**
     * Menampilkan detail arsip.
     */
    public function show(Archive $archive)
    {
        // Load detail beserta history disposisi
        $archive->load(['dispositions.sender', 'dispositions.receiver', 'uploader', 'organizationUnit']);
        
        return Inertia::render('App/Archives/Show', [
            'archive' => $archive
        ]);
    }

    /**
     * Form Edit Arsip.
     */
    public function edit(Archive $archive)
    {
        $units = OrganizationUnit::select('id', 'name', 'type')->get();

        return Inertia::render('App/Archives/Form', [
            'archive' => $archive, // Mengirim data existing
            'units' => $units
        ]);
    }

    /**
     * Proses Update Arsip.
     */
    public function update(Request $request, Archive $archive)
    {
        // 1. Validasi
        // Kita gunakan 'sometimes' agar jika input file tidak ada/null, validasi file dilewati
        $validated = $request->validate([
            'organization_unit_id' => 'required|exists:organization_units,id',
            'title'                => 'required|string|max:255',
            'category'             => 'required|string',
            // Ganti nullable dengan sometimes agar aman dari string "null"
            'file'                 => 'sometimes|file|max:10240', 
            
            // Field Opsional
            'reference_number'     => 'nullable|string',
            'classification_code'  => 'nullable|string',
            'document_date'        => 'nullable|date',
            'received_date'        => 'nullable|date',
            'sender'               => 'nullable|string',
            'receiver'             => 'nullable|string',
            'confidentiality'      => 'nullable|string',
            'description'          => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Ambil semua data kecuali file (karena file butuh penanganan khusus)
            $data = collect($validated)->except(['file'])->toArray();

            // 2. Cek apakah ada file baru yang valid
            if ($request->hasFile('file') && $request->file('file')->isValid()) {
                
                // Hapus file lama jika ada
                if ($archive->file_path && Storage::disk('public')->exists($archive->file_path)) {
                    Storage::disk('public')->delete($archive->file_path);
                }

                // Upload file baru
                $file = $request->file('file');
                $extension = $file->getClientOriginalExtension();
                $fileSize = (int) ceil($file->getSize() / 1024);
                $type = $this->determineFileType($extension);
                
                $slug = Str::slug($request->title) . '-' . time();
                $fileName = $slug . '.' . $extension;
                $path = $file->storeAs('archives/' . date('Y/m'), $fileName, 'public');

                // Update data file
                $data['file_path'] = $path;
                $data['file_extension'] = $extension;
                $data['file_size'] = $fileSize;
                $data['type'] = $type;
                $data['slug'] = $slug;
            } 
            // Jika Judul berubah, update slug juga (opsional)
            elseif ($archive->title !== $validated['title']) {
                $data['slug'] = Str::slug($validated['title']) . '-' . time();
            }

            // 3. Update Database
            $archive->update($data);

            DB::commit();
            return to_route('archives.index')->with('success', 'Arsip berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memperbarui arsip: ' . $e->getMessage());
        }
    }

    /**
     * Download file.
     * NOTE: Ini BUKAN Inertia Request.
     */
    public function download(Archive $archive)
    {
        if (!Storage::disk('public')->exists($archive->file_path)) {
            return back()->with('error', 'File fisik tidak ditemukan.');
        }

        $archive->increment('download_count');

        return Storage::disk('public')->download($archive->file_path, $archive->slug . '.' . $archive->file_extension);
    }

    /**
     * Preview file.
     * NOTE: Ini BUKAN Inertia Request.
     */
    public function preview(Archive $archive)
    {
        if (!Storage::disk('public')->exists($archive->file_path)) {
            abort(404);
        }

        $path = storage_path('app/public/' . $archive->file_path);
        
        return response()->file($path);
    }

    /**
     * Hapus arsip.
     */
    public function destroy(Archive $archive)
    {
        // Opsional: Hapus file fisik jika menggunakan Hard Delete.
        // Karena SoftDeletes, kita biarkan file fisik tetap ada (atau hapus jika perlu).
        
        $archive->delete();
        return to_route('archives.index')->with('success', 'Arsip berhasil dihapus.');
    }

    // --- HELPER ---
    private function determineFileType($ext)
    {
        $images = ['jpg', 'jpeg', 'png', 'gif', 'svg'];
        $docs   = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'];
        $videos = ['mp4', 'avi', 'mov'];
        $audios = ['mp3', 'wav'];
        $archives = ['zip', 'rar'];

        if (in_array(strtolower($ext), $images)) return 'image';
        if (in_array(strtolower($ext), $docs)) return 'document';
        if (in_array(strtolower($ext), $videos)) return 'video';
        if (in_array(strtolower($ext), $audios)) return 'audio';
        if (in_array(strtolower($ext), $archives)) return 'archive';

        return 'other';
    }
}