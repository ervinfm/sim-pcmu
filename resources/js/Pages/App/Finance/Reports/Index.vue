<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import DatePicker from 'primevue/datepicker';
import Button from 'primevue/button';
import Select from 'primevue/select';

// Navigasi Tab (Sama di setiap halaman Finance)
import FinanceNavigation from '@/Components/Finance/FinanceNavigation.vue'; // Asumsi kita ekstrak nav jadi komponen, atau copy manual

// State Filter Laporan
const period = ref(new Date());
const reportType = ref(null);

const reportOptions = [
    { label: 'Neraca (Balance Sheet)', value: 'balance-sheet', icon: 'pi pi-building', desc: 'Posisi Harta, Utang, dan Modal per tanggal tertentu.' },
    { label: 'Laba Rugi (Income Statement)', value: 'income-statement', icon: 'pi pi-chart-line', desc: 'Pendapatan vs Beban selama periode tertentu.' },
    { label: 'Buku Besar (Ledger)', value: 'ledger', icon: 'pi pi-book', desc: 'Rincian mutasi debit/kredit per akun.' },
    { label: 'Arus Kas (Cash Flow)', value: 'cash-flow', icon: 'pi pi-money-bill', desc: 'Aliran masuk dan keluar uang kas.' },
];

const generateReport = () => {
    if (!reportType.value) return;
    
    // Redirect ke halaman detail laporan dengan query params
    const routeName = `finance.reports.${reportType.value}`;
    const dateStr = period.value.toISOString().split('T')[0];
    
    window.location.href = route(routeName, { date: dateStr });
};
</script>

<template>
    <Head title="Pusat Laporan Keuangan" />

    <AppLayout>
        <div class="px-4 md:px-6 space-y-6">
            
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-black text-gray-800 tracking-tight">Pusat Laporan</h1>
                    <p class="text-gray-500 text-sm">Analisis kinerja keuangan dan posisi aset unit.</p>
                </div>
            </div>

            <div class="border-b border-gray-100 overflow-x-auto">
                <nav class="flex items-center gap-6 min-w-max">
                    <Link :href="route('finance.transactions.index')" class="py-4 px-1 text-gray-500 hover:text-emerald-600 font-medium text-sm border-b-2 border-transparent hover:border-emerald-200 flex items-center gap-2"><i class="pi pi-list"></i> Transaksi</Link>
                    <Link :href="route('finance.reports.index')" class="py-4 px-1 text-emerald-700 font-bold text-sm border-b-2 border-emerald-500 flex items-center gap-2"><i class="pi pi-chart-bar"></i> Laporan</Link>
                    <Link :href="route('finance.opening-balances.index')" class="py-4 px-1 text-gray-500 hover:text-emerald-600 font-medium text-sm border-b-2 border-transparent hover:border-emerald-200 flex items-center gap-2"><i class="pi pi-wallet"></i> Saldo Awal</Link>
                    <Link :href="route('finance.closing-periods.index')" class="py-4 px-1 text-gray-500 hover:text-emerald-600 font-medium text-sm border-b-2 border-transparent hover:border-emerald-200 flex items-center gap-2"><i class="pi pi-lock"></i> Tutup Buku</Link>
                    <Link :href="route('finance.accounts.index')" class="py-4 px-1 text-gray-500 hover:text-emerald-600 font-medium text-sm border-b-2 border-transparent hover:border-emerald-200 flex items-center gap-2"><i class="pi pi-book"></i> Master Akun</Link>
                </nav>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <div class="lg:col-span-1 space-y-6">
                    <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
                        <h3 class="font-bold text-gray-800 mb-4">Filter Laporan</h3>
                        
                        <div class="space-y-4">
                            <div class="space-y-1">
                                <label class="text-xs font-bold text-gray-500 uppercase">Periode / Tanggal</label>
                                <DatePicker v-model="period" dateFormat="dd MM yy" showIcon fluid class="w-full" />
                            </div>

                            <div class="pt-2">
                                <Button label="Tampilkan Laporan" icon="pi pi-search" class="w-full" 
                                        :disabled="!reportType" @click="generateReport" />
                            </div>
                        </div>
                    </div>

                    <div class="bg-blue-50 p-6 rounded-2xl border border-blue-100 text-blue-800 text-sm">
                        <h4 class="font-bold mb-2 flex items-center gap-2"><i class="pi pi-info-circle"></i> Catatan</h4>
                        <p>Laporan yang dihasilkan bersifat <b>Realtime</b> berdasarkan input transaksi harian. Pastikan semua transaksi bulan ini sudah terinput sebelum mencetak laporan.</p>
                    </div>
                </div>

                <div class="lg:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div v-for="rpt in reportOptions" :key="rpt.value" 
                         @click="reportType = rpt.value"
                         class="cursor-pointer group p-5 rounded-2xl border transition-all duration-300 relative overflow-hidden"
                         :class="reportType === rpt.value 
                            ? 'bg-emerald-50 border-emerald-500 shadow-md ring-1 ring-emerald-500' 
                            : 'bg-white border-gray-100 hover:border-emerald-200 hover:shadow-lg'">
                        
                        <div class="flex items-start justify-between mb-3">
                            <div class="w-12 h-12 rounded-xl flex items-center justify-center transition-colors"
                                 :class="reportType === rpt.value ? 'bg-emerald-200 text-emerald-700' : 'bg-gray-50 text-gray-400 group-hover:bg-emerald-50 group-hover:text-emerald-600'">
                                <i :class="rpt.icon" class="text-2xl"></i>
                            </div>
                            <div v-if="reportType === rpt.value" class="w-6 h-6 bg-emerald-500 rounded-full flex items-center justify-center">
                                <i class="pi pi-check text-white text-xs"></i>
                            </div>
                        </div>

                        <h4 class="font-bold text-gray-800 text-lg mb-1">{{ rpt.label }}</h4>
                        <p class="text-sm text-gray-500 leading-relaxed">{{ rpt.desc }}</p>
                    </div>
                </div>

            </div>
        </div>
    </AppLayout>
</template>