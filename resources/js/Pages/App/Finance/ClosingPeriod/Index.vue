<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import FinanceNavigation from '@/Components/Finance/FinanceNavigation.vue';

// PrimeVue Components
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import Select from 'primevue/select';
import Tag from 'primevue/tag';
import ConfirmPopup from 'primevue/confirmpopup';
import { useConfirm } from "primevue/useconfirm";
import { useToast } from "primevue/usetoast";

const props = defineProps({
    periods: Array, // Data periode yg sudah ditutup
});

const page = usePage();
const confirm = useConfirm();
const toast = useToast();

const showModal = ref(false);
const currentYear = ref(new Date().getFullYear());
const yearOptions = [2024, 2025, 2026, 2027, 2028];

// Data Bulan Statis
const months = [
    { name: 'Januari', value: 1 }, { name: 'Februari', value: 2 },
    { name: 'Maret', value: 3 }, { name: 'April', value: 4 },
    { name: 'Mei', value: 5 }, { name: 'Juni', value: 6 },
    { name: 'Juli', value: 7 }, { name: 'Agustus', value: 8 },
    { name: 'September', value: 9 }, { name: 'Oktober', value: 10 },
    { name: 'November', value: 11 }, { name: 'Desember', value: 12 },
];

const form = useForm({
    year: currentYear.value,
    month: null,
});

// Helper: Cari data closing berdasarkan bulan & tahun
const getClosingData = (monthVal, yearVal) => {
    return props.periods.find(p => p.month === monthVal && p.year === yearVal);
};

// Helper: Cek status
const isClosed = (monthVal, yearVal) => {
    return !!getClosingData(monthVal, yearVal);
};

const openModal = () => {
    form.reset();
    form.year = currentYear.value;
    // Auto-select bulan sebelumnya (karena biasanya kita tutup buku bulan lalu)
    const prevMonth = new Date().getMonth(); // 0-11, where 0 is Jan. but our value is 1-12. 
    // Logic: if current is Feb (1), prev is Jan (1). 
    form.month = prevMonth === 0 ? 12 : prevMonth; 
    if(prevMonth === 0) form.year = currentYear.value - 1;

    showModal.value = true;
};

const submitClosing = () => {
    form.post(route('finance.closing-periods.store'), {
        onSuccess: () => {
            showModal.value = false;
            toast.add({ severity: 'success', summary: 'Berhasil', detail: 'Periode berhasil ditutup', life: 3000 });
        },
        onError: () => {
            toast.add({ severity: 'error', summary: 'Gagal', detail: 'Terjadi kesalahan', life: 3000 });
        }
    });
};

const confirmDelete = (event, id) => {
    confirm.require({
        target: event.currentTarget,
        message: 'PERINGATAN SUPER ADMIN: Membuka periode ini memungkinkan perubahan data historis. Lanjutkan?',
        icon: 'pi pi-exclamation-triangle',
        acceptClass: 'p-button-danger',
        accept: () => {
            router.delete(route('finance.closing-periods.destroy', id), {
                onSuccess: () => toast.add({ severity: 'warn', summary: 'Unlocked', detail: 'Periode dibuka kembali', life: 3000 })
            });
        }
    });
};
</script>

<template>
    <Head title="Tutup Buku" />

    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tutup Buku Periode</h2>
        </template>

        <div class="py-4">
            <div class="max-w-7xl mx-auto sm:px-4 lg:px-6 space-y-6">
                
                <FinanceNavigation />

                <div class="bg-indigo-50 border-l-4 border-indigo-500 p-4 rounded-r shadow-sm flex items-start gap-3">
                    <i class="pi pi-info-circle text-indigo-500 text-xl mt-0.5"></i>
                    <div>
                        <h4 class="font-bold text-indigo-900 text-sm">Manajemen Periode Akuntansi</h4>
                        <p class="text-sm text-indigo-700 mt-1">
                            Tutup buku akan mengunci seluruh transaksi (Pemasukan, Pengeluaran, Jurnal) pada bulan tersebut. 
                            Pastikan rekonsiliasi bank dan penyesuaian sudah selesai sebelum menutup buku.
                        </p>
                    </div>
                </div>

                <div class="flex flex-col md:flex-row justify-between items-center bg-white p-4 rounded-xl shadow-sm border border-gray-200 gap-4">
                    <div class="flex items-center gap-3 w-full md:w-auto">
                         <span class="text-gray-500 font-bold text-sm uppercase tracking-wide">Tahun Buku:</span>
                         <Select v-model="currentYear" :options="yearOptions" class="w-32 font-bold" />
                    </div>
                    <Button label="Tutup Buku Baru" icon="pi pi-lock" severity="warning" @click="openModal" class="w-full md:w-auto" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    <div v-for="m in months" :key="m.value" 
                         class="relative rounded-xl border-2 transition-all duration-200 p-5 flex flex-col justify-between h-40 group"
                         :class="isClosed(m.value, currentYear) 
                            ? 'bg-white border-emerald-500 shadow-sm' 
                            : 'bg-gray-50 border-dashed border-gray-300 opacity-80 hover:opacity-100 hover:border-gray-400'">
                        
                        <div class="flex justify-between items-start">
                            <span class="font-bold text-lg" 
                                  :class="isClosed(m.value, currentYear) ? 'text-emerald-700' : 'text-gray-500'">
                                {{ m.name }}
                            </span>
                            <span class="text-xs font-bold bg-gray-100 px-2 py-1 rounded text-gray-500">
                                {{ currentYear }}
                            </span>
                        </div>

                        <div v-if="isClosed(m.value, currentYear)">
                            <div class="flex items-center gap-2 mt-2">
                                <i class="pi pi-lock text-emerald-600 text-xl"></i>
                                <div>
                                    <div class="text-[10px] uppercase text-gray-400 font-bold">Status</div>
                                    <div class="text-sm font-bold text-emerald-600">TERKUNCI</div>
                                </div>
                            </div>
                            
                            <div class="mt-3 pt-2 border-t border-gray-100 text-[10px] text-gray-500">
                                <p>Oleh: {{ getClosingData(m.value, currentYear).closed_by_name }}</p>
                                <p>Tgl: {{ getClosingData(m.value, currentYear).closed_at_formatted }}</p>
                            </div>

                            <div v-if="$page.props.auth.user.role === 'super_admin'" 
                                 class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                <Button icon="pi pi-lock-open" rounded text severity="danger" size="small" 
                                        @click="confirmDelete($event, getClosingData(m.value, currentYear).id)" 
                                        v-tooltip.left="'Buka Kunci (Super Admin)'" />
                            </div>
                        </div>

                        <div v-else class="flex flex-col items-center justify-center h-full text-gray-400">
                            <i class="pi pi-lock-open text-2xl opacity-20 mb-1"></i>
                            <span class="text-xs font-medium">Periode Terbuka</span>
                        </div>

                    </div>
                </div>

            </div>
        </div>

        <Dialog v-model:visible="showModal" header="Konfirmasi Tutup Buku" modal class="w-full md:w-[450px]">
            <div class="flex flex-col gap-5">
                <div class="bg-amber-50 p-3 rounded-lg border border-amber-200 text-amber-800 text-sm flex gap-3 items-start">
                    <i class="pi pi-exclamation-triangle mt-1 text-amber-600"></i>
                    <span>Tindakan ini akan <b>MENGUNCI PERMANEN</b> seluruh transaksi pada periode yang dipilih. Tidak ada data yang bisa diedit/hapus setelah ini.</span>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-2">
                        <label class="font-semibold text-sm">Bulan</label>
                        <Select v-model="form.month" :options="months" optionLabel="name" optionValue="value" placeholder="Pilih Bulan" class="w-full" />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="font-semibold text-sm">Tahun</label>
                        <Select v-model="form.year" :options="yearOptions" class="w-full" />
                    </div>
                </div>
            </div>

            <template #footer>
                <div class="flex justify-end gap-2 pt-4">
                    <Button label="Batal" icon="pi pi-times" text severity="secondary" @click="showModal = false" />
                    <Button label="Kunci Periode Sekarang" icon="pi pi-lock" severity="warning" @click="submitClosing" :loading="form.processing" />
                </div>
            </template>
        </Dialog>

        <ConfirmPopup />
    </AppLayout>
</template>