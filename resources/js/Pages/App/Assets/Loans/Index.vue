<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

// PrimeVue Components
import Menu from 'primevue/menu';
import Button from 'primevue/button';
import ConfirmPopup from 'primevue/confirmpopup';
import { useConfirm } from 'primevue/useconfirm';

const props = defineProps({
    loans: Array, 
});

const confirm = useConfirm();

// --- STATE ---
const search = ref('');
const activeTab = ref('ACTIVE'); 

// --- FILTERING ---
const filteredLoans = computed(() => {
    let data = props.loans;

    if (activeTab.value === 'PENDING') {
        data = data.filter(l => l.status === 'PENDING');
    } else if (activeTab.value === 'ACTIVE') {
        data = data.filter(l => ['APPROVED', 'BORROWED'].includes(l.status));
    } else if (activeTab.value === 'OVERDUE') {
        const today = new Date().toISOString().split('T')[0];
        data = data.filter(l => l.status === 'BORROWED' && l.return_date_plan < today);
    } else if (activeTab.value === 'HISTORY') {
        data = data.filter(l => ['COMPLETED', 'REJECTED'].includes(l.status));
    }

    if (search.value) {
        const q = search.value.toLowerCase();
        data = data.filter(l => 
            (l.asset?.name || '').toLowerCase().includes(q) ||
            (l.borrower_name || '').toLowerCase().includes(q) ||
            (l.member?.full_name || '').toLowerCase().includes(q) // Fix: full_name
        );
    }
    return data;
});

// --- MENU ACTIONS DINAMIS ---
const menu = ref();
const selectedLoan = ref(null);

const getActions = (loan) => {
    if (!loan) return [];
    
    let actions = [
        {
            label: 'Detail & Proses',
            icon: 'pi pi-eye',
            command: () => router.get(route('assets.loans.show', loan.id))
        }
    ];

    // Opsi untuk status PENDING
    if (loan.status === 'PENDING') {
        actions.push({ separator: true });
        actions.push({
            label: 'Setujui (Approve)',
            icon: 'pi pi-check',
            class: 'text-green-600',
            command: (event) => confirmStatusChange(event, loan.id, 'APPROVED')
        });
        actions.push({
            label: 'Tolak (Reject)',
            icon: 'pi pi-times',
            class: 'text-red-600',
            command: (event) => confirmStatusChange(event, loan.id, 'REJECTED')
        });
    }

    // Opsi untuk status APPROVED (Siap Ambil)
    if (loan.status === 'APPROVED') {
        actions.push({ separator: true });
        actions.push({
            label: 'Serahkan Barang',
            icon: 'pi pi-box',
            class: 'text-blue-600',
            command: (event) => confirmStatusChange(event, loan.id, 'BORROWED')
        });
    }

    // Opsi Cetak & Hapus
    actions.push({ separator: true });
    actions.push({
        label: 'Cetak Surat',
        icon: 'pi pi-print',
        command: () => window.open(route('assets.loans.show', loan.id) + '?print=true', '_blank')
    });

    if (['PENDING', 'APPROVED'].includes(loan.status)) {
        actions.push({
            label: 'Hapus Data',
            icon: 'pi pi-trash',
            class: 'text-red-600',
            command: (event) => confirmDelete(event, loan.id)
        });
    }

    return [{ label: 'Pilihan Aksi', items: actions }];
};

const toggleMenu = (event, loan) => {
    selectedLoan.value = loan;
    menu.value.toggle(event);
};

// --- CONFIRMATION HANDLERS ---
const confirmStatusChange = (event, id, newStatus) => {
    const target = event.originalEvent ? event.originalEvent.target : null;
    
    confirm.require({
        target: target, 
        group: 'popup', 
        message: `Ubah status menjadi ${newStatus}?`,
        icon: 'pi pi-info-circle',
        acceptClass: 'p-button-primary',
        accept: () => {
            router.patch(route('assets.loans.change-status', id), { status: newStatus }, {
                preserveScroll: true
            });
        }
    });
};

const confirmDelete = (event, id) => {
    confirm.require({
        target: event.originalEvent ? event.originalEvent.target : null,
        message: 'Hapus data peminjaman ini?',
        icon: 'pi pi-exclamation-triangle',
        acceptClass: 'p-button-danger',
        accept: () => router.delete(route('assets.loans.destroy', id))
    });
};

// Helpers
const formatDate = (date) => new Date(date).toLocaleDateString('id-ID', { day: '2-digit', month: 'short' });
const getStatusConfig = (status) => {
    const configs = {
        'PENDING':  { label: 'Menunggu ACC', class: 'bg-yellow-50 text-yellow-700 border-yellow-200', icon: 'pi pi-clock' },
        'APPROVED': { label: 'Disetujui',    class: 'bg-blue-50 text-blue-700 border-blue-200', icon: 'pi pi-check-circle' },
        'BORROWED': { label: 'Dipinjam',     class: 'bg-indigo-50 text-indigo-700 border-indigo-200', icon: 'pi pi-sync' },
        'COMPLETED':{ label: 'Selesai',      class: 'bg-emerald-50 text-emerald-700 border-emerald-200', icon: 'pi pi-verified' },
        'REJECTED': { label: 'Ditolak',      class: 'bg-red-50 text-red-700 border-red-200', icon: 'pi pi-times-circle' },
        'OVERDUE':  { label: 'TERLAMBAT',    class: 'bg-rose-50 text-rose-700 border-rose-200 animate-pulse font-bold', icon: 'pi pi-exclamation-triangle' },
    };
    return configs[status] || { label: status, class: 'bg-gray-100', icon: 'pi pi-info-circle' };
};

const tabs = [
    { id: 'ACTIVE',  label: 'Sedang Dipinjam', icon: 'pi pi-briefcase' },
    { id: 'PENDING', label: 'Request Baru', icon: 'pi pi-inbox' },
    { id: 'OVERDUE', label: 'Jatuh Tempo', icon: 'pi pi-bell' },
    { id: 'HISTORY', label: 'Riwayat', icon: 'pi pi-history' },
];
</script>

<template>
    <Head title="Sirkulasi Peminjaman" />

    <AppLayout>
        <ConfirmPopup />

        <div class="max-w-7xl mx-auto space-y-6 pb-12">
            
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-gray-200 pb-4">
                <div>
                    <h1 class="text-3xl font-black text-gray-900 tracking-tight font-sans">
                        Sirkulasi <span class="text-blue-600">Aset</span>
                    </h1>
                    <p class="text-gray-500 mt-1 text-sm">Monitor pergerakan dan status barang.</p>
                </div>
                <div class="flex gap-2">
                    <Link :href="route('assets.index')" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-xl text-sm font-bold shadow-sm hover:bg-gray-50 transition flex items-center gap-2">
                        <i class="pi pi-arrow-left"></i> Kembali
                    </Link>
                    <Link :href="route('assets.loans.create')" class="px-4 py-2 bg-gray-900 hover:bg-gray-800 text-white rounded-xl text-sm font-bold shadow-lg transition flex items-center gap-2">
                        <i class="pi pi-plus-circle"></i> Buat Baru
                    </Link>
                </div>
            </div>

            <div class="bg-white p-2 rounded-2xl border border-gray-100 shadow-sm">
                <div class="flex flex-col md:flex-row justify-between items-center gap-3">
                    <div class="flex flex-wrap gap-1 w-full md:w-auto p-1 bg-gray-50/50 rounded-xl">
                        <button v-for="tab in tabs" :key="tab.id" @click="activeTab = tab.id"
                            class="relative px-4 py-2 rounded-lg text-sm font-bold transition-all flex items-center gap-2"
                            :class="activeTab === tab.id ? 'bg-white text-blue-600 shadow-sm ring-1 ring-black/5' : 'text-gray-500 hover:bg-gray-100/50'">
                            <i :class="tab.icon"></i> {{ tab.label }}
                            <span v-if="tab.id === 'ACTIVE'" class="ml-1 text-[10px] px-1.5 py-0.5 rounded-full bg-blue-100 text-blue-700">
                                {{ loans.filter(l => ['APPROVED', 'BORROWED'].includes(l.status)).length }}
                            </span>
                        </button>
                    </div>
                    <div class="relative w-full md:w-64 group">
                        <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input v-model="search" type="text" placeholder="Cari..." 
                            class="block w-full pl-9 pr-3 py-2 bg-gray-50 border-none rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-blue-100 focus:border-blue-500 transition-all">
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div v-if="filteredLoans.length === 0" class="md:col-span-2 bg-white rounded-2xl p-8 text-center border-dashed border-2 border-gray-200 text-gray-400">
                    <i class="pi pi-inbox text-3xl mb-2"></i>
                    <p class="text-sm">Data tidak ditemukan.</p>
                </div>

                <transition-group name="list">
                    <div v-for="loan in filteredLoans" :key="loan.id" 
                        class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm hover:shadow-md hover:border-blue-300 transition-all group relative overflow-hidden"
                    >
                        <div class="flex items-start gap-4">
                            <div class="w-16 h-16 rounded-xl bg-gray-100 border border-gray-200 flex-shrink-0 overflow-hidden">
                                <img v-if="loan.asset?.images?.[0]" :src="`/storage/${loan.asset.images[0].image_path}`" class="w-full h-full object-cover">
                                <div v-else class="w-full h-full flex items-center justify-center text-gray-300"><i class="pi pi-image"></i></div>
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <div class="text-[10px] font-mono font-bold text-gray-500 bg-gray-50 px-1.5 py-0.5 rounded inline-block mb-1">
                                            {{ loan.asset?.inventory_code }}
                                        </div>
                                        <h3 class="text-sm font-bold text-gray-900 truncate" :title="loan.asset?.name">{{ loan.asset?.name }}</h3>
                                    </div>
                                    <div :class="['px-2 py-0.5 rounded text-[10px] font-bold uppercase border', getStatusConfig(loan.status).class]">
                                        {{ getStatusConfig(loan.status).label }}
                                    </div>
                                </div>

                                <div class="mt-2 flex items-center justify-between">
                                    <div class="flex items-center gap-2 text-xs text-gray-600">
                                        <i class="pi pi-user text-blue-500"></i>
                                        <span class="font-medium truncate max-w-[120px]" :title="loan.member?.full_name || loan.borrower_name">
                                            {{ loan.member ? loan.member.full_name : loan.borrower_name }}
                                        </span>
                                    </div>
                                    <div class="text-[10px] text-gray-500 bg-gray-50 px-2 py-1 rounded">
                                        {{ formatDate(loan.loan_date) }} <i class="pi pi-arrow-right mx-1 text-[8px]"></i> {{ formatDate(loan.return_date_plan) }}
                                    </div>
                                </div>
                            </div>

                            <div class="absolute bottom-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                <Button icon="pi pi-ellipsis-h" text rounded severity="secondary" @click="toggleMenu($event, loan)" aria-haspopup="true" aria-controls="loan_menu" class="!w-8 !h-8" />
                            </div>
                        </div>
                    </div>
                </transition-group>
            </div>

            <Menu ref="menu" id="loan_menu" :model="getActions(selectedLoan)" :popup="true" class="!rounded-xl !shadow-xl !min-w-[180px]" />

        </div>
    </AppLayout>
</template>

<style scoped>
.list-enter-active, .list-leave-active { transition: all 0.3s ease; }
.list-enter-from, .list-leave-to { opacity: 0; transform: translateY(10px); }
:deep(.p-menu) { padding: 0.5rem; border-radius: 0.75rem; }
</style>