<script setup>
import { ref, watch, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { FilterMatchMode } from '@primevue/core/api';

// PrimeVue v4 Components
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import Tag from 'primevue/tag';
import SelectButton from 'primevue/selectbutton';
import Select from 'primevue/select';
import ConfirmPopup from 'primevue/confirmpopup';
import Badge from 'primevue/badge';
import { useConfirm } from "primevue/useconfirm";

import { useForm } from '@inertiajs/vue3';
import Dialog from 'primevue/dialog';
import Password from 'primevue/password';
import Checkbox from 'primevue/checkbox';

// --- LOGIC TAMBAH AKUN ---
const showModal = ref(false);

const accountForm = useForm({
    name: '',
    type: 'ASSET',
    is_cash: false,
    password: '' // Konfirmasi Password
});

const accountTypes = [
    { label: 'Harta / Aset', value: 'ASSET' },
    { label: 'Kewajiban / Utang', value: 'LIABILITY' },
    { label: 'Pendapatan', value: 'REVENUE' },
    { label: 'Pengeluaran', value: 'EXPENSE' }
];

const submitAccount = () => {
    accountForm.post(route('finance.accounts.store'), {
        onSuccess: () => {
            showModal.value = false;
            accountForm.reset();
        },
        preserveScroll: true
    });
};

const props = defineProps({
    transactions: Object,
    balances: Object, // { total: 1000, details: [...] }
    filters: Object,
    units: Array
});

// --- FORMATTER ---
const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(value);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('id-ID', {
        day: 'numeric', month: 'short', year: 'numeric'
    });
};

const formatTime = (date) => {
    return new Date(date).toLocaleTimeString('id-ID', {
        hour: '2-digit', minute: '2-digit'
    });
};

// --- LOGIC UI HELPER ---
const getSeverity = (type) => {
    switch (type) {
        case 'INCOME': return 'success';
        case 'EXPENSE': return 'danger';
        case 'TRANSFER': return 'info';
        default: return 'secondary';
    }
};

const getIcon = (type) => {
    switch (type) {
        case 'INCOME': return 'pi pi-arrow-down-left';
        case 'EXPENSE': return 'pi pi-arrow-up-right';
        case 'TRANSFER': return 'pi pi-arrow-right-arrow-left';
        default: return 'pi pi-circle';
    }
};

const getLabel = (type) => {
    const map = { 'INCOME': 'Pemasukan', 'EXPENSE': 'Pengeluaran', 'TRANSFER': 'Mutasi' };
    return map[type] || type;
};

// --- FILTER LOGIC ---
const search = ref(props.filters.search || '');
const selectedType = ref(props.filters.type || null);
const selectedUnit = ref(props.filters.unit_id || null);
const viewMode = ref('list'); // 'list' only for now as table is primary

const typeOptions = [
    { label: 'Semua', value: null },
    { label: 'Masuk', value: 'INCOME' },
    { label: 'Keluar', value: 'EXPENSE' },
    { label: 'Mutasi', value: 'TRANSFER' }
];

let timeout = null;
const handleSearch = () => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        router.get(route('transactions.index'), { 
            search: search.value, 
            type: selectedType.value,
            unit_id: selectedUnit.value
        }, { preserveState: true, replace: true });
    }, 300);
};

watch([selectedType, selectedUnit], handleSearch);

// --- DELETE LOGIC ---
const confirm = useConfirm();
const handleDelete = (event, id) => {
    confirm.require({
        target: event.currentTarget,
        message: 'Hapus transaksi ini? Saldo akan dikembalikan (Rollback).',
        header: 'Konfirmasi Hapus',
        icon: 'pi pi-exclamation-triangle',
        acceptClass: 'p-button-danger',
        accept: () => router.delete(route('transactions.destroy', id))
    });
};
</script>

<template>
    <Head title="Keuangan & Arus Kas" />

    <AppLayout>
        <div class="max-w-7xl mx-auto space-y-8 pb-20">
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
                
                <div class="lg:col-span-1 space-y-5">
                    
                    <div class="relative h-56 w-full overflow-hidden rounded-2xl bg-gradient-to-br from-slate-800 via-slate-900 to-black text-white shadow-2xl transition-transform duration-500 hover:scale-[1.02] group ring-1 ring-white/10">
                        
                        <div class="absolute top-0 right-0 -mr-16 -mt-16 h-48 w-48 rounded-full bg-emerald-500/20 blur-3xl"></div>
                        <div class="absolute bottom-0 left-0 -ml-16 -mb-16 h-48 w-48 rounded-full bg-blue-600/20 blur-3xl"></div>
                        <div class="absolute inset-0 opacity-30 bg-[url('https://grainy-gradients.vercel.app/noise.svg')]"></div>

                        <div class="relative z-10 flex h-full flex-col justify-between p-6">
                            
                            <div class="flex justify-between items-start">
                                <div class="h-9 w-11 rounded-md bg-gradient-to-tr from-yellow-200 to-yellow-500 shadow-inner border border-yellow-600/30 relative overflow-hidden">
                                    <div class="absolute top-1/2 w-full h-[1px] bg-yellow-700/40"></div>
                                    <div class="absolute left-1/2 h-full w-[1px] bg-yellow-700/40"></div>
                                    <div class="absolute top-1/2 left-1/2 w-4 h-3 -translate-x-1/2 -translate-y-1/2 border border-yellow-700/40 rounded-[2px]"></div>
                                </div>
                                
                                <div class="text-right relative z-20">
                                    <p class="text-[9px] font-bold uppercase tracking-widest text-slate-400 mb-0.5">Unit Organization</p>
                                    <p class="text-sm font-bold text-white tracking-wide truncate max-w-[180px] drop-shadow-md uppercase" 
                                       :title="$page.props.auth.user.organization_unit?.name || 'PIMPINAN CABANG'">
                                        {{ $page.props.auth.user.organization_unit?.name || 'PIMPINAN CABANG' }}
                                    </p>
                                </div>
                            </div>

                            <div class="mt-1">
                                <p class="mb-1 text-[10px] text-slate-400 font-mono tracking-widest uppercase">Total Balance</p>
                                <h2 class="text-3xl font-mono font-bold tracking-tight text-white drop-shadow-lg tabular-nums">
                                    {{ formatCurrency(balances.total) }}
                                </h2>
                            </div>

                            <div class="flex justify-between items-end">
                                <div>
                                    <p class="text-[8px] text-slate-500 uppercase mb-0.5">Card Holder</p>
                                    <p class="text-xs font-medium tracking-widest uppercase text-slate-200 truncate max-w-[140px]">
                                        {{ $page.props.auth.user.name }}
                                    </p>
                                </div>
                                <div class="flex items-center gap-4">
                                    <div class="flex flex-col items-end">
                                        <p class="text-[7px] text-slate-500 uppercase">Valid Thru</p>
                                        <p class="text-xs font-mono">12/30</p>
                                    </div>
                                    <div class="flex -space-x-2 opacity-80">
                                        <div class="h-6 w-6 rounded-full bg-red-500/90 mix-blend-screen"></div>
                                        <div class="h-6 w-6 rounded-full bg-orange-500/90 mix-blend-screen"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <Link :href="route('transactions.create')" class="block">
                        <button class="w-full group flex items-center justify-between bg-white border border-slate-200 p-3.5 rounded-xl shadow-sm hover:border-emerald-500 hover:shadow-emerald-100 transition-all duration-300">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 rounded-full bg-slate-900 text-white flex items-center justify-center group-hover:bg-emerald-600 transition-colors shadow-md">
                                    <i class="pi pi-plus text-sm"></i>
                                </div>
                                <div class="text-left">
                                    <p class="text-sm font-bold text-slate-800">Catat Transaksi</p>
                                    <p class="text-[11px] text-slate-500">Input pemasukan atau pengeluaran baru</p>
                                </div>
                            </div>
                            <div class="h-8 w-8 rounded-full bg-slate-50 flex items-center justify-center group-hover:bg-emerald-50 text-slate-400 group-hover:text-emerald-600 transition-colors">
                                <i class="pi pi-arrow-right text-xs"></i>
                            </div>
                        </button>
                    </Link>
                </div>

                <div class="lg:col-span-2 flex flex-col h-full">
                   <div class="flex items-center justify-between mb-4 px-1 shrink-0">
                        <h3 class="font-bold text-slate-800 text-lg">Dompet & Rekening</h3>
                        
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold bg-slate-100 text-slate-600 px-2.5 py-1 rounded-md border border-slate-200 cursor-help" v-tooltip.bottom="'Total akun kas & bank aktif'">
                                {{ balances.details.length }} Akun
                            </span>

                            <Link :href="route('finance-accounts.index')"> 
                                <button class="h-7 w-7 rounded-full bg-white border border-slate-200 text-slate-400 flex items-center justify-center hover:bg-slate-50 hover:text-slate-700 hover:border-slate-300 transition-all shadow-sm" v-tooltip.left="'Kelola Akun (Edit/Hapus)'">
                                    <i class="pi pi-cog text-xs"></i>
                                </button>
                            </Link>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4 auto-rows-fr h-full">
                        
                        <div v-for="(acc, index) in balances.details" :key="index" 
                             class="group relative bg-white p-5 rounded-2xl border border-slate-100 shadow-sm hover:border-emerald-300 hover:shadow-md transition-all duration-300 cursor-default flex flex-col justify-between min-h-[140px]">
                            
                            <div class="flex items-start gap-3">
                                <div class="h-10 w-10 shrink-0 rounded-xl bg-slate-50 flex items-center justify-center text-slate-500 border border-slate-100 group-hover:bg-white group-hover:text-emerald-600 group-hover:border-emerald-100 transition-colors shadow-sm">
                                    <i :class="['pi text-lg', acc.name.toLowerCase().includes('bank') ? 'pi-building-columns' : 'pi-wallet']"></i>
                                </div>
                                <div class="overflow-hidden w-full">
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">
                                        {{ acc.name.toLowerCase().includes('bank') ? 'Bank Account' : 'Cash Account' }}
                                    </p>
                                    <p class="text-sm font-bold text-slate-800 truncate leading-tight" :title="acc.name">
                                        {{ acc.name }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-end justify-between border-t border-slate-50 pt-3 mt-3">
                                <div>
                                    <p class="text-[10px] text-slate-400 mb-0.5 font-medium">Saldo Tersedia</p>
                                    <p class="text-lg font-mono font-bold text-slate-700 group-hover:text-emerald-700 transition-colors tabular-nums tracking-tight">
                                        {{ formatCurrency(acc.balance) }}
                                    </p>
                                </div>
                                <Link :href="route('finance-accounts.show', acc.id)">
                                    <button class="h-8 w-8 rounded-full bg-slate-50 flex items-center justify-center text-slate-400 opacity-0 group-hover:opacity-100 transition-all hover:bg-emerald-100 hover:text-emerald-600 shrink-0" v-tooltip.top="'Lihat Mutasi & Detail'">
                                        <i class="pi pi-list text-xs"></i>
                                    </button>
                                </Link>
                            </div>
                        </div>

                       <Link :href="route('finance-accounts.index')" 
                             class="flex flex-col items-center justify-center p-5 rounded-2xl border-2 border-dashed border-slate-200 text-slate-400 hover:border-emerald-300 hover:text-emerald-600 hover:bg-emerald-50/30 transition-all cursor-pointer min-h-[140px] gap-3 group">
                            
                            <div class="h-12 w-12 rounded-full bg-slate-50 flex items-center justify-center group-hover:bg-emerald-100 transition-colors">
                                <i class="pi pi-sliders-h text-xl group-hover:scale-110 transition-transform"></i>
                            </div>
                            
                            <span class="text-xs font-bold uppercase tracking-wide text-center">Kelola / Tambah Akun</span>
                        </Link>

                    </div>
                </div>

            </div>

            <div class="sticky top-4 z-40 bg-white/80 backdrop-blur-xl p-3 rounded-2xl border border-white/20 shadow-lg ring-1 ring-black/5 flex flex-col md:flex-row justify-between items-center gap-4 transition-all">
                
                <div class="flex items-center gap-3 w-full md:w-auto overflow-x-auto pb-2 md:pb-0 scrollbar-hide">
                    <div class="flex bg-slate-100 p-1 rounded-xl">
                        <button v-for="opt in typeOptions" :key="opt.label"
                                @click="selectedType = opt.value"
                                :class="[
                                    'px-4 py-2 text-xs font-bold rounded-lg transition-all',
                                    selectedType === opt.value 
                                        ? 'bg-white text-slate-800 shadow-sm' 
                                        : 'text-slate-500 hover:text-slate-700'
                                ]">
                            {{ opt.label }}
                        </button>
                    </div>

                    <Select v-if="units.length" v-model="selectedUnit" :options="units" optionLabel="name" optionValue="id" 
                            placeholder="Filter Unit" showClear class="w-48 !rounded-xl !text-sm" />
                </div>

                <div class="w-full md:w-auto relative">
                    <IconField iconPosition="left">
                        <InputIcon class="pi pi-search text-slate-400" />
                        <InputText v-model="search" @input="handleSearch" placeholder="Cari transaksi..." 
                                   class="w-full md:w-64 !rounded-xl !bg-slate-50 !border-transparent focus:!bg-white focus:!border-slate-200 focus:!ring-0 transition-all" />
                    </IconField>
                </div>
            </div>

            <div class="bg-white rounded-[2rem] border border-slate-100 shadow-sm overflow-hidden">
                <DataTable :value="transactions.data" stripedRows class="w-full">
                    <template #empty>
                        <div class="flex flex-col items-center justify-center py-20 text-center">
                            <div class="h-20 w-20 bg-slate-50 rounded-full flex items-center justify-center mb-4">
                                <i class="pi pi-inbox text-3xl text-slate-300"></i>
                            </div>
                            <h3 class="text-lg font-bold text-slate-700">Belum ada data</h3>
                            <p class="text-sm text-slate-400">Transaksi yang Anda catat akan muncul di sini.</p>
                        </div>
                    </template>

                    <Column header="Waktu" style="width: 15%">
                        <template #body="{ data }">
                            <div class="flex flex-col">
                                <span class="font-bold text-slate-700 text-sm">{{ formatDate(data.date) }}</span>
                                <span class="text-xs text-slate-400">{{ data.created_at ? formatTime(data.created_at) : '-' }}</span>
                            </div>
                        </template>
                    </Column>

                    <Column header="Keterangan & Alur Dana" style="width: 40%">
                        <template #body="{ data }">
                            <div class="flex items-start gap-3 py-2">
                                <div :class="[
                                    'h-10 w-10 rounded-xl flex items-center justify-center shrink-0 border',
                                    data.type === 'INCOME' ? 'bg-emerald-50 border-emerald-100 text-emerald-600' : 
                                    data.type === 'EXPENSE' ? 'bg-rose-50 border-rose-100 text-rose-600' : 
                                    'bg-blue-50 border-blue-100 text-blue-600'
                                ]">
                                    <i :class="getIcon(data.type)"></i>
                                </div>

                                <div>
                                    <p class="font-bold text-slate-800 text-sm line-clamp-1">{{ data.description }}</p>
                                    
                                    <div class="flex items-center gap-2 mt-1 text-xs text-slate-500">
                                        <Tag :value="getLabel(data.type)" :severity="getSeverity(data.type)" class="!text-[9px] !px-1.5 !py-0.5" rounded />
                                        
                                        <div v-if="data.type === 'INCOME'" class="flex items-center gap-1">
                                            <span>{{ data.category_coa?.name }}</span>
                                            <i class="pi pi-arrow-right text-[10px] text-slate-300"></i>
                                            <span class="font-bold text-emerald-700">{{ data.cash_coa?.name }}</span>
                                        </div>

                                        <div v-else-if="data.type === 'EXPENSE'" class="flex items-center gap-1">
                                            <span class="font-bold text-rose-700">{{ data.cash_coa?.name }}</span>
                                            <i class="pi pi-arrow-right text-[10px] text-slate-300"></i>
                                            <span>{{ data.category_coa?.name }}</span>
                                        </div>

                                        <div v-else class="flex items-center gap-1">
                                            <span class="font-bold text-slate-700">{{ data.cash_coa?.name }}</span>
                                            <i class="pi pi-arrow-right text-[10px] text-slate-300"></i>
                                            <span class="font-bold text-blue-700">{{ data.category_coa?.name }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </Column>

                    <Column v-if="units.length" header="Unit" style="width: 15%">
                        <template #body="{ data }">
                            <div class="inline-flex items-center gap-1 px-2 py-1 bg-slate-50 border border-slate-200 rounded-lg">
                                <i class="pi pi-building text-[10px] text-slate-400"></i>
                                <span class="text-xs font-semibold text-slate-600">{{ data.organization_unit?.name }}</span>
                            </div>
                        </template>
                    </Column>

                    <Column header="Nominal" style="width: 20%; text-align: right">
                        <template #body="{ data }">
                            <div :class="[
                                'font-mono font-bold text-base tracking-tight',
                                data.type === 'INCOME' ? 'text-emerald-600' : 
                                data.type === 'EXPENSE' ? 'text-rose-600' : 'text-blue-600'
                            ]">
                                {{ data.type === 'EXPENSE' ? '-' : '+' }} {{ formatCurrency(data.amount) }}
                            </div>
                        </template>
                    </Column>

                    <Column style="width: 10%; text-align: center">
                        <template #body="{ data }">
                            <div class="flex justify-center gap-1">
                                <a v-if="data.proof_path" :href="'/storage/'+data.proof_path" target="_blank" 
                                   class="h-8 w-8 flex items-center justify-center rounded-full text-slate-400 hover:bg-slate-100 hover:text-blue-600 transition-colors"
                                   v-tooltip.bottom="'Lihat Bukti'">
                                    <i class="pi pi-image"></i>
                                </a>
                                <button class="h-8 w-8 flex items-center justify-center rounded-full text-slate-400 hover:bg-rose-50 hover:text-rose-600 transition-colors"
                                        @click="handleDelete($event, data.id)"
                                        v-tooltip.bottom="'Batalkan'">
                                    <i class="pi pi-trash"></i>
                                </button>
                            </div>
                        </template>
                    </Column>
                </DataTable>

                <div v-if="transactions.links" class="p-4 border-t border-slate-100 bg-slate-50 flex justify-center">
                    </div>
            </div>

        </div>
        <ConfirmPopup />
    </AppLayout>
</template>

<style scoped>
/* Custom Scrollbar Hide */
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>