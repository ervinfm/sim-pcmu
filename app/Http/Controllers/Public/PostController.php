<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Inertia\Inertia;

class PostController extends Controller
{
    // Daftar Semua Berita (Archive)
    public function index()
    {
        $posts = Post::with('author')
            ->where('status', 'PUBLISHED')
            ->latest('published_at')
            ->paginate(9); // 9 Berita per halaman

        return Inertia::render('Public/News/Index', [
            'posts' => $posts
        ]);
    }

    // Baca 1 Berita Detail
    public function show($slug)
    {
        $post = Post::with(['author', 'organizationUnit'])
            ->where('slug', $slug)
            ->where('status', 'PUBLISHED')
            ->firstOrFail(); // 404 jika tidak ketemu

        // Ambil berita lain untuk sidebar "Berita Terkait"
        $relatedPosts = Post::where('id', '!=', $post->id)
            ->where('status', 'PUBLISHED')
            ->latest('published_at')
            ->take(5)
            ->get();

        return Inertia::render('Public/News/Show', [
            'post' => $post,
            'relatedPosts' => $relatedPosts
        ]);
    }
}