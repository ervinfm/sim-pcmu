<script setup>
import { ref, computed } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, Head, router } from '@inertiajs/vue3';
import dayjs from 'dayjs';
import 'dayjs/locale/id';

// --- PRIMEVUE IMPORTS ---
import ConfirmPopup from 'primevue/confirmpopup';
import { useConfirm } from "primevue/useconfirm";

const props = defineProps({ archives: Array });
const confirm = useConfirm(); // Inisialisasi Confirm Service

// --- STATE MANAGEMENT ---
const search = ref('');
const selectedCategory = ref('');
const viewMode = ref('list');

// --- CONSTANTS ---
const categories = [
    { value: 'SK', label: 'Surat Keputusan (SK)' },
    { value: 'SURAT_MASUK', label: 'Surat Masuk' },
    { value: 'SURAT_KELUAR', label: 'Surat Keluar' },
    { value: 'PROPOSAL', label: 'Proposal' },
    { value: 'KEUANGAN', label: 'Laporan Keuangan' },
    { value: 'LAINNYA', label: 'Lainnya' },
];

// --- LOGIC ---
const filteredArchives = computed(() => {
    return props.archives.filter(item => {
        const query = search.value.toLowerCase();
        const matchesSearch = 
            item.title.toLowerCase().includes(query) ||
            (item.reference_number && item.reference_number.toLowerCase().includes(query)) ||
            (item.description && item.description.toLowerCase().includes(query));
        const matchesCategory = selectedCategory.value ? item.category === selectedCategory.value : true;
        return matchesSearch && matchesCategory;
    });
});

const formatDate = (dateString) => {
    if (!dateString) return '-';
    return dayjs(dateString).locale('id').format('DD MMM YYYY');
};

const getFileIcon = (ext) => {
    const e = ext ? ext.toLowerCase() : '';
    if (['pdf'].includes(e)) return 'text-red-500';
    if (['doc', 'docx'].includes(e)) return 'text-blue-500';
    if (['xls', 'xlsx', 'csv'].includes(e)) return 'text-emerald-600';
    if (['jpg', 'jpeg', 'png'].includes(e)) return 'text-purple-500';
    return 'text-gray-500';
};

// --- DELETE ACTION (MODIFIED) ---
const deleteArchive = (event, id, title) => {
    confirm.require({
        target: event.currentTarget, // Popup muncul di tombol yang diklik
        message: `Hapus arsip "${title}"?`,
        icon: 'pi pi-exclamation-triangle',
        rejectProps: {
            label: 'Batal',
            severity: 'secondary',
            outlined: true,
            size: 'small'
        },
        acceptProps: {
            label: 'Hapus',
            severity: 'danger',
            size: 'small'
        },
        accept: () => {
            router.delete(route('archives.destroy', id), {
                preserveScroll: true,
            });
        }
    });
};
</script>

<template>
    <Head title="E-Arsip Digital" />

    <AppLayout title="Arsip & Persuratan">
        <ConfirmPopup />

        <div class="">
            
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800 tracking-tight">
                        <span class="text-emerald-600">E-Arsip</span> Digital
                    </h1>
                    <p class="text-gray-500 mt-1 text-sm">
                        Manajemen arsip surat masuk, keluar, dan dokumen organisasi.
                    </p>
                </div>
                
                <Link :href="route('archives.create')" 
                    class="inline-flex items-center px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg shadow-lg shadow-emerald-200 transition-all duration-200 transform hover:-translate-y-0.5">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Upload Arsip Baru
                </Link>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6 sticky top-4 z-20">
                <div class="flex flex-col md:flex-row gap-4 justify-between items-center">
                    <div class="flex flex-col sm:flex-row w-full md:w-auto gap-3 flex-grow">
                        <div class="relative w-full sm:w-64">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </span>
                            <input v-model="search" type="text" placeholder="Cari nomor surat, judul..." 
                                class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all placeholder-gray-400" />
                        </div>
                        <select v-model="selectedCategory" 
                            class="w-full sm:w-48 py-2 pl-3 pr-8 border border-gray-200 rounded-lg text-sm focus:ring-emerald-500 focus:border-emerald-500 text-gray-600 cursor-pointer">
                            <option value="">Semua Kategori</option>
                            <option v-for="cat in categories" :key="cat.value" :value="cat.value">{{ cat.label }}</option>
                        </select>
                    </div>
                    <div class="flex bg-gray-100 p-1 rounded-lg">
                        <button @click="viewMode = 'list'" :class="{'bg-white text-emerald-600 shadow-sm': viewMode === 'list', 'text-gray-500 hover:text-gray-700': viewMode !== 'list'}" class="p-2 rounded-md transition-all duration-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                        </button>
                        <button @click="viewMode = 'grid'" :class="{'bg-white text-emerald-600 shadow-sm': viewMode === 'grid', 'text-gray-500 hover:text-gray-700': viewMode !== 'grid'}" class="p-2 rounded-md transition-all duration-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                        </button>
                    </div>
                </div>
            </div>

            <div v-if="filteredArchives.length === 0" class="flex flex-col items-center justify-center py-16 bg-white rounded-xl border border-dashed border-gray-300">
                <div class="bg-emerald-50 p-4 rounded-full mb-4">
                    <svg class="w-10 h-10 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900">Tidak ada arsip ditemukan</h3>
                <p class="text-gray-500 text-sm mt-1">Coba ubah kata kunci pencarian atau filter kategori.</p>
            </div>

            <div v-else-if="viewMode === 'list'" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-gray-600">
                        <thead class="bg-gray-50 border-b border-gray-100 uppercase text-xs font-bold text-gray-500 tracking-wider">
                            <tr>
                                <th class="px-6 py-4">Dokumen</th>
                                <th class="px-6 py-4 hidden sm:table-cell">Kategori</th>
                                <th class="px-6 py-4 hidden md:table-cell">Unit / Pengupload</th>
                                <th class="px-6 py-4 text-center">Tanggal</th>
                                <th class="px-6 py-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="item in filteredArchives" :key="item.id" class="hover:bg-emerald-50/30 transition duration-150 group">
                                <td class="px-6 py-4">
                                    <div class="flex items-start gap-3">
                                        <div class="mt-1">
                                            <svg :class="getFileIcon(item.file_extension)" class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24"><path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zM6 20V4h7v5h5v11H6z"></path></svg>
                                        </div>
                                        <div>
                                            <Link :href="route('archives.show', item.id)" class="font-bold text-gray-800 hover:text-emerald-600 line-clamp-2 leading-tight mb-1">
                                                {{ item.title }}
                                            </Link>
                                            <div class="text-xs text-gray-500 flex items-center gap-2">
                                                <span v-if="item.reference_number" class="bg-gray-100 px-1.5 py-0.5 rounded text-gray-600 font-mono">
                                                    {{ item.reference_number }}
                                                </span>
                                                <span>{{ (item.file_size / 1024).toFixed(1) }} MB</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 hidden sm:table-cell">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border"
                                        :class="{
                                            'bg-blue-50 text-blue-700 border-blue-100': item.category === 'SURAT_MASUK',
                                            'bg-amber-50 text-amber-700 border-amber-100': item.category === 'SURAT_KELUAR',
                                            'bg-emerald-50 text-emerald-700 border-emerald-100': item.category === 'SK',
                                            'bg-gray-50 text-gray-600 border-gray-100': !['SURAT_MASUK', 'SURAT_KELUAR', 'SK'].includes(item.category)
                                        }">
                                        {{ item.category.replace(/_/g, ' ') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 hidden md:table-cell">
                                    <div class="text-gray-900 font-medium text-xs">{{ item.organization_unit?.name || '-' }}</div>
                                    <div class="text-gray-400 text-xs mt-0.5">Oleh: {{ item.uploader?.name || 'System' }}</div>
                                </td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    <div class="text-xs font-semibold text-gray-600">{{ formatDate(item.document_date || item.created_at) }}</div>
                                    <div class="text-[10px] text-gray-400 mt-1">Diupload: {{ formatDate(item.created_at) }}</div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <Link :href="route('archives.show', item.id)" class="p-1.5 text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-md transition" title="Detail">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        </Link>
                                        <a v-if="item.file_path" :href="route('archives.download', item.id)" target="_blank" class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-md transition" title="Download">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                        </a>
                                        <Link :href="route('archives.edit', item.id)" class="p-1.5 text-gray-400 hover:text-amber-500 hover:bg-amber-50 rounded-md transition" title="Edit">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        </Link>

                                        <button @click="deleteArchive($event, item.id, item.title)" class="p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-md transition" title="Hapus">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>

                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <div v-for="item in filteredArchives" :key="item.id" class="bg-white group rounded-xl border border-gray-100 shadow-sm hover:shadow-md hover:border-emerald-200 transition-all duration-200 flex flex-col h-full relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-1" :class="{'bg-blue-500': item.category === 'SURAT_MASUK', 'bg-amber-500': item.category === 'SURAT_KELUAR', 'bg-emerald-500': item.category === 'SK', 'bg-gray-300': !['SURAT_MASUK', 'SURAT_KELUAR', 'SK'].includes(item.category)}"></div>
                    <div class="p-5 flex-grow">
                        <div class="flex justify-between items-start mb-3">
                            <div class="bg-gray-50 p-2 rounded-lg">
                                <svg :class="getFileIcon(item.file_extension)" class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24"><path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zM6 20V4h7v5h5v11H6z"></path></svg>
                            </div>
                            <div class="flex gap-1">
                                <Link :href="route('archives.edit', item.id)" class="p-1 text-gray-300 hover:text-amber-500 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                </Link>
                                <button @click="deleteArchive($event, item.id, item.title)" class="p-1 text-gray-300 hover:text-red-500 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </div>
                        </div>
                        <Link :href="route('archives.show', item.id)" class="block group-hover:text-emerald-600 transition-colors">
                            <h3 class="font-bold text-gray-800 text-lg mb-1 leading-snug line-clamp-2" :title="item.title">{{ item.title }}</h3>
                        </Link>
                        <p v-if="item.reference_number" class="text-xs font-mono text-gray-500 mb-2 bg-gray-50 inline-block px-1.5 rounded">{{ item.reference_number }}</p>
                        <p v-if="item.description" class="text-sm text-gray-500 line-clamp-2 mb-4">{{ item.description }}</p>
                    </div>
                    <div class="bg-gray-50 px-5 py-3 border-t border-gray-100 flex justify-between items-center">
                        <div class="text-xs text-gray-400">{{ formatDate(item.document_date || item.created_at) }}</div>
                        <div class="flex gap-2 items-center">
                             <a v-if="item.file_path" :href="route('archives.download', item.id)" target="_blank" class="text-gray-400 hover:text-blue-600 p-1 hover:bg-white rounded transition" title="Download">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                            </a>
                            <Link :href="route('archives.show', item.id)" class="text-emerald-600 hover:text-emerald-700 text-xs font-bold uppercase tracking-wide flex items-center">
                                Detail <span class="ml-1 text-base">&rarr;</span>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </AppLayout>
</template>