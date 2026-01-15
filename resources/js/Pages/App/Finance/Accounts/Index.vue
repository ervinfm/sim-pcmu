<script setup>
import { ref, watch } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import FinanceNavigation from '@/Components/Finance/FinanceNavigation.vue';

// Components
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import Tag from 'primevue/tag';
import Dialog from 'primevue/dialog';
import Select from 'primevue/select';
import Checkbox from 'primevue/checkbox';
import ConfirmPopup from 'primevue/confirmpopup';
import { useConfirm } from "primevue/useconfirm";

const props = defineProps({
    accounts: Object,
    filters: Object
});

const search = ref(props.filters.search || '');
const confirm = useConfirm();

// --- SEARCH LOGIC ---
let timeout = null;
const handleSearch = () => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        router.get(route('finance.accounts.index'), { search: search.value }, { preserveState: true, replace: true });
    }, 300);
};

// --- MODAL STATE ---
const modalVisible = ref(false);
const isEdit = ref(false);
const form = useForm({
    id: null,
    name: '',
    code: '',
    type: 'ASSET',
    is_cash: false
});

const accountTypes = [
    { label: 'Harta / Aset', value: 'ASSET' },
    { label: 'Kewajiban / Utang', value: 'LIABILITY' },
    { label: 'Modal / Ekuitas', value: 'EQUITY' },
    { label: 'Pendapatan', value: 'REVENUE' },
    { label: 'Beban / Pengeluaran', value: 'EXPENSE' }
];

// --- ACTIONS ---
const openCreate = () => {
    isEdit.value = false;
    form.reset();
    // Auto generate code suggestion (simple logic)
    form.code = '';
    modalVisible.value = true;
};

const openEdit = (account) => {
    // Proteksi Frontend: Jangan edit akun global jika bukan super admin
    // (Logic ini sebaiknya sinkron dengan backend, tapi visual disable cukup membantu)
    isEdit.value = true;
    form.id = account.id;
    form.name = account.name;
    form.code = account.code;
    form.type = account.type;
    form.is_cash = Boolean(account.is_cash);
    modalVisible.value = true;
};

const submitForm = () => {
    if (isEdit.value) {
        form.put(route('finance.accounts.update', form.id), {
            onSuccess: () => modalVisible.value = false
        });
    } else {
        form.post(route('finance.accounts.store'), {
            onSuccess: () => modalVisible.value = false
        });
    }
};

const handleDelete = (event, id) => {
    confirm.require({
        target: event.currentTarget,
        message: 'Hapus akun ini? Data tidak bisa dikembalikan.',
        icon: 'pi pi-exclamation-triangle',
        acceptClass: 'p-button-danger',
        accept: () => router.delete(route('finance.accounts.destroy', id))
    });
};

// Helper Warna Tag
const getTypeSeverity = (type) => {
    const map = {
        'ASSET': 'success',
        'LIABILITY': 'warning',
        'EQUITY': 'info',
        'REVENUE': 'success', // atau warna lain
        'EXPENSE': 'danger'
    };
    return map[type] || 'secondary';
};
</script>

<template>
    <Head title="Manajemen Akun Rekening" />

    <AppLayout>
        <div class="px-4 md:px-6 space-y-6">
            
            <!-- <div class="flex flex-col md:flex-row justify-between items-center gap-4 bg-white p-5 rounded-2xl border border-gray-200 shadow-sm">
                <div>
                    <div class="flex items-center gap-3">
                        <Link :href="route('finance.transactions.index')">
                            <Button icon="pi pi-arrow-left" text rounded severity="secondary" />
                        </Link>
                        <div>
                            <h1 class="text-xl font-black text-gray-800 tracking-tight">Chart of Accounts</h1>
                            <p class="text-xs text-gray-500">Kelola daftar akun, rekening bank, dan kategori pos keuangan.</p>
                        </div>
                    </div>
                </div>

                <div class="flex gap-3 w-full md:w-auto">
                    <IconField iconPosition="left" class="w-full md:w-64">
                        <InputIcon class="pi pi-search text-gray-400" />
                        <InputText v-model="search" @input="handleSearch" placeholder="Cari kode atau nama..." class="w-full !rounded-xl" />
                    </IconField>
                    <Button label="Akun Baru" icon="pi pi-plus" @click="openCreate" class="!bg-slate-900 !border-slate-900 !rounded-xl font-bold shadow-lg" />
                </div>
            </div> -->

            
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-black text-gray-800 tracking-tight">Master Data Akun (COA)</h1>
                    <p class="text-gray-500 text-sm">Kelola hierarki akun aset, pendapatan, dan pengeluaran.</p>
                </div>
                
                <button @click="openCreateModal" 
                class="bg-emerald-600 text-white px-5 py-2.5 rounded-xl shadow-lg shadow-emerald-200 hover:bg-emerald-700 hover:shadow-none transition-all font-bold text-sm flex items-center gap-2">
                <i class="pi pi-plus-circle"></i>
                Tambah Akun
                </button>
            </div>
        
            <FinanceNavigation />
            
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
                <DataTable :value="accounts.data" stripedRows class="w-full">
                    <template #empty>
                        <div class="text-center p-8 text-gray-400">Belum ada data akun.</div>
                    </template>

                    <Column field="code" header="Kode" style="width: 15%">
                        <template #body="{ data }">
                            <span class="font-mono font-bold text-slate-700 bg-slate-100 px-2 py-1 rounded text-xs">{{ data.code }}</span>
                        </template>
                    </Column>

                    <Column field="name" header="Nama Akun" style="width: 40%">
                        <template #body="{ data }">
                            <div class="flex flex-col">
                                <span class="font-bold text-slate-800">{{ data.name }}</span>
                                <span v-if="!data.organization_unit_id" class="text-[10px] text-blue-500 font-bold uppercase tracking-wider flex items-center gap-1">
                                    <i class="pi pi-verified"></i> Standar Pusat
                                </span>
                                <span v-else class="text-[10px] text-slate-400">Akun Lokal</span>
                            </div>
                        </template>
                    </Column>

                    <Column field="type" header="Tipe" style="width: 15%">
                        <template #body="{ data }">
                            <Tag :value="data.type" :severity="getTypeSeverity(data.type)" class="!text-[10px] !px-2" rounded />
                        </template>
                    </Column>

                    <Column header="Karakteristik" style="width: 15%">
                        <template #body="{ data }">
                            <div v-if="data.is_cash" class="flex items-center gap-1 text-emerald-600 font-bold text-xs">
                                <i class="pi pi-wallet"></i> Cash/Bank
                            </div>
                            <span v-else class="text-xs text-slate-400">Non-Cash</span>
                        </template>
                    </Column>

                    <Column header="Aksi" style="width: 15%; text-align: center">
                        <template #body="{ data }">
                            <div class="flex justify-center gap-1" v-if="data.organization_unit_id || $page.props.auth.user.role === 'super_admin'">
                                <Button icon="pi pi-pencil" text rounded severity="info" size="small" @click="openEdit(data)" />
                                <Button icon="pi pi-trash" text rounded severity="danger" size="small" @click="handleDelete($event, data.id)" />
                            </div>
                            <div v-else class="text-xs text-slate-300 italic">
                                <i class="pi pi-lock"></i> Locked
                            </div>
                        </template>
                    </Column>
                </DataTable>

                <div v-if="accounts.links" class="p-4 border-t border-slate-100 bg-slate-50 flex justify-center">
                    </div>
            </div>

        </div>

        <Dialog v-model:visible="modalVisible" modal :header="isEdit ? 'Edit Akun' : 'Buat Akun Baru'" :style="{ width: '30rem' }">
            <div class="space-y-4 pt-2">
                
                <div class="grid grid-cols-3 gap-4">
                    <div class="col-span-1 space-y-1">
                        <label class="font-bold text-xs text-slate-500 uppercase">Kode</label>
                        <InputText v-model="form.code" placeholder="1-xxxx" class="w-full" :disabled="isEdit" /> </div>
                    <div class="col-span-2 space-y-1">
                        <label class="font-bold text-xs text-slate-500 uppercase">Tipe</label>
                        <Select v-model="form.type" :options="accountTypes" optionLabel="label" optionValue="value" class="w-full" :disabled="isEdit" />
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="font-bold text-xs text-slate-500 uppercase">Nama Akun</label>
                    <InputText v-model="form.name" placeholder="Contoh: Kas Pembangunan Masjid" class="w-full" />
                    <small class="text-red-500">{{ form.errors.name }}</small>
                </div>

                <div class="bg-yellow-50 p-3 rounded-lg border border-yellow-200 flex items-start gap-3">
                    <Checkbox v-model="form.is_cash" :binary="true" inputId="is_cash_modal" />
                    <label for="is_cash_modal" class="text-sm text-yellow-800 cursor-pointer select-none leading-tight">
                        <strong>Akun Kas/Bank?</strong><br>
                        <span class="text-xs font-normal">Centang jika akun ini menyimpan uang (Dompet, Rekening Bank, Brankas). Jika akun Pendapatan/Beban, jangan dicentang.</span>
                    </label>
                </div>

            </div>

            <template #footer>
                <Button label="Batal" icon="pi pi-times" text @click="modalVisible = false" />
                <Button label="Simpan" icon="pi pi-check" @click="submitForm" :loading="form.processing" severity="success" />
            </template>
        </Dialog>

        <ConfirmPopup />
    </AppLayout>
</template>