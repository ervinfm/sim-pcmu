<?php

namespace App\Http\Controllers\App\Maps;

use App\Http\Controllers\Controller;
use App\Models\OrganizationUnit;
use Inertia\Inertia;

class MapController extends Controller
{
    public function index()
    {
        // Ambil unit yang punya koordinat saja
        $locations = OrganizationUnit::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->select('id', 'name', 'type', 'latitude', 'longitude', 'address')
            ->get();

        return Inertia::render('App/Maps/Index', [
            'locations' => $locations
        ]);
    }
}