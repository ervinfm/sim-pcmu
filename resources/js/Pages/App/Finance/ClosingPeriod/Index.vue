<script setup>
import { ref } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Select from 'primevue/select';
import Tag from 'primevue/tag';
import ConfirmPopup from 'primevue/confirmpopup';
import { useConfirm } from "primevue/useconfirm";

const props = defineProps({
    periods: Array
});

// Form Tutup Buku
const form = useForm({
    month: new Date().getMonth(), // Bulan Lalu (Logic 0-11 JS agak tricky, sesuaikan nanti)
    year: new Date().getFullYear()
});

const months = [
    { label: 'Januari', value: 1 }, { label: 'Februari', value: 2 }, { label: 'Maret', value: 3 },
    { label: 'April', value: 4 }, { label: 'Mei', value: 5 }, { label: 'Juni', value: 6 },
    { label: 'Juli', value: 7 }, { label: 'Agustus', value: 8 }, { label: 'September', value: 9 },
    { label: 'Oktober', value: 10 }, { label: 'November', value: 11 }, { label: 'Desember', value: 12 }
];

const years = [2023, 2024, 2025, 2026, 2027].map(y => ({ label: y, value: y }));

// Logic Hapus/Buka Kunci
const confirm = useConfirm();
const handleUnlock = (event, id) => {
    confirm.require({
        target: event.currentTarget,
        message: 'PERINGATAN: Membuka kunci periode berisiko data historis berubah. Lanjutkan?',
        icon: 'pi pi-exclamation-triangle',
        acceptClass: 'p-button-danger',
        accept: () => {
            router.delete(route('finance.closing-periods.destroy', id));
        }
    });
};

const submitClose = () => {
    form.post(route('finance.closing-periods.store'), {
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <Head title="Tutup Buku" />

    <AppLayout>
        <div class="px-4 md:px-6 space-y-6">
            
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-black text-gray-800 tracking-tight">Tutup Buku</h1>
                    <p class="text-gray-500 text-sm">Kunci periode akuntansi agar data aman dari perubahan.</p>
                </div>
            </div>

            <div class="border-b border-gray-100 overflow-x-auto">
                <nav class="flex items-center gap-6 min-w-max">
                    <Link :href="route('finance.transactions.index')" class="py-4 px-1 text-gray-500 hover:text-emerald-600 font-medium text-sm border-b-2 border-transparent hover:border-emerald-200 flex items-center gap-2"><i class="pi pi-list"></i> Transaksi</Link>
                    <Link :href="route('finance.reports.index')" class="py-4 px-1 text-gray-500 hover:text-emerald-600 font-medium text-sm border-b-2 border-transparent hover:border-emerald-200 flex items-center gap-2"><i class="pi pi-chart-bar"></i> Laporan</Link>
                    <Link :href="route('finance.opening-balances.index')" class="py-4 px-1 text-gray-500 hover:text-emerald-600 font-medium text-sm border-b-2 border-transparent hover:border-emerald-200 flex items-center gap-2"><i class="pi pi-wallet"></i> Saldo Awal</Link>
                    <Link :href="route('finance.closing-periods.index')" class="py-4 px-1 text-emerald-700 font-bold text-sm border-b-2 border-emerald-500 flex items-center gap-2"><i class="pi pi-lock"></i> Tutup Buku</Link>
                    <Link :href="route('finance.accounts.index')" class="py-4 px-1 text-gray-500 hover:text-emerald-600 font-medium text-sm border-b-2 border-transparent hover:border-emerald-200 flex items-center gap-2"><i class="pi pi-book"></i> Master Akun</Link>
                </nav>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <div class="lg:col-span-1">
                    <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm sticky top-6">
                        <h3 class="font-bold text-gray-800 mb-4">Kunci Periode Baru</h3>
                        <form @submit.prevent="submitClose" class="space-y-4">
                            <div class="space-y-1">
                                <label class="text-xs font-bold text-gray-500">Bulan</label>
                                <Select v-model="form.month" :options="months" optionLabel="label" optionValue="value" fluid placeholder="Pilih Bulan" />
                            </div>
                            <div class="space-y-1">
                                <label class="text-xs font-bold text-gray-500">Tahun</label>
                                <Select v-model="form.year" :options="years" optionLabel="label" optionValue="value" fluid placeholder="Pilih Tahun" />
                            </div>
                            
                            <Button type="submit" label="Kunci Periode Ini" icon="pi pi-lock" 
                                    class="w-full !bg-gray-800 !border-gray-800" 
                                    :loading="form.processing" />
                        </form>
                    </div>
                </div>

                <div class="lg:col-span-2">
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                        <div class="p-4 border-b border-gray-50 bg-gray-50/50">
                            <h3 class="font-bold text-gray-700">Riwayat Penutupan</h3>
                        </div>
                        <DataTable :value="periods" stripedRows>
                            <template #empty>
                                <div class="text-center py-8 text-gray-400 text-sm">Belum ada periode yang ditutup.</div>
                            </template>
                            
                            <Column header="Periode">
                                <template #body="{ data }">
                                    <span class="font-bold text-gray-800">{{ months.find(m => m.value === data.month)?.label }} {{ data.year }}</span>
                                </template>
                            </Column>
                            
                            <Column header="Status">
                                <template #body="{ data }">
                                    <Tag value="Terkunci" severity="secondary" icon="pi pi-lock" />
                                </template>
                            </Column>

                            <Column header="Ditutup Oleh">
                                <template #body="{ data }">
                                    <div class="text-xs">
                                        <p class="font-bold text-gray-700">{{ data.closer?.name || 'System' }}</p>
                                        <p class="text-gray-400">{{ new Date(data.closed_at).toLocaleDateString('id-ID') }}</p>
                                    </div>
                                </template>
                            </Column>

                            <Column header="Aksi" style="text-align: right">
                                <template #body="{ data }">
                                    <Button icon="pi pi-lock-open" text rounded severity="danger" 
                                            @click="handleUnlock($event, data.id)" 
                                            v-tooltip.top="'Buka Kunci (Darurat)'" />
                                </template>
                            </Column>
                        </DataTable>
                    </div>
                </div>

            </div>
        </div>
        <ConfirmPopup />
    </AppLayout>
</template>