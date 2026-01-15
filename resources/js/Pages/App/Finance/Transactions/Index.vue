<script setup>
import { ref, watch, computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { FilterMatchMode } from '@primevue/core/api';
import FinanceNavigation from '@/Components/Finance/FinanceNavigation.vue';

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

// --- PROPS ---
const props = defineProps({
    transactions: Array,
    units: {
        type: Array,
        default: () => []
    },
    balances: Object,
    errors: Object,
    // [BARU] Prop untuk cek status setup
    hasOpeningBalance: { 
        type: Boolean, 
        default: true // Default true agar tidak merah jika backend belum kirim
    } 
});

// --- NAVIGATION TABS CONFIG ---
// Menu Tab untuk Navigasi Antar Sub-Modul Finance
const navItems = computed(() => [
    { 
        label: 'Transaksi', 
        route: 'finance.transactions.index', 
        active: true, // Halaman ini
        icon: 'pi pi-list' 
    },
    { 
        label: 'Laporan', 
        route: 'finance.reports.index', 
        active: false,
        icon: 'pi pi-chart-bar' 
    },
    { 
        label: 'Saldo Awal', 
        route: 'finance.opening-balances.index', 
        active: false,
        icon: 'pi pi-wallet',
        // Tampilkan badge warning jika belum setup
        showWarning: !props.hasOpeningBalance 
    },
    { 
        label: 'Tutup Buku', 
        route: 'finance.closing-periods.index', 
        active: false,
        icon: 'pi pi-lock' 
    },
    { 
        label: 'Master Akun', 
        route: 'finance.accounts.index', 
        active: false,
        icon: 'pi pi-book' 
    }
]);

// --- STATE FILTER CLIENT-SIDE ---
const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    type: { value: null, matchMode: FilterMatchMode.EQUALS },
    organization_unit_id: { value: null, matchMode: FilterMatchMode.EQUALS },
});

// State untuk UI Filter (Tombol & Dropdown)
const selectedType = ref(null);
const selectedUnit = ref(null);

const typeOptions = [
    { label: 'Semua', value: null },
    { label: 'Pemasukan', value: 'INCOME' },
    { label: 'Pengeluaran', value: 'EXPENSE' },
    { label: 'Transfer', value: 'TRANSFER' },
];

// --- WATCHERS ---
watch(selectedType, (newVal) => {
    filters.value.type.value = newVal;
});
watch(selectedUnit, (newVal) => {
    filters.value.organization_unit_id.value = newVal;
});

// --- HELPER FORMATTING ---
const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value);
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
};

const formatTime = (dateString) => {
    return new Date(dateString).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
};

const getSeverity = (type) => {
    switch (type) {
        case 'INCOME': return 'success';
        case 'EXPENSE': return 'danger';
        case 'TRANSFER': return 'info';
        default: return 'secondary';
    }
};

const getLabel = (type) => {
    switch (type) {
        case 'INCOME': return 'Pemasukan';
        case 'EXPENSE': return 'Pengeluaran';
        case 'TRANSFER': return 'Transfer';
        default: return type;
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

// --- DELETE LOGIC ---
const confirm = useConfirm();
const handleDelete = (event, id) => {
    confirm.require({
        target: event.currentTarget,
        message: 'Hapus transaksi ini? Data jurnal juga akan terhapus.',
        icon: 'pi pi-exclamation-triangle',
        rejectProps: { label: 'Batal', severity: 'secondary', outlined: true },
        acceptProps: { label: 'Hapus', severity: 'danger' },
        accept: () => {
            router.delete(route('transactions.destroy', id), {
                preserveScroll: true,
            });
        }
    });
};

// Helper route check agar tidak error jika route belum dibuat
const safeRoute = (name) => {
    return route().has(name) ? route(name) : '#';
};
</script>

<template>
    <Head title="Transaksi Keuangan" />

    <AppLayout>
        <div class="px-4 md:px-6 space-y-6">
            
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-black text-gray-800 tracking-tight">Manajemen Keuangan</h1>
                    <p class="text-gray-500 text-sm">Pusat kontrol operasional keuangan unit.</p>
                </div>
                <Link :href="route('finance.transactions.create')" 
                      class="bg-emerald-600 text-white px-5 py-2.5 rounded-xl shadow-lg shadow-emerald-200 hover:bg-emerald-700 hover:shadow-none transition-all font-bold text-sm flex items-center gap-2">
                    <i class="pi pi-plus-circle"></i>
                    Catat Transaksi
                </Link>
            </div>

            <FinanceNavigation />

            <div v-if="balances" class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="col-span-1 md:col-span-1 bg-gradient-to-br from-emerald-600 to-teal-700 rounded-2xl p-6 text-white shadow-lg relative overflow-hidden group">
                    <div class="absolute right-0 top-0 w-32 h-32 bg-white opacity-10 rounded-full blur-2xl -mr-10 -mt-10"></div>
                    <p class="text-emerald-100 text-xs font-bold uppercase tracking-widest mb-1">Total Kas & Bank</p>
                    <h2 class="text-3xl font-black tracking-tight">{{ formatCurrency(balances?.total || 0) }}</h2>
                    <div class="mt-4 flex items-center gap-2 text-xs bg-white/20 w-fit px-2 py-1 rounded-lg backdrop-blur-sm">
                        <i class="pi pi-shield"></i>
                        <span>Posisi Keuangan Aman</span>
                    </div>
                </div>

                <div class="col-span-1 md:col-span-2 bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
                    <h4 class="text-sm font-bold text-gray-700 mb-3 flex items-center gap-2">
                        <i class="pi pi-wallet text-gray-400"></i> Rincian Dompet
                    </h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                        <div v-for="acc in balances?.details" :key="acc.code" 
                             class="p-3 rounded-xl bg-gray-50 border border-gray-100 hover:border-emerald-200 transition-colors">
                            <p class="text-xs text-gray-500 truncate">{{ acc.name }}</p>
                            <p class="font-bold text-gray-800">{{ formatCurrency(acc.balance) }}</p>
                        </div>
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

                    <Select v-if="units && units.length" 
                            v-model="selectedUnit" 
                            :options="units" 
                            optionLabel="name" 
                            optionValue="id" 
                            placeholder="Filter Unit" 
                            showClear 
                            class="w-48 !rounded-xl !text-sm" />
                </div>

                <div class="w-full md:w-auto relative">
                    <IconField iconPosition="left">
                        <InputIcon class="pi pi-search text-slate-400" />
                        <InputText v-model="filters['global'].value" 
                                   placeholder="Cari transaksi..." 
                                   class="w-full md:w-64 !rounded-xl !bg-slate-50 !border-transparent focus:!bg-white focus:!border-slate-200 focus:!ring-0 transition-all" />
                    </IconField>
                </div>
            </div>

            <div class="bg-white rounded-[2rem] border border-slate-100 shadow-sm overflow-hidden">
                
                <DataTable 
                    :value="transactions" 
                    :paginator="true" 
                    :rows="10"
                    :rowsPerPageOptions="[10, 20, 50]"
                    v-model:filters="filters"
                    :globalFilterFields="['description', 'amount', 'category_coa.name', 'cash_coa.name', 'type']"
                    stripedRows 
                    class="w-full"
                    removableSort
                >
                    <template #empty>
                        <div class="flex flex-col items-center justify-center py-20 text-center">
                            <div class="h-20 w-20 bg-slate-50 rounded-full flex items-center justify-center mb-4">
                                <i class="pi pi-inbox text-3xl text-slate-300"></i>
                            </div>
                            <h3 class="text-lg font-bold text-slate-700">Data tidak ditemukan</h3>
                            <p class="text-sm text-slate-400">Coba ubah filter atau kata kunci pencarian Anda.</p>
                        </div>
                    </template>

                    <Column field="date" header="Waktu" sortable style="width: 15%">
                        <template #body="{ data }">
                            <div class="flex flex-col">
                                <span class="font-bold text-slate-700 text-sm">{{ formatDate(data.date) }}</span>
                                <span class="text-xs text-slate-400">{{ data.created_at ? formatTime(data.created_at) : '-' }}</span>
                            </div>
                        </template>
                    </Column>

                    <Column field="description" header="Keterangan & Alur Dana" sortable style="width: 40%">
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

                    <Column v-if="units && units.length" field="organization_unit.name" header="Unit" sortable style="width: 15%">
                        <template #body="{ data }">
                            <div class="inline-flex items-center gap-1 px-2 py-1 bg-slate-50 border border-slate-200 rounded-lg">
                                <i class="pi pi-building text-[10px] text-slate-400"></i>
                                <span class="text-xs font-semibold text-slate-600">{{ data.organization_unit?.name }}</span>
                            </div>
                        </template>
                    </Column>

                    <Column field="amount" header="Nominal" sortable style="width: 20%; text-align: right">
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
            </div>

        </div>
        <ConfirmPopup />
    </AppLayout>
</template>

<style scoped>
/* Hapus scrollbar di filter tipe agar rapi */
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}

/* Custom Paginator PrimeVue agar menyatu dengan Tema */
:deep(.p-paginator) {
    background: transparent;
    border-top: 1px solid #f1f5f9;
    padding: 1rem;
    font-size: 0.875rem;
}
:deep(.p-paginator-current) {
    color: #94a3b8;
}
</style>