<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\OrganizationUnit;
use App\Models\SiteSetting;
use Inertia\Inertia;

class PageController extends Controller
{
    // Halaman Profil / Sejarah / Visi Misi
    public function profile()
    {
        $pcm = OrganizationUnit::where('type', 'PCM')->first();
        $settings = SiteSetting::whereIn('key', ['visi', 'misi', 'sejarah'])->pluck('value', 'key');

        return Inertia::render('Public/Profile', [
            'pcm' => $pcm,
            'settings' => $settings
        ]);
    }

    // Halaman Struktur Organisasi & AUM
    public function structure()
    {
        // Ambil PCM beserta anak-anaknya (PRM & AUM)
        // Kita load juga relasi 'villages' untuk PRM
        $structure = OrganizationUnit::where('type', 'PCM')
            ->with(['children.villages']) 
            ->first();

        return Inertia::render('Public/Structure', [
            'structure' => $structure
        ]);
    }
}