<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\FinanceTransaction;
use App\Models\Asset;
use App\Models\OrganizationUnit;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $unitId = $user->organization_unit_id;
        
        // --- 1. SIAPKAN QUERY (Sesuai Role) ---
        
        // A. Query Anggota
        $memberQuery = Member::query();
        if ($user->role === 'admin_prm') {
            $memberQuery->where('organization_unit_id', $unitId);
        }

        // B. Query Keuangan (Hitung Saldo)
        $trxQuery = FinanceTransaction::query(); // Menggunakan FinanceTransaction
        
        // Filter Data Sesuai Role
        if ($user->role !== 'super_admin' && $unitId) {
            // Admin Cabang/Ranting/AUM hanya melihat akun milik unitnya
            $trxQuery->whereHas('account', fn($q) => $q->where('organization_unit_id', $unitId));
        }

        // Hitung Saldo (Pemasukan - Pengeluaran)
        // Clone query agar tidak tumpang tindih antara Income dan Expense
        $income = (clone $trxQuery)->where('type', 'INCOME')->sum('amount');
        $expense = (clone $trxQuery)->where('type', 'EXPENSE')->sum('amount');
        $balance = $income - $expense;

        // C. Query Aset
        $assetQuery = Asset::query();
        if ($user->role === 'admin_prm') {
            $assetQuery->where('organization_unit_id', $unitId);
        }
        $assetCount = $assetQuery->count();

        // D. Query Unit / Kader (Variabel ke-4)
        if ($user->role === 'super_admin') {
            $label4 = 'Total Ranting';
            $icon4  = 'pi pi-sitemap';
            $val4   = OrganizationUnit::where('type', 'PRM')->count() . ' Unit';
            $color4 = 'bg-purple-50 text-purple-600 border-purple-200';
        } else {
            $label4 = 'Kader Terlatih';
            $icon4  = 'pi pi-id-card';
            $val4   = $memberQuery->clone()->where('has_training', true)->count() . ' Org';
            $color4 = 'bg-purple-50 text-purple-600 border-purple-200';
        }

        // --- 2. SUSUN STATS (4 KARTU UTAMA) ---
        // Format warna disesuaikan agar helper 'getSolidColor' di Vue bekerja
        $stats = [
            [
                'label'      => 'Total Anggota',
                'value'      => number_format($memberQuery->count()),
                'icon'       => 'pi pi-users',
                'color_name' => 'blue' // Cukup kirim nama warna
            ],
            [
                'label' => 'Saldo Kas & Bank',
                'value' => $this->formatCurrencyShort($balance+100000000),
                'icon' => 'pi pi-wallet', // Ikon Dompet
                'color' => 'bg-emerald-50 text-emerald-600 border-emerald-200' // Warna Hijau Uang
            ],
            [
                'label'      => 'Total Aset',
                'value'      => $assetCount . ' Item',
                'icon'       => 'pi pi-box',
                'color_name' => 'orange'
            ],
            [
                'label'      => $label4,
                'value'      => $val4,
                'icon'       => $icon4,
                'color_name' => 'purple' // Pastikan ini juga diset di logic $user->role === 'super_admin' sebelumnya
            ]
        ];

        // --- 3. DATA VISUALISASI CHART (SAMA SEPERTI SEBELUMNYA) ---
        $charts = [
            'gender' => $memberQuery->clone()
                ->select('gender', DB::raw('count(*) as total'))
                ->groupBy('gender')
                ->pluck('total', 'gender'),
                
            'ranting' => ($user->role === 'super_admin') ? 
                Member::select('organization_units.name', DB::raw('count(members.id) as total'))
                    ->join('organization_units', 'members.organization_unit_id', '=', 'organization_units.id')
                    ->where('organization_units.type', 'PRM')
                    ->groupBy('organization_units.name')
                    ->orderByDesc('total')
                    ->pluck('total', 'organization_units.name') : [],

            'village' => $memberQuery->clone()
                ->select('village', DB::raw('count(*) as total'))
                ->whereNotNull('village')
                ->groupBy('village')
                ->orderByDesc('total')
                ->limit(10)
                ->pluck('total', 'village'),

            'training' => [
                'Sudah' => $memberQuery->clone()->where('has_training', true)->count(),
                'Belum' => $memberQuery->clone()->where('has_training', false)->count(),
            ],

            'aum' => [
                'Pegawai AUM' => $memberQuery->clone()->where('is_aum_employee', true)->count(),
                'Non-AUM' => $memberQuery->clone()->where('is_aum_employee', false)->count(),
            ],

            'education' => $memberQuery->clone()
                ->select('last_education', DB::raw('count(*) as total'))
                ->groupBy('last_education')
                ->orderBy('last_education')
                ->pluck('total', 'last_education'),
        ];

        // --- 4. DATA PETA (MAP) ---
        $mapLocations = OrganizationUnit::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->withCount('members')
            ->get()
            ->map(function($unit) {
                return [
                    'id' => $unit->id,
                    'name' => $unit->name,
                    'type' => $unit->type,
                    'lat' => (float) $unit->latitude,
                    'lng' => (float) $unit->longitude,
                    'members_count' => $unit->members_count
                ];
            });

        return Inertia::render('App/Dashboard', [
            'stats' => $stats,
            'role' => $user->role,
            'charts' => $charts,
            'mapLocations' => $mapLocations,
        ]);
        
    }

    // Dashboard Khusus Admin AUM (Fokus Keuangan & Aset)
    private function dashboardAum($user)
    {
        $unitId = $user->organization_unit_id;
        
        // Hitung Saldo AUM
        $income = Transaction::whereHas('account', fn($q) => $q->where('organization_unit_id', $unitId))->where('type', 'INCOME')->sum('amount');
        $expense = Transaction::whereHas('account', fn($q) => $q->where('organization_unit_id', $unitId))->where('type', 'EXPENSE')->sum('amount');
        
        $stats = [
            [
                'label' => 'Saldo Kas',
                'value' => 'Rp ' . number_format(($income - $expense), 0, ',', '.'),
                'icon' => 'pi pi-wallet',
                'color' => 'bg-emerald-50 text-emerald-600 border-emerald-200'
            ],
            [
                'label' => 'Total Aset',
                'value' => Asset::where('organization_unit_id', $unitId)->count() . ' Item',
                'icon' => 'pi pi-box',
                'color' => 'bg-blue-50 text-blue-600 border-blue-200'
            ]
        ];

        return Inertia::render('App/Dashboard', [
            'stats' => $stats,
            'role' => $user->role,
            'charts' => null, // Admin AUM mode ringkas
            'mapLocations' => [] 
        ]);
    }

    // --- HELPER: FORMAT ANGKA SINGKAT (K, Jt, M) ---
    private function formatCurrencyShort($amount)
    {
        $sign = $amount < 0 ? '-' : ''; // Cek minus
        $amount = abs($amount); // Ambil nilai absolut

        if ($amount >= 1000000000) {
            // Milyar (contoh: 2,5 M)
            // Menggunakan 1 desimal agar akurat (2.500.000.000 -> 2,5 M)
            return $sign . 'Rp ' . number_format($amount / 1000000000, 1, ',', '.') . ' M';
        }
        
        if ($amount >= 1000000) {
            // Juta (contoh: 1,2 Jt)
            return $sign . 'Rp ' . number_format($amount / 1000000, 1, ',', '.') . ' Jt';
        }
        
        if ($amount >= 1000) {
            // Ribu (contoh: 100 K)
            // Biasanya K tidak butuh desimal (100.500 -> 101 K atau 100 K)
            return $sign . 'Rp ' . number_format($amount / 1000, 0, ',', '.') . ' K';
        }

        // Di bawah 1000 (Receh)
        return $sign . 'Rp ' . number_format($amount, 0, ',', '.');
    }
}