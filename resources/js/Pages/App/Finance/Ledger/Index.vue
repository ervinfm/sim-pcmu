<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import FinanceNavigation from '@/Components/Finance/FinanceNavigation.vue';
import dayjs from 'dayjs';
import 'dayjs/locale/id';

// PrimeVue Components
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Select from 'primevue/select';
import Tag from 'primevue/tag';
import InputText from 'primevue/inputtext'; // Menggunakan InputText type="date"

// Props dari Controller
const props = defineProps({
    accounts: Array,       // List Akun untuk Dropdown
    ledger: Array,         // Data Transaksi
    openingBalance: Number,
    selectedAccount: Object,
    filters: Object,       // State filter dari server
});

// State Filter (Reaktif terhadap input user)
const filterForm = ref({
    coa_id: props.filters.coa_id || null,
    start_date: props.filters.start_date || dayjs().startOf('month').format('YYYY-MM-DD'),
    end_date: props.filters.end_date || dayjs().format('YYYY-MM-DD'),
    fund_type: props.filters.fund_type || null,
});

// Opsi Fund Type
const fundTypes = [
    { label: 'Semua Dana', value: null },
    { label: 'Dana Bebas (Operasional)', value: 'UNRESTRICTED' },
    { label: 'Dana Terikat', value: 'RESTRICTED' },
    { label: 'Dana Abadi', value: 'ENDOWMENT' }
];

// Helper Formatters
const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value);
};

const formatDate = (date) => {
    return dayjs(date).locale('id').format('DD MMM YYYY');
};

// --- LOGIC UTAMA: COMPUTED RUNNING BALANCE ---
// Menggabungkan Saldo Awal sebagai baris pertama + Menghitung saldo berjalan
const ledgerData = computed(() => {
    if (!props.selectedAccount) return [];

    let currentBalance = Number(props.openingBalance);
    const isDebitNormal = ['ASSET', 'EXPENSE'].includes(props.selectedAccount.type);

    // 1. Proses Data Transaksi
    const processedTransactions = props.ledger.map((item) => {
        const debit = Number(item.debit);
        const credit = Number(item.credit);

        // Rumus Saldo Berjalan
        if (isDebitNormal) {
            currentBalance = currentBalance + debit - credit;
        } else {
            currentBalance = currentBalance + credit - debit;
        }

        return {
            id: item.id,
            date: item.journal.transaction_date,
            journal_number: item.journal.journal_number,
            description: item.journal.description,
            fund_type: item.fund_type,
            debit: debit,
            credit: credit,
            balance: currentBalance,
            is_row: true // Penanda baris biasa
        };
    });

    // 2. Tambahkan Baris "Saldo Awal" di paling atas array
    const openingRow = {
        id: 'opening',
        date: props.filters.start_date,
        journal_number: '-',
        description: 'SALDO AWAL (OPENING BALANCE)',
        fund_type: null,
        debit: 0,
        credit: 0,
        balance: Number(props.openingBalance),
        is_opening: true // Penanda styling khusus
    };

    return [openingRow, ...processedTransactions];
});

// Hitung Total Mutasi (Footer)
const totals = computed(() => {
    const debit = props.ledger.reduce((acc, item) => acc + Number(item.debit), 0);
    const credit = props.ledger.reduce((acc, item) => acc + Number(item.credit), 0);
    return { debit, credit };
});

// Actions
const applyFilter = () => {
    router.get(route('finance.ledgers.index'), filterForm.value, {
        preserveState: true,
        preserveScroll: true,
        only: ['ledger', 'openingBalance', 'selectedAccount', 'filters'],
    });
};

const resetFilter = () => {
    filterForm.value = {
        coa_id: null,
        start_date: dayjs().startOf('month').format('YYYY-MM-DD'),
        end_date: dayjs().format('YYYY-MM-DD'),
        fund_type: null
    };
    applyFilter();
};

const printPdf = () => {
    window.print();
};
</script>

<template>
    <Head title="Buku Besar" />

    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Buku Besar</h2>
        </template>

        <div class="py-2">
            <div class="max-w-7xl mx-auto sm:px-2 lg:px-4 space-y-6">
                
                <FinanceNavigation />

                <div class="bg-white shadow-sm sm:rounded-lg p-6 border border-gray-100">
                    <div class="flex flex-col md:flex-row gap-4 items-end">
                        
                        <div class="w-full md:w-1/3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Akun (COA)</label>
                            <Select v-model="filterForm.coa_id" 
                                    :options="accounts" optionLabel="name" optionValue="id" 
                                    filter placeholder="Pilih Akun..." class="w-full" showClear>
                                <template #option="slotProps">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-sm">{{ slotProps.option.code }}</span>
                                        <span class="text-xs text-gray-500">{{ slotProps.option.name }}</span>
                                    </div>
                                </template>
                                <template #value="slotProps">
                                    <div v-if="slotProps.value" class="flex items-center">
                                        {{ accounts.find(a => a.id === slotProps.value)?.code }} - 
                                        {{ accounts.find(a => a.id === slotProps.value)?.name }}
                                    </div>
                                    <span v-else>{{ slotProps.placeholder }}</span>
                                </template>
                            </Select>
                        </div>

                        <div class="w-full md:w-1/4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Periode Awal</label>
                            <InputText type="date" v-model="filterForm.start_date" class="w-full" />
                        </div>
                        <div class="w-full md:w-1/4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Periode Akhir</label>
                            <InputText type="date" v-model="filterForm.end_date" class="w-full" />
                        </div>

                        <div class="w-full md:w-auto flex gap-2">
                            <Button icon="pi pi-filter" label="Tampilkan" @click="applyFilter" />
                            <Button icon="pi pi-refresh" severity="secondary" @click="resetFilter" v-tooltip="'Reset'" />
                        </div>
                    </div>
                    
                    <div class="mt-4 pt-4 border-t border-gray-100 flex items-center gap-4">
                        <div class="w-full md:w-1/4">
                             <Select v-model="filterForm.fund_type" 
                                    :options="fundTypes" optionLabel="label" optionValue="value" 
                                    placeholder="Filter Jenis Dana" class="w-full p-inputtext-sm" showClear />
                        </div>
                        <span class="text-xs text-gray-400 italic">
                            *Pilih akun dan rentang tanggal untuk melihat mutasi.
                        </span>
                    </div>
                </div>

                <div v-if="selectedAccount" class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                    
                    <div class="p-6 border-b border-gray-100 flex flex-col md:flex-row justify-between md:items-center bg-gray-50/50">
                        <div>
                            <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                                <span class="bg-indigo-100 text-indigo-700 px-2 py-1 rounded text-sm">{{ selectedAccount.code }}</span>
                                {{ selectedAccount.name }}
                            </h3>
                            <div class="text-sm text-gray-500 mt-1 flex gap-3">
                                <span><i class="pi pi-calendar mr-1"></i> {{ formatDate(filterForm.start_date) }} s.d {{ formatDate(filterForm.end_date) }}</span>
                                <Tag :value="selectedAccount.type" severity="info" class="text-[10px]" />
                            </div>
                        </div>
                        <div class="mt-4 md:mt-0">
                            <Button label="Cetak / PDF" icon="pi pi-print" severity="secondary" outlined size="small" @click="printPdf" />
                        </div>
                    </div>

                    <DataTable :value="ledgerData" stripedRows class="p-datatable-sm" tableStyle="min-width: 50rem">
                        
                        <template #empty>
                            <div class="text-center py-8 text-gray-500">Data tidak ditemukan untuk periode ini.</div>
                        </template>

                        <Column header="Tanggal" style="width: 12%">
                            <template #body="{ data }">
                                <span :class="{'font-bold text-gray-800': data.is_opening, 'text-gray-600': !data.is_opening}">
                                    {{ formatDate(data.date) }}
                                </span>
                            </template>
                        </Column>

                        <Column field="journal_number" header="No. Bukti" style="width: 15%">
                            <template #body="{ data }">
                                <span v-if="!data.is_opening" class="text-indigo-600 font-mono text-xs cursor-pointer hover:underline">
                                    {{ data.journal_number }}
                                </span>
                                <span v-else>-</span>
                            </template>
                        </Column>

                        <Column header="Keterangan">
                            <template #body="{ data }">
                                <div class="flex flex-col">
                                    <span :class="{'font-bold uppercase tracking-wide text-gray-500': data.is_opening, 'text-gray-800': !data.is_opening}">
                                        {{ data.description || '-' }}
                                    </span>
                                    <div v-if="data.fund_type && data.fund_type !== 'UNRESTRICTED'" class="mt-1">
                                         <Tag :value="data.fund_type === 'RESTRICTED' ? 'Terikat' : 'Abadi'" 
                                              severity="warning" class="text-[9px] py-0" />
                                    </div>
                                </div>
                            </template>
                        </Column>

                        <Column header="Debit" class="text-right" style="width: 13%">
                            <template #body="{ data }">
                                <span v-if="data.debit > 0" class="text-gray-700">
                                    {{ formatCurrency(data.debit) }}
                                </span>
                                <span v-else class="text-gray-300">-</span>
                            </template>
                        </Column>

                        <Column header="Kredit" class="text-right" style="width: 13%">
                            <template #body="{ data }">
                                <span v-if="data.credit > 0" class="text-gray-700">
                                    {{ formatCurrency(data.credit) }}
                                </span>
                                <span v-else class="text-gray-300">-</span>
                            </template>
                        </Column>

                        <Column header="Saldo" class="text-right bg-gray-50/80" style="width: 15%">
                            <template #body="{ data }">
                                <span class="font-bold" :class="data.balance < 0 ? 'text-red-600' : 'text-gray-800'">
                                    {{ formatCurrency(data.balance) }}
                                </span>
                            </template>
                        </Column>

                        <template #footer>
                            <div class="flex justify-end gap-10 pr-4 text-sm font-bold text-gray-700">
                                <span>TOTAL MUTASI:</span>
                                <span class="text-emerald-600">Debit: {{ formatCurrency(totals.debit) }}</span>
                                <span class="text-rose-600">Kredit: {{ formatCurrency(totals.credit) }}</span>
                            </div>
                        </template>

                    </DataTable>
                </div>

                <div v-else class="bg-white border-2 border-dashed border-gray-300 rounded-xl p-12 text-center opacity-75">
                    <div class="bg-gray-50 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="pi pi-book text-2xl text-gray-400"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900">Mulai Dengan Memilih Akun</h3>
                    <p class="text-gray-500 max-w-sm mx-auto mt-1">
                        Silakan pilih akun perkiraan (COA) dan rentang tanggal pada panel filter di atas untuk menampilkan detail buku besar.
                    </p>
                </div>

            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Custom Style untuk Header Table agar mirip Transaction/Index.vue */
:deep(.p-datatable .p-datatable-thead > tr > th) {
    background-color: #f8fafc;
    color: #475569;
    font-size: 0.875rem;
    padding-top: 1rem;
    padding-bottom: 1rem;
}

:deep(.p-datatable .p-datatable-tbody > tr > td) {
    padding-top: 0.75rem;
    padding-bottom: 0.75rem;
    font-size: 0.9rem;
}

/* Style khusus untuk baris Saldo Awal */
:deep(.p-datatable .p-datatable-tbody > tr.bg-yellow-50) {
    background-color: #fefce8 !important;
}
</style>