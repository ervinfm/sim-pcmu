<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import FinanceNavigation from '@/Components/Finance/FinanceNavigation.vue';

// PrimeVue Components
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import Select from 'primevue/select'; // PrimeVue v4 uses Select
import Tag from 'primevue/tag';
import ConfirmPopup from 'primevue/confirmpopup';
import { useConfirm } from "primevue/useconfirm";
import { useToast } from "primevue/usetoast";
import { FilterMatchMode } from '@primevue/core/api'; // Penting untuk Client Side Filter

// Props dari Controller
const props = defineProps({
    transactions: Array,  // Sekarang Array, bukan Object Paginator
    balances: Object,
    units: Array,
});

const confirm = useConfirm();
const toast = useToast();

// --- CLIENT SIDE FILTER CONFIG ---
const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    type: { value: null, matchMode: FilterMatchMode.EQUALS },
    fund_type: { value: null, matchMode: FilterMatchMode.EQUALS },
    'organizationUnit.id': { value: null, matchMode: FilterMatchMode.EQUALS }, // Filter Nested
});

// Opsi Dropdown
const transactionTypes = [
    { label: 'Semua Tipe', value: null },
    { label: 'Pemasukan', value: 'INCOME' },
    { label: 'Pengeluaran', value: 'EXPENSE' },
    { label: 'Transfer', value: 'TRANSFER' }
];

const fundTypes = [
    { label: 'Semua Dana', value: null },
    { label: 'Dana Bebas', value: 'UNRESTRICTED' },
    { label: 'Dana Terikat', value: 'RESTRICTED' },
    { label: 'Dana Abadi', value: 'ENDOWMENT' }
];

// Helper Formatter
const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value);
};

const formatDate = (dateString) => {
    const options = { day: 'numeric', month: 'short', year: 'numeric' };
    return new Date(dateString).toLocaleDateString('id-ID', options);
};

// --- ACTIONS ---
const handleDelete = (event, id) => {
    confirm.require({
        target: event.currentTarget,
        message: 'Yakin ingin membatalkan transaksi ini? Jurnal akuntansi akan dihapus.',
        icon: 'pi pi-exclamation-triangle',
        acceptClass: 'p-button-danger',
        accept: () => {
            router.delete(route('finance.transactions.destroy', id), {
                preserveScroll: true,
                onSuccess: () => toast.add({ severity: 'success', summary: 'Berhasil', detail: 'Transaksi dihapus', life: 3000 }),
                onError: () => toast.add({ severity: 'error', summary: 'Gagal', detail: 'Gagal menghapus (Cek Tutup Buku)', life: 3000 })
            });
        }
    });
};
// Logika Status Saldo
const balanceStatus = computed(() => {
    const total = props.balances?.total || 0; // Ambil nilai total

    if (total < 0) {
        return {
            bg: 'bg-gradient-to-br from-red-500 to-rose-600', // Merah Solid
            text: 'text-white',
            subText: 'text-red-100',
            icon: 'pi-exclamation-triangle', // Icon Peringatan
            iconColor: 'text-rose-900',
            badgeLabel: 'DEFISIT',
            badgeClass: 'bg-red-800/30 text-white border-red-400/30',
            message: 'Segera lakukan penyeimbangan kas!'
        };
    } else if (total < 10000000) {
        return {
            bg: 'bg-gradient-to-br from-amber-400 to-orange-500', // Kuning/Oranye
            text: 'text-white',
            subText: 'text-amber-100',
            icon: 'pi-exclamation-circle', // Icon Waspada
            iconColor: 'text-amber-800',
            badgeLabel: 'MENIPIS',
            badgeClass: 'bg-amber-800/30 text-white border-amber-400/30',
            message: 'Perhatikan pengeluaran operasional.'
        };
    } else {
        return {
            bg: 'bg-gradient-to-br from-emerald-500 to-teal-600', // Hijau Emerald
            text: 'text-white',
            subText: 'text-emerald-100',
            icon: 'pi-wallet', // Icon Dompet
            iconColor: 'text-emerald-900',
            badgeLabel: 'AMAN',
            badgeClass: 'bg-emerald-800/30 text-white border-emerald-400/30',
            message: 'Kondisi kas terpantau stabil.'
        };
    }
});
</script>

<template>
    <Head title="Transaksi Keuangan" />

    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Keuangan</h2>
        </template>

        <div class="py-2">
            <div class="max-w-7xl mx-auto sm:px-2 lg:px-4 space-y-6">
                
                <FinanceNavigation />

               <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="rounded-2xl p-6 shadow-lg relative overflow-hidden transition-all duration-300 hover:shadow-xl group border border-white/20"
                        :class="balanceStatus.bg">
                        
                        <i class="pi absolute -right-6 -bottom-8 opacity-20 transform -rotate-12 group-hover:scale-110 transition-transform duration-500"
                        :class="[balanceStatus.icon, balanceStatus.iconColor]"
                        style="font-size: 10rem;"></i>

                        <div class="relative z-10 flex flex-col h-full justify-between">
                            
                            <div class="flex justify-between items-start mb-4">
                                <p class="font-bold text-xs uppercase tracking-widest opacity-90" 
                                :class="balanceStatus.subText">
                                    Total Saldo Kas & Bank
                                </p>
                                
                                <div class="px-3 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-widest backdrop-blur-md border shadow-sm flex items-center gap-1.5"
                                    :class="balanceStatus.badgeClass">
                                    <span class="relative flex h-2 w-2">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75 bg-white"></span>
                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-white"></span>
                                    </span>
                                    {{ balanceStatus.badgeLabel }}
                                </div>
                            </div>

                            <div>
                                <h3 class="text-3xl md:text-4xl font-black tracking-tight text-white drop-shadow-sm">
                                    {{ formatCurrency(balances.total) }}
                                </h3>
                                
                                <p class="text-xs mt-3 font-medium flex items-center gap-1.5 opacity-90" 
                                :class="balanceStatus.subText">
                                    <i class="pi pi-info-circle text-sm"></i>
                                    {{ balanceStatus.message }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="md:col-span-2 grid grid-cols-2 sm:grid-cols-3 gap-4 h-fit">

                        <div v-for="acc in balances.details.slice(0, 2)" :key="acc.id" 
                            class="bg-white border border-gray-100 rounded-xl p-4 shadow-sm hover:shadow-md transition-all duration-300 group flex flex-col justify-between relative overflow-hidden h-[130px]">
                            
                            <div class="absolute top-0 left-0 w-full h-1 transition-colors duration-300"
                                :class="acc.balance < 0 ? 'bg-red-400' : 'bg-gray-100 group-hover:bg-blue-400'"></div>

                            <div class="flex justify-between items-start">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center text-sm transition-colors"
                                    :class="acc.balance < 0 ? 'bg-red-50 text-red-500' : 'bg-blue-50 text-blue-500 group-hover:bg-blue-500 group-hover:text-white'">
                                    <i class="pi pi-wallet"></i>
                                </div>
                                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-gray-50 text-gray-500 border border-gray-100">
                                    {{ acc.code }}
                                </span>
                            </div>

                            <div class="mt-2">
                                <h5 class="text-lg font-bold tracking-tight" 
                                    :class="acc.balance < 0 ? 'text-red-600' : 'text-gray-800'">
                                    {{ formatCurrency(acc.balance) }}
                                </h5>
                                <p class="text-xs text-gray-500 font-medium truncate mt-0.5" :title="acc.name">
                                    {{ acc.name }}
                                </p>
                            </div>
                            
                            <div class="absolute inset-0 bg-gradient-to-tr from-white via-transparent to-blue-50 opacity-0 group-hover:opacity-40 pointer-events-none transition-opacity"></div>
                        </div>

                        <Link :href="route('finance.accounts.index')" 
                            class="flex flex-col items-center justify-center h-[130px] rounded-xl border-2 border-dashed border-gray-300 bg-gray-50 text-gray-400 hover:border-blue-400 hover:bg-blue-50 hover:text-blue-600 transition-all duration-300 cursor-pointer group gap-2">
                            
                            <div class="w-10 h-10 rounded-full bg-white border border-gray-200 shadow-sm flex items-center justify-center group-hover:scale-110 transition-transform">
                                <i class="pi pi-arrow-right text-lg"></i>
                            </div>
                            
                            <div class="text-center">
                                <span class="block text-xs font-bold uppercase tracking-wider">Lihat Semua</span>
                                <span class="text-[10px] opacity-70">{{ balances.details.length }} Akun Total</span>
                            </div>
                        </Link>

                    </div>
                </div>

                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    
                    <DataTable :value="props.transactions" 
                               v-model:filters="filters"
                               paginator :rows="10" :rowsPerPageOptions="[10, 20, 50, 100]"
                               stripedRows class="p-datatable-sm" 
                               :globalFilterFields="['description', 'cash_coa.name', 'category_coa.name', 'amount']"
                               sortMode="multiple" removableSort
                               tableStyle="min-width: 50rem">
                        
                        <template #header>
                            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                                <div class="flex flex-col md:flex-row gap-2 w-full md:w-auto">
                                    <IconField iconPosition="left">
                                        <InputIcon class="pi pi-search" />
                                        <InputText v-model="filters['global'].value" placeholder="Cari data..." class="w-full md:w-64" />
                                    </IconField>
                                    
                                    <Select v-if="units.length > 0" 
                                            v-model="filters['organizationUnit.id'].value" 
                                            :options="units" optionLabel="name" optionValue="id" 
                                            placeholder="Semua Unit" class="w-full md:w-48" showClear />
                                </div>

                                <div class="flex gap-2 w-full md:w-auto overflow-x-auto pb-1">
                                    <Select v-model="filters['type'].value" 
                                            :options="transactionTypes" optionLabel="label" optionValue="value" 
                                            placeholder="Semua Tipe" class="w-full md:w-40" showClear />
                                    
                                    <Select v-model="filters['fund_type'].value" 
                                            :options="fundTypes" optionLabel="label" optionValue="value" 
                                            placeholder="Jenis Dana" class="w-full md:w-40" showClear />

                                    <Link :href="route('finance.transactions.create')">
                                        <Button label="Transaksi Baru" icon="pi pi-plus" severity="contrast" />
                                    </Link>
                                </div>
                            </div>
                        </template>

                        <template #empty>
                            <div class="text-center py-8 text-gray-500">Tidak ada data ditemukan.</div>
                        </template>

                        <Column field="date" header="Tanggal" sortable style="width: 12%">
                            <template #body="{ data }">
                                <span class="font-medium text-gray-700">{{ formatDate(data.date) }}</span>
                            </template>
                        </Column>

                        <Column field="description" header="Keterangan" sortable>
                            <template #body="{ data }">
                                <div class="flex flex-col">
                                    <span class="font-semibold text-gray-800 flex items-center gap-2">
                                        {{ data.description }}
                                        <i v-if="data.is_opening_balance" class="pi pi-verified text-blue-500" v-tooltip="'Saldo Awal'"></i>
                                    </span>
                                    <span class="text-xs text-gray-500 mt-1">
                                        {{ data.cash_coa?.name }} 
                                        <i class="pi pi-arrow-right text-[10px] mx-1"></i> 
                                        {{ data.category_coa?.name ?? 'Transfer' }}
                                    </span>
                                </div>
                            </template>
                        </Column>

                        <Column field="fund_type" header="Dana" sortable style="width: 10%">
                            <template #body="{ data }">
                                <Tag :value="data.fund_type === 'UNRESTRICTED' ? 'Bebas' : (data.fund_type === 'RESTRICTED' ? 'Terikat' : 'Abadi')" 
                                     :severity="data.fund_type === 'UNRESTRICTED' ? 'success' : (data.fund_type === 'RESTRICTED' ? 'info' : 'warning')" 
                                     class="text-[10px] uppercase" />
                            </template>
                        </Column>

                        <Column field="amount" header="Nominal" sortable class="text-right" style="width: 15%">
                            <template #body="{ data }">
                                <span :class="{
                                    'text-emerald-600 font-bold': data.type === 'INCOME',
                                    'text-rose-600 font-bold': data.type === 'EXPENSE',
                                    'text-blue-600 font-bold': data.type === 'TRANSFER'
                                }">
                                    {{ data.type === 'EXPENSE' ? '-' : '+' }} {{ formatCurrency(data.amount) }}
                                </span>
                            </template>
                        </Column>

                        <Column style="width: 10%; text-align: center" header="Aksi">
                            <template #body="{ data }">
                                <div class="flex justify-end gap-2">
                                    <a v-if="data.proof_path" :href="'/storage/' + data.proof_path" target="_blank" 
                                       class="p-2 text-gray-400 hover:text-blue-600 transition" v-tooltip.top="'Lihat Bukti'">
                                        <i class="pi pi-image"></i>
                                    </a>
                                    <button @click="handleDelete($event, data.id)" 
                                            class="p-2 text-gray-400 hover:text-rose-600 transition" 
                                            v-tooltip.top="'Batalkan'">
                                        <i class="pi pi-trash"></i>
                                    </button>
                                </div>
                            </template>
                        </Column>
                    </DataTable>

                </div>
            </div>
        </div>
        
        <ConfirmPopup />
    </AppLayout>
</template>

<style scoped>
/* Styling Tambahan */
:deep(.p-datatable .p-datatable-thead > tr > th) {
    background-color: #f8fafc;
    color: #475569;
    font-size: 0.875rem;
}
:deep(.p-inputtext) {
    padding-top: 0.5rem;
    padding-bottom: 0.5rem;
}
</style>