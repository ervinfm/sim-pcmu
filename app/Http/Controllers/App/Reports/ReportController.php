<?php

namespace App\Http\Controllers\App\Reports;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
// Import Model untuk Data Laporan
use App\Models\Member;
use App\Models\Asset;
use App\Models\OrganizationUnit;

class ReportController extends Controller
{
    public function index()
    {
        // Contoh Logika Ringkas untuk Laporan
        $stats = [
            'members_total' => Member::count(),
            'members_active' => Member::where('status', 'ACTIVE')->count(),
            'assets_value' => Asset::sum('acquisition_value'),
            'units_count' => OrganizationUnit::count(),
        ];

        return Inertia::render('App/Reports/Index', [
            'stats' => $stats
        ]);
    }
}