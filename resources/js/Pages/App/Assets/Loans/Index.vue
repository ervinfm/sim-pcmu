<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

// PrimeVue Components
import Menu from 'primevue/menu';
import Button from 'primevue/button';
import ConfirmPopup from 'primevue/confirmpopup';
import Dialog from 'primevue/dialog';
import Textarea from 'primevue/textarea';
import DatePicker from 'primevue/datepicker';
import Select from 'primevue/select';
import { useConfirm } from 'primevue/useconfirm';

const props = defineProps({ loans: Array });
const confirm = useConfirm();

const search = ref('');
const activeTab = ref('ALL'); // Default tetap ACTIVE agar fokus ke yg berjalan

// --- STATE CHECK-IN ---
const showCheckinDialog = ref(false);
const checkinForm = useForm({
    id: null,
    return_date_actual: new Date(),
    condition_after: 'GOOD',
    notes: ''
});

const conditions = [
    { label: 'Baik (Good)', value: 'GOOD' },
    { label: 'Rusak Ringan', value: 'SLIGHTLY_DAMAGED' },
    { label: 'Rusak Berat', value: 'HEAVILY_DAMAGED' },
    { label: 'Hilang', value: 'LOST' }
];

// --- FILTERING LOGIC (UPDATED WITH 'ALL') ---
const filteredLoans = computed(() => {
    let data = props.loans;

    // A. Filter Tab
    if (activeTab.value === 'ALL') {
        // Pass semua data (tanpa filter status)
    } else if (activeTab.value === 'PENDING') {
        data = data.filter(l => l.status === 'PENDING');
    } else if (activeTab.value === 'ACTIVE') {
        data = data.filter(l => ['APPROVED', 'BORROWED'].includes(l.status));
    } else if (activeTab.value === 'OVERDUE') {
        const today = new Date().toISOString().split('T')[0];
        data = data.filter(l => l.status === 'BORROWED' && l.return_date_plan < today);
    } else if (activeTab.value === 'HISTORY') {
        data = data.filter(l => ['COMPLETED', 'REJECTED'].includes(l.status));
    }

    // B. Filter Search
    if (search.value) {
        const q = search.value.toLowerCase();
        data = data.filter(l => 
            (l.asset?.name || '').toLowerCase().includes(q) ||
            (l.borrower_name || '').toLowerCase().includes(q) ||
            (l.member?.full_name || '').toLowerCase().includes(q)
        );
    }
    return data;
});

// ACTIONS MENU
const menu = ref();
const selectedLoan = ref(null);

const getActions = (loan) => {
    if (!loan) return [];
    let actions = [
        { label: 'Detail', icon: 'pi pi-eye', command: () => router.get(route('assets.loans.show', loan.id)) }
    ];

    if (loan.status === 'PENDING') {
        actions.push({ separator: true });
        actions.push({ label: 'Setujui', icon: 'pi pi-check', class: 'text-green-600', command: (e) => confirmAction(e, loan.id, 'APPROVED') });
        actions.push({ label: 'Tolak', icon: 'pi pi-times', class: 'text-red-600', command: (e) => confirmAction(e, loan.id, 'REJECTED') });
    }
    
    if (loan.status === 'APPROVED') {
        actions.push({ separator: true });
        actions.push({ label: 'Serahkan Barang', icon: 'pi pi-box', class: 'text-blue-600', command: (e) => confirmAction(e, loan.id, 'BORROWED') });
    }

    if (loan.status === 'BORROWED') {
        actions.push({ separator: true });
        actions.push({ 
            label: 'Kembalikan (Selesai)', 
            icon: 'pi pi-check-square', 
            class: 'text-purple-600 font-bold',
            command: () => openCheckinModal(loan)
        });
    }

    if (['PENDING', 'APPROVED'].includes(loan.status)) {
        actions.push({ separator: true });
        actions.push({ label: 'Hapus', icon: 'pi pi-trash', class: 'text-red-600', command: (e) => confirmDelete(e, loan.id) });
    }
    return [{ label: 'Aksi', items: actions }];
};

const toggleMenu = (event, loan) => {
    selectedLoan.value = loan;
    menu.value.toggle(event);
};

// CHECK-IN HANDLER
const openCheckinModal = (loan) => {
    checkinForm.reset();
    checkinForm.id = loan.id;
    checkinForm.return_date_actual = new Date();
    checkinForm.condition_after = loan.condition_before || 'GOOD';
    showCheckinDialog.value = true;
};

const submitCheckin = () => {
    checkinForm.put(route('assets.loans.checkin', checkinForm.id), {
        onSuccess: () => showCheckinDialog.value = false
    });
};

const confirmAction = (event, id, status) => {
    const target = event.originalEvent ? event.originalEvent.target : null;
    confirm.require({
        target: target,
        group: 'popup', 
        message: `Ubah status menjadi ${status}?`,
        icon: 'pi pi-info-circle',
        acceptClass: 'p-button-primary p-button-sm',
        accept: () => router.patch(route('assets.loans.change-status', id), { status }, { preserveScroll: true })
    });
};

const confirmDelete = (event, id) => {
    const target = event.originalEvent ? event.originalEvent.target : null;
    confirm.require({
        target: target,
        group: 'popup',
        message: 'Hapus data ini?',
        icon: 'pi pi-exclamation-triangle',
        acceptClass: 'p-button-danger p-button-sm',
        accept: () => router.delete(route('assets.loans.destroy', id))
    });
};

const formatDate = (d) => new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short' });
const getStatusConfig = (s) => {
    const map = {
        'PENDING': { label: 'Menunggu', class: 'bg-yellow-50 text-yellow-700 border-yellow-200' },
        'APPROVED': { label: 'Disetujui', class: 'bg-blue-50 text-blue-700 border-blue-200' },
        'BORROWED': { label: 'Dipinjam', class: 'bg-indigo-50 text-indigo-700 border-indigo-200' },
        'COMPLETED': { label: 'Selesai', class: 'bg-green-50 text-green-700 border-green-200' },
        'REJECTED': { label: 'Ditolak', class: 'bg-red-50 text-red-700 border-red-200' },
        'OVERDUE': { label: 'TELAT', class: 'bg-rose-50 text-rose-700 border-rose-200 animate-pulse font-bold' }
    };
    return map[s] || { label: s, class: 'bg-gray-100' };
};

// --- UPDATED TABS CONFIG ---
const tabs = [
    { id: 'ALL', label: 'Semua Data', icon: 'pi pi-align-justify' },
    { id: 'ACTIVE', label: 'Sedang Dipinjam', icon: 'pi pi-briefcase' },
    { id: 'PENDING', label: 'Request Baru', icon: 'pi pi-inbox' },
    { id: 'OVERDUE', label: 'Jatuh Tempo', icon: 'pi pi-bell' },
    { id: 'HISTORY', label: 'Riwayat', icon: 'pi pi-history' },
];
</script>

<template>
    <Head title="Sirkulasi Peminjaman" />
    <AppLayout>
        <ConfirmPopup group="popup" />
        
        <div class="max-w-7xl mx-auto space-y-6 pb-12">
            
            <div class="flex flex-col md:flex-row justify-between gap-4 border-b border-gray-200 pb-4">
                <div>
                    <h1 class="text-3xl font-black text-gray-900 tracking-tight font-sans">
                        Sirkulasi <span class="text-blue-600">Aset</span>
                    </h1>
                    <p class="text-gray-500 mt-1 text-sm">Monitor pergerakan dan status barang.</p>
                </div>
                <div class="flex gap-2">
                    <Link :href="route('assets.index')" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-xl text-sm font-bold shadow-sm hover:bg-gray-50 flex items-center gap-2"><i class="pi pi-arrow-left"></i> Kembali</Link>
                    <Link :href="route('assets.loans.create')" class="px-4 py-2 bg-gray-900 hover:bg-gray-800 text-white rounded-xl text-sm font-bold shadow-lg transition flex items-center gap-2"><i class="pi pi-plus"></i> Buat Baru</Link>
                </div>
            </div>

            <div class="bg-white p-2 rounded-2xl border border-gray-100 shadow-sm flex flex-col md:flex-row justify-between items-center gap-3">
                <div class="flex flex-wrap gap-1 bg-gray-50/50 p-1 rounded-xl">
                    <button v-for="t in tabs" :key="t.id" @click="activeTab = t.id" class="px-4 py-2 rounded-lg text-sm font-bold flex items-center gap-2 transition-all" :class="activeTab === t.id ? 'bg-white text-blue-600 shadow-sm ring-1 ring-black/5' : 'text-gray-500 hover:bg-gray-100'">
                        <i :class="t.icon"></i> {{ t.label }}
                        
                        <span v-if="t.id === 'ALL'" class="ml-1 text-[10px] bg-gray-200 text-gray-700 px-1.5 py-0.5 rounded-full">
                            {{ loans.length }}
                        </span>
                        <span v-else-if="t.id === 'ACTIVE'" class="ml-1 text-[10px] bg-blue-100 text-blue-700 px-1.5 py-0.5 rounded-full">
                            {{ loans.filter(l => ['APPROVED', 'BORROWED'].includes(l.status)).length }}
                        </span>
                        <span v-else-if="t.id === 'PENDING' && loans.filter(l => l.status === 'PENDING').length > 0" class="ml-1 text-[10px] bg-yellow-100 text-yellow-700 px-1.5 py-0.5 rounded-full">
                            {{ loans.filter(l => l.status === 'PENDING').length }}
                        </span>
                    </button>
                </div>
                <div class="relative w-full md:w-64">
                    <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input v-model="search" type="text" placeholder="Cari..." class="w-full pl-9 pr-3 py-2 bg-gray-50 border-none rounded-xl text-sm focus:ring-2 focus:ring-blue-100">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div v-if="filteredLoans.length === 0" class="md:col-span-2 p-8 text-center text-gray-400 border-2 border-dashed border-gray-200 rounded-2xl">
                    <i class="pi pi-inbox text-3xl mb-2"></i><p class="text-sm">Tidak ada data.</p>
                </div>

                <transition-group name="list">
                    <div v-for="loan in filteredLoans" :key="loan.id" class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm hover:shadow-md transition-all group relative">
                        <div class="flex items-start gap-4">
                            <div class="w-16 h-16 rounded-xl bg-gray-100 border border-gray-200 overflow-hidden flex-shrink-0">
                                <img v-if="loan.asset?.images?.[0]" :src="`/storage/${loan.asset.images[0].image_path}`" class="w-full h-full object-cover">
                                <div v-else class="w-full h-full flex items-center justify-center text-gray-300"><i class="pi pi-image"></i></div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <div class="text-[10px] font-mono font-bold text-gray-500 bg-gray-50 px-1.5 py-0.5 rounded mb-1">{{ loan.asset?.inventory_code }}</div>
                                        <h3 class="text-sm font-bold text-gray-900 truncate">{{ loan.asset?.name }}</h3>
                                    </div>
                                    <div :class="['px-2 py-0.5 rounded text-[10px] font-bold uppercase border', getStatusConfig(loan.status).class]">{{ getStatusConfig(loan.status).label }}</div>
                                </div>
                                <div class="mt-2 flex justify-between items-center text-xs">
                                    <div class="flex items-center gap-2 text-gray-600 font-medium">
                                        <i class="pi pi-user text-blue-500"></i>
                                        <span class="truncate max-w-[120px]" :title="loan.borrower_type === 'INTERNAL' ? (loan.member?.full_name || 'Member') : loan.borrower_name">
                                            {{ loan.borrower_type === 'INTERNAL' ? (loan.member?.full_name || '-') : loan.borrower_name }}
                                        </span>
                                    </div>
                                    <div class="text-gray-500 bg-gray-50 px-2 py-1 rounded">{{ formatDate(loan.loan_date) }} <i class="pi pi-arrow-right mx-1 text-[8px]"></i> {{ formatDate(loan.return_date_plan) }}</div>
                                </div>
                            </div>
                            <div class="absolute bottom-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                <Button icon="pi pi-ellipsis-h" text rounded severity="secondary" @click="toggleMenu($event, loan)" aria-haspopup="true" aria-controls="loan_menu" class="!w-8 !h-8" />
                            </div>
                        </div>
                    </div>
                </transition-group>
            </div>

            <Menu ref="menu" id="loan_menu" :model="getActions(selectedLoan)" :popup="true" class="!rounded-xl !shadow-xl" />

            <Dialog v-model:visible="showCheckinDialog" modal header="Pengembalian Barang" :style="{ width: '400px' }" class="rounded-2xl">
                <div class="space-y-4 pt-2">
                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-bold text-gray-700">Tanggal Kembali</label>
                        <DatePicker v-model="checkinForm.return_date_actual" showIcon dateFormat="dd/mm/yy" class="w-full" />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-bold text-gray-700">Kondisi Barang Saat Ini</label>
                        <Select v-model="checkinForm.condition_after" :options="conditions" optionLabel="label" optionValue="value" class="w-full" />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-bold text-gray-700">Catatan (Opsional)</label>
                        <Textarea v-model="checkinForm.notes" rows="3" placeholder="Keterangan kondisi..." class="w-full !rounded-xl" />
                    </div>
                </div>
                <template #footer>
                    <div class="flex justify-end gap-2 pt-4">
                        <Button label="Batal" text @click="showCheckinDialog = false" class="!text-gray-500" />
                        <Button label="Konfirmasi Selesai" @click="submitCheckin" :loading="checkinForm.processing" class="!bg-purple-600 !border-none !rounded-xl" icon="pi pi-check-circle" />
                    </div>
                </template>
            </Dialog>

        </div>
    </AppLayout>
</template>
<style scoped>.list-enter-active, .list-leave-active { transition: all 0.3s ease; } .list-enter-from, .list-leave-to { opacity: 0; transform: translateY(10px); }</style>