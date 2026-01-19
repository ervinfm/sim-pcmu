<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use Inertia\Inertia;

class AssetController extends Controller
{
    public function show($id)
    {
        // 1. Ambil data aset dengan relasi terbatas (Hanya yang aman untuk publik)
        // Gunakan findOrFail agar jika ID ngawur, muncul 404
        $asset = Asset::with(['location', 'images', 'activeLoan'])
            ->where('id', $id)
            ->firstOrFail();

        // 2. Return View khusus Publik (Guest Layout)
        return Inertia::render('Public/Asset/Show', [
            'asset' => $asset
        ]);
    }
}