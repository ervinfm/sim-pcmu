<script setup>
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import FinanceNavigation from '@/Components/Finance/FinanceNavigation.vue';

// PrimeVue Components
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Select from 'primevue/select';
import InputNumber from 'primevue/inputnumber';
import InputText from 'primevue/inputtext';
import Dialog from 'primevue/dialog';
import Tag from 'primevue/tag';
import ProgressBar from 'primevue/progressbar';
import Textarea from 'primevue/textarea';

const props = defineProps({
    incomes: Array,
    expenses: Array,
    accounts: Array,
    filters: Object,
    summary: Object
});

// --- STATE ---
const yearFilter = ref(props.filters.year);
const showModal = ref(false);
const isEditMode = ref(false);

// Form Handling
const form = useForm({
    id: null,
    coa_id: null,
    fiscal_year: props.filters.year,
    amount: null,
    description: '',
});

// --- ACTIONS ---
const applyFilter = () => {
    router.get(route('finance.budgets.index'), { year: yearFilter.value }, { preserveState: true });
};

const openCreateModal = () => {
    isEditMode.value = false;
    form.reset();
    form.fiscal_year = yearFilter.value;
    showModal.value = true;
};

const editBudget = (item) => {
    isEditMode.value = true;
    form.id = item.id;
    form.coa_id = item.coa_id;
    form.fiscal_year = item.fiscal_year;
    form.amount = Number(item.amount);
    form.description = item.description;
    showModal.value = true;
};

const saveBudget = () => {
    form.post(route('finance.budgets.store'), {
        onSuccess: () => {
            showModal.value = false;
            form.reset();
        }
    });
};

const deleteBudget = (item) => {
    if (confirm('Apakah Anda yakin ingin menghapus anggaran ini?')) {
        router.delete(route('finance.budgets.destroy', item.id));
    }
};

// --- HELPERS ---
const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value);
};

// Warna Progress Bar Dinamis
const getProgressColor = (percent, type) => {
    if (type === 'EXPENSE') {
        // Belanja: Merah jika boros (>90%), Hijau jika hemat
        return percent > 90 ? 'text-red-600 bg-red-600' : 'text-emerald-600 bg-emerald-600';
    } else {
        // Pendapatan: Hijau jika target tercapai, Kuning jika belum
        return percent >= 100 ? 'text-emerald-600 bg-emerald-600' : 'text-amber-500 bg-amber-500';
    }
};

const getRowClass = (data) => {
    return { 'bg-red-50': data.percentage > 100 && data.coa.type === 'EXPENSE' };
};
</script>

<template>
    <Head title="Anggaran (RAPB)" />

    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Anggaran & Realisasi (RAPB)</h2>
        </template>

        <div class="py-4">
            <div class="max-w-7xl mx-auto sm:px-4 lg:px-6 space-y-6">
                
                <FinanceNavigation />

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-gradient-to-br from-emerald-50 to-white p-6 rounded-2xl shadow-sm border border-emerald-100 relative overflow-hidden group hover:shadow-md transition-shadow">
                        <div class="absolute right-0 top-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                            <i class="pi pi-wallet text-8xl text-emerald-600 transform rotate-12"></i>
                        </div>
                        <div class="flex flex-col h-full justify-between relative z-10">
                            <div>
                                <h4 class="text-xs font-bold text-emerald-600 uppercase tracking-widest mb-1">Target Pendapatan</h4>
                                <div class="flex items-baseline gap-2">
                                    <span class="text-2xl font-extrabold text-gray-800">{{ formatCurrency(summary.total_income_real) }}</span>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">
                                    dari target <span class="font-semibold">{{ formatCurrency(summary.total_income_plan) }}</span>
                                </p>
                            </div>
                            <div class="mt-4">
                                <div class="flex justify-between text-[10px] font-bold text-gray-500 mb-1">
                                    <span>Progress</span>
                                    <span>{{ Math.min((summary.total_income_real / summary.total_income_plan) * 100, 100).toFixed(1) }}%</span>
                                </div>
                                <div class="w-full bg-emerald-200 rounded-full h-1.5">
                                    <div class="bg-emerald-500 h-1.5 rounded-full transition-all duration-1000" 
                                         :style="{ width: Math.min((summary.total_income_real / summary.total_income_plan) * 100, 100) + '%' }"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-rose-50 to-white p-6 rounded-2xl shadow-sm border border-rose-100 relative overflow-hidden group hover:shadow-md transition-shadow">
                        <div class="absolute right-0 top-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                            <i class="pi pi-shopping-cart text-8xl text-rose-600 transform -rotate-12"></i>
                        </div>
                        <div class="flex flex-col h-full justify-between relative z-10">
                            <div>
                                <h4 class="text-xs font-bold text-rose-600 uppercase tracking-widest mb-1">Realisasi Belanja</h4>
                                <div class="flex items-baseline gap-2">
                                    <span class="text-2xl font-extrabold text-gray-800">{{ formatCurrency(summary.total_expense_real) }}</span>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">
                                    dari pagu <span class="font-semibold">{{ formatCurrency(summary.total_expense_plan) }}</span>
                                </p>
                            </div>
                            <div class="mt-4">
                                <div class="flex justify-between text-[10px] font-bold text-gray-500 mb-1">
                                    <span>Serapan</span>
                                    <span>{{ Math.min((summary.total_expense_real / summary.total_expense_plan) * 100, 100).toFixed(1) }}%</span>
                                </div>
                                <div class="w-full bg-rose-200 rounded-full h-1.5">
                                    <div class="bg-rose-500 h-1.5 rounded-full transition-all duration-1000" 
                                         :style="{ width: Math.min((summary.total_expense_real / summary.total_expense_plan) * 100, 100) + '%' }"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200 flex flex-col justify-center gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Tahun Anggaran</label>
                            <div class="flex gap-2">
                                <InputNumber v-model="yearFilter" :useGrouping="false" :min="2020" :max="2030" 
                                    showButtons buttonLayout="horizontal" inputClass="text-center font-bold" class="w-full" />
                                <Button icon="pi pi-search" @click="applyFilter" severity="secondary" outlined v-tooltip="'Filter Data'" />
                            </div>
                        </div>
                        <Button label="Buat Anggaran Baru" icon="pi pi-plus" class="w-full" @click="openCreateModal" />
                    </div>
                </div>

                <div class="bg-white shadow-sm rounded-2xl overflow-hidden border border-gray-100">
                    <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                        <div>
                            <h3 class="font-bold text-lg text-gray-800">Anggaran Pendapatan</h3>
                            <p class="text-xs text-gray-500">Estimasi sumber dana masuk</p>
                        </div>
                        <Tag severity="success" value="Revenue" class="px-3" rounded />
                    </div>
                    <DataTable :value="incomes" stripedRows class="p-datatable-sm" rowHover>
                        <template #empty>
                            <div class="flex flex-col items-center justify-center py-8 text-gray-400">
                                <i class="pi pi-folder-open text-4xl mb-2 opacity-50"></i>
                                <span class="text-sm">Belum ada data anggaran pendapatan.</span>
                            </div>
                        </template>

                        <Column field="coa.code" header="Kode" style="width:10%">
                            <template #body="{ data }">
                                <span class="bg-gray-100 text-gray-600 text-xs font-mono px-2 py-1 rounded">{{ data.coa.code }}</span>
                            </template>
                        </Column>
                        <Column field="coa.name" header="Nama Akun">
                             <template #body="{ data }">
                                <div class="flex flex-col">
                                    <span class="font-semibold text-gray-700">{{ data.coa.name }}</span>
                                    <span v-if="data.description" class="text-xs text-gray-400 italic">{{ data.description }}</span>
                                </div>
                            </template>
                        </Column>
                        <Column header="Target (Pagu)" class="text-right">
                             <template #body="{ data }">
                                <span class="text-gray-600">{{ formatCurrency(data.amount) }}</span>
                            </template>
                        </Column>
                        <Column header="Realisasi" class="text-right">
                             <template #body="{ data }">
                                <span class="font-bold text-emerald-600">{{ formatCurrency(data.realization) }}</span>
                            </template>
                        </Column>
                        <Column header="Capaian" style="width: 20%">
                            <template #body="{ data }">
                                <div class="flex items-center gap-2">
                                    <div class="w-full bg-gray-100 rounded-full h-2">
                                        <div class="h-2 rounded-full transition-all"
                                             :class="data.percentage >= 100 ? 'bg-emerald-500' : 'bg-amber-400'"
                                             :style="{ width: Math.min(data.percentage, 100) + '%' }">
                                        </div>
                                    </div>
                                    <span class="text-xs font-bold w-10 text-right">{{ data.percentage }}%</span>
                                </div>
                            </template>
                        </Column>
                         <Column style="width: 8%; text-align: center">
                            <template #body="{ data }">
                                <div class="flex justify-center gap-1">
                                    <Button icon="pi pi-pencil" text rounded size="small" severity="info" @click="editBudget(data)" />
                                    <Button icon="pi pi-trash" text rounded size="small" severity="danger" @click="deleteBudget(data)" />
                                </div>
                            </template>
                        </Column>
                    </DataTable>
                </div>

                <div class="bg-white shadow-sm rounded-2xl overflow-hidden border border-gray-100">
                    <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                        <div>
                            <h3 class="font-bold text-lg text-gray-800">Anggaran Belanja</h3>
                            <p class="text-xs text-gray-500">Rencana pengeluaran dan beban</p>
                        </div>
                        <Tag severity="danger" value="Expenses" class="px-3" rounded />
                    </div>
                    <DataTable :value="expenses" stripedRows class="p-datatable-sm" rowHover :rowClass="getRowClass">
                        <template #empty>
                             <div class="flex flex-col items-center justify-center py-8 text-gray-400">
                                <i class="pi pi-folder-open text-4xl mb-2 opacity-50"></i>
                                <span class="text-sm">Belum ada data anggaran belanja.</span>
                            </div>
                        </template>

                        <Column field="coa.code" header="Kode" style="width:10%">
                            <template #body="{ data }">
                                <span class="bg-gray-100 text-gray-600 text-xs font-mono px-2 py-1 rounded">{{ data.coa.code }}</span>
                            </template>
                        </Column>
                        <Column field="coa.name" header="Nama Akun">
                             <template #body="{ data }">
                                <div class="flex flex-col">
                                    <span class="font-semibold text-gray-700">{{ data.coa.name }}</span>
                                    <span v-if="data.description" class="text-xs text-gray-400 italic">{{ data.description }}</span>
                                </div>
                            </template>
                        </Column>
                        <Column header="Pagu (Limit)" class="text-right">
                             <template #body="{ data }">
                                <span class="text-gray-600">{{ formatCurrency(data.amount) }}</span>
                            </template>
                        </Column>
                        <Column header="Terpakai" class="text-right">
                             <template #body="{ data }">
                                <span class="font-bold text-rose-600">{{ formatCurrency(data.realization) }}</span>
                            </template>
                        </Column>
                        <Column header="Sisa" class="text-right">
                             <template #body="{ data }">
                                <span :class="data.remaining < 0 ? 'text-red-600 font-bold' : 'text-gray-400'">
                                    {{ formatCurrency(data.remaining) }}
                                </span>
                            </template>
                        </Column>
                        <Column header="Serapan" style="width: 20%">
                            <template #body="{ data }">
                                <div class="flex items-center gap-2">
                                    <div class="w-full bg-gray-100 rounded-full h-2">
                                        <div class="h-2 rounded-full transition-all"
                                             :class="data.percentage > 90 ? 'bg-red-500' : 'bg-emerald-500'"
                                             :style="{ width: Math.min(data.percentage, 100) + '%' }">
                                        </div>
                                    </div>
                                    <span class="text-xs font-bold w-10 text-right" :class="data.percentage > 90 ? 'text-red-600' : 'text-gray-600'">{{ data.percentage }}%</span>
                                </div>
                            </template>
                        </Column>
                        <Column style="width: 8%; text-align: center">
                            <template #body="{ data }">
                                <div class="flex justify-center gap-1">
                                    <Button icon="pi pi-pencil" text rounded size="small" severity="info" @click="editBudget(data)" />
                                    <Button icon="pi pi-trash" text rounded size="small" severity="danger" @click="deleteBudget(data)" />
                                </div>
                            </template>
                        </Column>
                    </DataTable>
                </div>

            </div>
        </div>

        <Dialog 
            v-model:visible="showModal" 
            :header="isEditMode ? 'Edit Anggaran' : 'Anggaran Baru'" 
            modal 
            :style="{ width: '450px' }" 
            :breakpoints="{ '960px': '75vw', '641px': '90vw' }"
            class="p-fluid"
        >
            <div class="flex flex-col gap-5 mt-2">
                
                <div class="bg-indigo-50 p-3 rounded-lg border border-indigo-100 flex justify-between items-center">
                    <span class="text-sm text-indigo-700 font-medium">Tahun Anggaran</span>
                    <span class="text-lg font-bold text-indigo-800">{{ form.fiscal_year }}</span>
                </div>

                <div class="flex flex-col gap-2">
                    <label class="font-semibold text-sm text-gray-700">Akun (COA) <span class="text-red-500">*</span></label>
                    <Select v-model="form.coa_id" :options="accounts" optionLabel="name" optionValue="id" 
                            filter placeholder="Pilih Akun..." class="w-full" :disabled="isEditMode">
                        <template #option="slotProps">
                            <div class="flex flex-col">
                                <span class="font-bold text-sm text-gray-800">{{ slotProps.option.code }}</span>
                                <span class="text-xs text-gray-500">{{ slotProps.option.name }}</span>
                            </div>
                        </template>
                        <template #value="slotProps">
                            <div v-if="slotProps.value" class="flex items-center text-sm">
                                <span class="font-bold mr-2 text-indigo-600 bg-indigo-50 px-1.5 rounded">{{ accounts.find(a => a.id === slotProps.value)?.code }}</span>
                                <span class="truncate">{{ accounts.find(a => a.id === slotProps.value)?.name }}</span>
                            </div>
                            <span v-else class="text-gray-400">{{ slotProps.placeholder }}</span>
                        </template>
                    </Select>
                </div>

                <div class="flex flex-col gap-2">
                    <label class="font-semibold text-sm text-gray-700">Nominal Pagu (Rp) <span class="text-red-500">*</span></label>
                    <InputNumber v-model="form.amount" mode="currency" currency="IDR" locale="id-ID" 
                        placeholder="0" class="w-full font-bold" inputClass="text-right" />
                </div>

                <div class="flex flex-col gap-2">
                    <label class="font-semibold text-sm text-gray-700">Keterangan (Opsional)</label>
                    <Textarea v-model="form.description" rows="2" placeholder="Contoh: Honorarium Guru TPA 1 Tahun" class="w-full text-sm" autoResize />
                </div>
            </div>

            <template #footer>
                <div class="flex justify-end gap-2 pt-2">
                    <Button label="Batal" icon="pi pi-times" text severity="secondary" @click="showModal = false" />
                    <Button label="Simpan" icon="pi pi-check" @click="saveBudget" :loading="form.processing" severity="primary" />
                </div>
            </template>
        </Dialog>

    </AppLayout>
</template>