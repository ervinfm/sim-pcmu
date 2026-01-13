<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\OrganizationUnit;
use App\Models\Post;
use App\Models\SiteSetting;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index()
    {
        // 1. Ambil Data Profil PCM (Untuk Footer/Header)
        $pcm = OrganizationUnit::where('type', 'PCM')->first();

        // 2. Ambil 3 Berita Terbaru (Status Published)
        $latestPosts = Post::with('author')
            ->where('status', 'PUBLISHED')
            ->latest('published_at')
            ->take(3)
            ->get();

        // 3. Ambil Konfigurasi Web (Slider, Sambutan, dll)
        // Kita ambil semua setting dan format jadi key-value pair agar mudah diakses di Vue
        $settings = SiteSetting::pluck('value', 'key');

        return Inertia::render('Public/Home', [
            'pcm' => $pcm,
            'latestPosts' => $latestPosts,
            'settings' => $settings,
        ]);
    }
}