<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({ stats: Object });

const reports = [
    { name: 'Laporan Keuangan Bulanan', desc: 'Rekapitulasi pemasukan dan pengeluaran kas.', icon: 'pi pi-wallet' },
    { name: 'Laporan Aset & Inventaris', desc: 'Daftar aset AUM beserta nilai penyusutannya.', icon: 'pi pi-box' },
    { name: 'Laporan Database Anggota', desc: 'Statistik pertumbuhan anggota per cabang.', icon: 'pi pi-users' },
];
</script>

<template>
    <Head title="Pusat Laporan" />
    <AppLayout title="Pusat Laporan Terpadu">
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-800">Pusat Laporan Terpadu</h1>
            <p class="text-gray-500 text-sm">Generasi laporan untuk kebutuhan analisis dan rapat pimpinan.</p>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm">
                <div class="text-gray-400 text-xs font-bold uppercase">Total Anggota</div>
                <div class="text-2xl font-black text-gray-800">{{ stats.members_total }}</div>
            </div>
            <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm">
                <div class="text-gray-400 text-xs font-bold uppercase">Nilai Aset</div>
                <div class="text-2xl font-black text-gray-800">Rp {{ stats.assets_value.toLocaleString() }}</div>
            </div>
            </div>

        <h3 class="font-bold text-gray-800 mb-4">Cetak Laporan</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div v-for="(rpt, idx) in reports" :key="idx" 
                 class="bg-white p-6 rounded-2xl border border-gray-100 hover:shadow-lg transition cursor-pointer group">
                <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center mb-4 text-xl group-hover:bg-emerald-600 group-hover:text-white transition">
                    <i :class="rpt.icon"></i>
                </div>
                <h4 class="font-bold text-gray-800 mb-1">{{ rpt.name }}</h4>
                <p class="text-sm text-gray-500 mb-4">{{ rpt.desc }}</p>
                <button class="w-full py-2 rounded-lg border border-gray-200 text-sm font-bold text-gray-600 hover:bg-gray-50">
                    <i class="pi pi-download mr-1"></i> Download PDF
                </button>
            </div>
        </div>
    </AppLayout>
</template>