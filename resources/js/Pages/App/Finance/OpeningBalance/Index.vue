<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    hasOpeningBalance: Boolean
});
</script>

<template>
    <Head title="Setup Saldo Awal" />

    <AppLayout>
        <div class="px-4 md:px-6 space-y-6">
            
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-black text-gray-800 tracking-tight">Saldo Awal</h1>
                    <p class="text-gray-500 text-sm">Konfigurasi awal neraca saat migrasi sistem.</p>
                </div>
            </div>

            <div class="border-b border-gray-100 overflow-x-auto">
                <nav class="flex items-center gap-6 min-w-max">
                    <Link :href="route('finance.transactions.index')" class="py-4 px-1 text-gray-500 hover:text-emerald-600 font-medium text-sm border-b-2 border-transparent hover:border-emerald-200 flex items-center gap-2"><i class="pi pi-list"></i> Transaksi</Link>
                    <Link :href="route('finance.reports.index')" class="py-4 px-1 text-gray-500 hover:text-emerald-600 font-medium text-sm border-b-2 border-transparent hover:border-emerald-200 flex items-center gap-2"><i class="pi pi-chart-bar"></i> Laporan</Link>
                    <Link :href="route('finance.opening-balances.index')" class="py-4 px-1 text-emerald-700 font-bold text-sm border-b-2 border-emerald-500 flex items-center gap-2"><i class="pi pi-wallet"></i> Saldo Awal</Link>
                    <Link :href="route('finance.closing-periods.index')" class="py-4 px-1 text-gray-500 hover:text-emerald-600 font-medium text-sm border-b-2 border-transparent hover:border-emerald-200 flex items-center gap-2"><i class="pi pi-lock"></i> Tutup Buku</Link>
                    <Link :href="route('finance.accounts.index')" class="py-4 px-1 text-gray-500 hover:text-emerald-600 font-medium text-sm border-b-2 border-transparent hover:border-emerald-200 flex items-center gap-2"><i class="pi pi-book"></i> Master Akun</Link>
                </nav>
            </div>

            <div class="max-w-3xl mx-auto mt-10 text-center">
                
                <div v-if="hasOpeningBalance" class="bg-white p-8 rounded-3xl border border-gray-100 shadow-sm">
                    <div class="w-20 h-20 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="pi pi-check-circle text-4xl"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">Saldo Awal Sudah Disetup</h2>
                    <p class="text-gray-500 mb-8 max-w-md mx-auto">
                        Data saldo awal kas, bank, dan aset lainnya sudah tercatat di sistem. Anda tidak perlu melakukan konfigurasi ulang.
                    </p>
                    <Link :href="route('finance.transactions.index')" 
                          class="inline-flex items-center justify-center px-6 py-3 bg-gray-900 text-white font-bold rounded-xl hover:bg-gray-800 transition">
                        Kembali ke Transaksi
                    </Link>
                </div>

                <div v-else class="bg-white p-8 rounded-3xl border border-orange-100 shadow-sm relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-orange-400 to-red-500"></div>
                    
                    <div class="w-20 h-20 bg-orange-50 text-orange-600 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="pi pi-exclamation-triangle text-4xl"></i>
                    </div>
                    
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">Setup Saldo Awal Diperlukan</h2>
                    <p class="text-gray-500 mb-8 max-w-lg mx-auto">
                        Sebelum memulai pencatatan harian, mohon input sisa uang Kas Tunai dan Bank yang dimiliki Unit saat ini agar Laporan Neraca valid.
                    </p>
                    
                    <Link :href="route('finance.opening-balances.create')" 
                          class="inline-flex items-center gap-2 justify-center px-8 py-4 bg-emerald-600 text-white font-bold rounded-xl shadow-lg shadow-emerald-200 hover:bg-emerald-700 hover:scale-105 transition-all">
                        <i class="pi pi-pencil"></i>
                        Input Saldo Awal Sekarang
                    </Link>
                </div>

            </div>
        </div>
    </AppLayout>
</template>