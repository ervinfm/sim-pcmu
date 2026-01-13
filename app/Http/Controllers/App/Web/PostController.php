<?php

namespace App\Http\Controllers\App\Web;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\PostAttachment;
use App\Models\PostGallery;
use App\Http\Requests\PostRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        $query = Post::with(['author', 'organizationUnit', 'category'])
                     ->withCount(['attachments', 'galleries']); // Hitung jumlah file & foto

        if ($user->role !== 'super_admin' && $user->organization_unit_id) {
            $query->where('organization_unit_id', $user->organization_unit_id);
        }

        return Inertia::render('App/Posts/Index', [
            'posts' => $query->latest('updated_at')->get()
        ]);
    }

    public function create()
    {
        return Inertia::render('App/Posts/Form', [
            'categories' => PostCategory::orderBy('name')->get()
        ]);
    }

    public function store(PostRequest $request)
    {
        $data = $request->validated();
        $user = auth()->user();

        // 1. Slug & Meta
        $data['slug'] = empty($data['slug']) ? Str::slug($data['title']) : Str::slug($data['slug']);
        $data['user_id'] = $user->id;
        $data['organization_unit_id'] = $user->organization_unit_id;
        
        if (empty($data['excerpt'])) {
            $data['excerpt'] = Str::limit(strip_tags($data['content']), 150);
        }

        // 2. Thumbnail Upload
        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $request->file('thumbnail')->store('uploads/posts/covers', 'public');
        }

        // 3. Create Post
        $post = Post::create($data);

        // 4. Handle Attachments (Dokumen)
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('uploads/posts/attachments', 'public');
                PostAttachment::create([
                    'post_id' => $post->id,
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getClientOriginalExtension(),
                    'file_size' => $file->getSize()
                ]);
            }
        }

        // 5. Handle Gallery (Dokumentasi Kegiatan)
        if ($request->hasFile('galleries')) {
            foreach ($request->file('galleries') as $image) {
                $path = $image->store('uploads/posts/galleries', 'public');
                PostGallery::create([
                    'post_id' => $post->id,
                    'image_path' => $path,
                    'caption' => null
                ]);
            }
        }

        return redirect()->route('posts.index')->with('success', 'Konten berhasil diterbitkan.');
    }

    public function edit(Post $post)
    {
        $user = auth()->user();
        if ($user->role !== 'super_admin' && $post->organization_unit_id !== $user->organization_unit_id) {
            abort(403);
        }

        // Load relasi file
        $post->load(['attachments', 'galleries']);

        return Inertia::render('App/Posts/Form', [
            'post' => $post,
            'categories' => PostCategory::orderBy('name')->get()
        ]);
    }

    public function update(PostRequest $request, Post $post)
    {
        $data = $request->validated();
        $data['slug'] = empty($data['slug']) ? Str::slug($data['title']) : Str::slug($data['slug']);

        if ($request->hasFile('thumbnail')) {
            if ($post->thumbnail) Storage::disk('public')->delete($post->thumbnail);
            $data['thumbnail'] = $request->file('thumbnail')->store('uploads/posts/covers', 'public');
        }

        $post->update($data);

        // Handle New Attachments
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('uploads/posts/attachments', 'public');
                PostAttachment::create([
                    'post_id' => $post->id,
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getClientOriginalExtension(),
                    'file_size' => $file->getSize()
                ]);
            }
        }

        // Handle New Gallery Images
        if ($request->hasFile('galleries')) {
            foreach ($request->file('galleries') as $image) {
                $path = $image->store('uploads/posts/galleries', 'public');
                PostGallery::create([
                    'post_id' => $post->id,
                    'image_path' => $path
                ]);
            }
        }

        return redirect()->route('posts.index')->with('success', 'Konten diperbarui.');
    }

    public function destroy(Post $post)
    {
        // Hapus fisik file
        if ($post->thumbnail) Storage::disk('public')->delete($post->thumbnail);
        
        foreach ($post->attachments as $f) Storage::disk('public')->delete($f->file_path);
        foreach ($post->galleries as $g) Storage::disk('public')->delete($g->image_path);
        
        $post->delete();
        return redirect()->route('posts.index')->with('success', 'Berita dihapus.');
    }

    // Hapus 1 Attachment
    public function destroyAttachment($id)
    {
        $file = PostAttachment::findOrFail($id);
        Storage::disk('public')->delete($file->file_path);
        $file->delete();
        return back()->with('success', 'Lampiran dihapus.');
    }

    // Hapus 1 Foto Galeri
    public function destroyGallery($id)
    {
        $img = PostGallery::findOrFail($id);
        Storage::disk('public')->delete($img->image_path);
        $img->delete();
        return back()->with('success', 'Foto dihapus dari galeri.');
    }
}