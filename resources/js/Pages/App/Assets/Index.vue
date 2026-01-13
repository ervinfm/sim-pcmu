<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { FilterMatchMode } from '@primevue/core/api';

// Components
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import Tag from 'primevue/tag';
import ConfirmPopup from 'primevue/confirmpopup';
import { useConfirm } from "primevue/useconfirm";

const props = defineProps({
    assets: Array,
    stats: Object,
});

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
});

// Format Rupiah
const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value);
};

// Delete Logic
const confirm = useConfirm();
const handleDelete = (event, id) => {
    confirm.require({
        target: event.currentTarget,
        message: 'Yakin hapus aset ini? Data tidak bisa dikembalikan.',
        icon: 'pi pi-exclamation-triangle',
        acceptClass: 'p-button-danger',
        accept: () => {
            router.delete(route('assets.destroy', id));
        }
    });
};

// Helper Warna Kondisi
const getConditionSeverity = (condition) => {
    switch (condition) {
        case 'BAIK': return 'success';
        case 'RUSAK_RINGAN': return 'warn';
        case 'RUSAK_BERAT': return 'danger';
        case 'HILANG': return 'secondary';
        default: return 'info';
    }
};
</script>

<template>
    <Head title="Aset & Inventaris" />

    <AppLayout>
        <div class="space-y-6">
            
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Aset & Inventaris</h1>
                    <p class="text-gray-500 text-sm">Manajemen barang milik persyarikatan.</p>
                </div>
                <Link :href="route('assets.create')">
                    <Button label="Tambah Aset" icon="pi pi-box" class="!bg-emerald-600 !border-emerald-600" />
                </Link>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white p-4 rounded-xl border-l-4 border-blue-500 shadow-sm">
                    <div class="text-gray-500 text-xs font-bold uppercase">Total Nilai Aset</div>
                    <div class="text-2xl font-bold text-blue-600 mt-1">{{ formatCurrency(stats.total_value) }}</div>
                </div>
                <div class="bg-white p-4 rounded-xl border-l-4 border-emerald-500 shadow-sm">
                    <div class="text-gray-500 text-xs font-bold uppercase">Jumlah Barang</div>
                    <div class="text-2xl font-bold text-emerald-600 mt-1">{{ stats.total_items }} Unit</div>
                </div>
                <div class="bg-white p-4 rounded-xl border-l-4 border-orange-500 shadow-sm">
                    <div class="text-gray-500 text-xs font-bold uppercase">Kondisi Baik</div>
                    <div class="text-2xl font-bold text-orange-600 mt-1">{{ stats.good_condition }} Unit</div>
                </div>
            </div>

            <div class="card bg-white p-2 rounded-xl border border-gray-100 shadow-sm">
                <DataTable v-model:filters="filters" :value="assets" paginator :rows="10"
                           :globalFilterFields="['name', 'inventory_code', 'category', 'location']"
                           class="p-datatable-sm" stripedRows removableSort>
                    
                    <template #header>
                        <div class="flex justify-between items-center">
                            <span class="text-lg font-bold text-gray-700">Daftar Aset</span>
                            <IconField iconPosition="left">
                                <InputIcon class="pi pi-search" />
                                <InputText v-model="filters['global'].value" placeholder="Cari Barang..." class="w-full md:w-64" />
                            </IconField>
                        </div>
                    </template>

                    <template #empty>
                        <div class="text-center py-8 text-gray-400">Belum ada data aset.</div>
                    </template>

                    <Column field="inventory_code" header="Kode Inventaris" sortable>
                        <template #body="{ data }">
                            <span class="font-mono text-xs bg-gray-100 px-2 py-1 rounded text-gray-600">
                                {{ data.inventory_code }}
                            </span>
                        </template>
                    </Column>

                    <Column field="name" header="Nama Barang" sortable>
                        <template #body="{ data }">
                            <div class="font-bold text-gray-800">{{ data.name }}</div>
                            <div class="text-xs text-gray-500">{{ data.category }}</div>
                        </template>
                    </Column>

                    <Column field="acquisition_value" header="Nilai Perolehan" sortable style="text-align: right">
                        <template #body="{ data }">
                            {{ formatCurrency(data.acquisition_value) }}
                        </template>
                    </Column>

                    <Column field="location" header="Lokasi" sortable></Column>

                    <Column field="condition" header="Kondisi" sortable>
                        <template #body="{ data }">
                            <Tag :value="data.condition.replace('_', ' ')" :severity="getConditionSeverity(data.condition)" class="text-[10px]" />
                        </template>
                    </Column>

                    <Column header="Aksi" style="text-align: center">
                        <template #body="{ data }">
                            <div class="flex justify-center gap-2">
                                <Link :href="route('assets.edit', data.id)">
                                    <Button icon="pi pi-pencil" size="small" text rounded severity="info" />
                                </Link>
                                <Button icon="pi pi-trash" size="small" text rounded severity="danger" @click="handleDelete($event, data.id)" />
                            </div>
                        </template>
                    </Column>

                </DataTable>
            </div>
        </div>
        <ConfirmPopup />
    </AppLayout>
</template>