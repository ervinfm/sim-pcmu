<script setup>
import { computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import dayjs from 'dayjs';
import 'dayjs/locale/id';

// PrimeVue Components
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import Timeline from 'primevue/timeline';
import Card from 'primevue/card';

// --- PRIMEVUE CONFIRM ---
import ConfirmPopup from 'primevue/confirmpopup';
import { useConfirm } from "primevue/useconfirm";

const props = defineProps({
    archive: Object,
});
const confirm = useConfirm();

// --- HELPERS ---
const formatDate = (date) => {
    if (!date) return '-';
    return dayjs(date).locale('id').format('DD MMMM YYYY');
};

const formatDateTime = (date) => {
    if (!date) return '-';
    return dayjs(date).locale('id').format('DD MMM YYYY, HH:mm');
};

const formatFileSize = (kb) => {
    if (kb < 1024) return `${kb} KB`;
    return `${(kb / 1024).toFixed(2)} MB`;
};

const getFileIcon = (ext) => {
    const e = ext ? ext.toLowerCase() : '';
    if (['pdf'].includes(e)) return 'pi pi-file-pdf text-red-500';
    if (['doc', 'docx'].includes(e)) return 'pi pi-file-word text-blue-500';
    if (['xls', 'xlsx'].includes(e)) return 'pi pi-file-excel text-emerald-600';
    if (['jpg', 'jpeg', 'png'].includes(e)) return 'pi pi-image text-purple-500';
    return 'pi pi-file text-gray-500';
};

const getCategorySeverity = (cat) => {
    switch (cat) {
        case 'SURAT_MASUK': return 'info';
        case 'SURAT_KELUAR': return 'warn';
        case 'SK': return 'success';
        case 'KEUANGAN': return 'secondary';
        default: return 'contrast';
    }
};

const getConfidentialityColor = (level) => {
    if (level === 'SANGAT_RAHASIA') return 'bg-red-100 text-red-700 border-red-200';
    if (level === 'RAHASIA') return 'bg-orange-100 text-orange-700 border-orange-200';
    return 'bg-green-100 text-green-700 border-green-200';
};

// --- DELETE ACTION (MODIFIED) ---
const deleteArchive = (event) => {
    confirm.require({
        target: event.currentTarget,
        message: 'Yakin ingin menghapus arsip ini secara permanen?',
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
            router.delete(route('archives.destroy', props.archive.id));
        }
    });
};
</script>

<template>
    <Head title="Detail Arsip" />

    <AppLayout title="Detail Arsip">
        <ConfirmPopup />

        <div class="mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <Link :href="route('archives.index')">
                        <Button icon="pi pi-arrow-left" text rounded severity="secondary" v-tooltip="'Kembali'" />
                    </Link>
                    <div>
                        <div class="flex items-center gap-2 text-sm text-gray-500 mb-1">
                            <span>Arsip Digital</span>
                            <i class="pi pi-angle-right text-xs"></i>
                            <span>{{ archive.organization_unit?.name }}</span>
                        </div>
                        <h1 class="font-bold text-2xl text-gray-800 leading-tight">
                            Detail Dokumen
                        </h1>
                    </div>
                </div>

                <div class="flex gap-2">
                    <Link :href="route('archives.edit', archive.id)">
                        <Button label="Edit" icon="pi pi-pencil" severity="secondary" outlined />
                    </Link>
                    <Button label="Hapus" icon="pi pi-trash" severity="danger" text @click="deleteArchive($event)" />
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto pb-12">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <div class="lg:col-span-2 space-y-6">
                    
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="p-6 md:p-8">
                            <div class="flex flex-col gap-4 mb-6 border-b border-gray-100 pb-6">
                                <div class="flex flex-wrap items-center gap-2">
                                    <Tag :value="archive.category.replace('_', ' ')" :severity="getCategorySeverity(archive.category)" />
                                    <span class="px-2 py-1 rounded text-xs font-bold border uppercase" :class="getConfidentialityColor(archive.confidentiality)">
                                        {{ archive.confidentiality.replace('_', ' ') }}
                                    </span>
                                    <span v-if="archive.classification_code" class="bg-gray-100 text-gray-600 px-2 py-1 rounded text-xs font-mono border border-gray-200">
                                        Kode: {{ archive.classification_code }}
                                    </span>
                                </div>
                                <h2 class="text-2xl font-bold text-gray-900 leading-snug">
                                    {{ archive.title }}
                                </h2>
                                <p v-if="archive.description" class="text-gray-600 leading-relaxed bg-gray-50 p-4 rounded-lg border border-gray-100">
                                    {{ archive.description }}
                                </p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-y-6 gap-x-8">
                                <div>
                                    <label class="text-xs font-bold text-gray-400 uppercase tracking-wider block mb-1">Nomor Surat</label>
                                    <p class="font-mono text-gray-800 font-medium text-lg">{{ archive.reference_number || '-' }}</p>
                                </div>
                                <div>
                                    <label class="text-xs font-bold text-gray-400 uppercase tracking-wider block mb-1">Tanggal Dokumen</label>
                                    <div class="flex items-center gap-2">
                                        <i class="pi pi-calendar text-emerald-500"></i>
                                        <p class="text-gray-800 font-medium">{{ formatDate(archive.document_date) }}</p>
                                    </div>
                                </div>
                                <div v-if="archive.sender">
                                    <label class="text-xs font-bold text-gray-400 uppercase tracking-wider block mb-1">Pengirim / Asal</label>
                                    <p class="text-gray-800">{{ archive.sender }}</p>
                                </div>
                                <div v-if="archive.receiver">
                                    <label class="text-xs font-bold text-gray-400 uppercase tracking-wider block mb-1">Ditujukan Kepada</label>
                                    <p class="text-gray-800">{{ archive.receiver }}</p>
                                </div>
                                <div v-if="archive.received_date">
                                    <label class="text-xs font-bold text-gray-400 uppercase tracking-wider block mb-1">Tanggal Diterima</label>
                                    <p class="text-gray-800">{{ formatDate(archive.received_date) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 md:p-8">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="font-bold text-lg text-gray-800 flex items-center gap-2">
                                <i class="pi pi-sitemap text-emerald-600"></i> Alur & Disposisi
                            </h3>
                            <Link :href="route('dispositions.create', archive.id)">
                                <Button label="Disposisi Baru" icon="pi pi-plus" size="small" severity="success" outlined />
                            </Link>
                        </div>

                        <div v-if="archive.dispositions && archive.dispositions.length > 0">
                            <Timeline :value="archive.dispositions" class="customized-timeline">
                                <template #marker="slotProps">
                                    <span class="flex w-8 h-8 items-center justify-center rounded-full bg-emerald-100 text-emerald-600 border border-emerald-200 shadow-sm z-10">
                                        <i class="pi pi-send text-xs"></i>
                                    </span>
                                </template>
                                <template #content="slotProps">
                                    <div class="pb-8 pl-2">
                                        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start mb-1">
                                            <span class="font-bold text-gray-800 text-sm">
                                                {{ slotProps.item.sender?.full_name || 'Admin' }} 
                                                <i class="pi pi-arrow-right text-xs mx-1 text-gray-400"></i>
                                                {{ slotProps.item.receiver?.full_name || slotProps.item.receiver_unit?.name || 'Unit Terkait' }}
                                            </span>
                                            <small class="text-gray-400 text-xs mt-1 sm:mt-0">
                                                {{ formatDateTime(slotProps.item.created_at) }}
                                            </small>
                                        </div>
                                        
                                        <div class="bg-gray-50 p-3 rounded-lg border border-gray-100 mt-2">
                                            <p class="text-sm text-gray-700 font-medium mb-1">
                                                <i class="pi pi-comment text-emerald-500 mr-1"></i>
                                                "{{ slotProps.item.instruction }}"
                                            </p>
                                            <p v-if="slotProps.item.note" class="text-xs text-gray-500 italic">
                                                Catatan: {{ slotProps.item.note }}
                                            </p>
                                            <div v-if="slotProps.item.completed_at" class="mt-2 pt-2 border-t border-gray-200">
                                                <span class="text-xs font-bold text-emerald-600 flex items-center gap-1">
                                                    <i class="pi pi-check-circle"></i> Selesai: {{ formatDateTime(slotProps.item.completed_at) }}
                                                </span>
                                                <p class="text-xs text-gray-600 mt-1">{{ slotProps.item.completion_note }}</p>
                                            </div>
                                            <div v-else class="mt-2">
                                                <Tag value="Menunggu Tindak Lanjut" severity="warning" style="font-size: 10px;" />
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </Timeline>
                        </div>
                        <div v-else class="text-center py-8 text-gray-400">
                            <i class="pi pi-sitemap text-4xl mb-2 opacity-50"></i>
                            <p class="text-sm">Belum ada riwayat disposisi untuk dokumen ini.</p>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-1 space-y-6">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="bg-gray-50 p-6 flex flex-col items-center justify-center border-b border-gray-100 text-center">
                            <i :class="getFileIcon(archive.file_extension)" style="font-size: 4rem" class="mb-4"></i>
                            <p class="font-bold text-gray-800 break-all px-4 line-clamp-2" :title="archive.title">
                                {{ archive.file_path.split('/').pop() }}
                            </p>
                            <span class="text-xs text-gray-500 mt-1 uppercase">
                                {{ archive.file_extension }} • {{ formatFileSize(archive.file_size) }}
                            </span>
                        </div>
                        <div class="p-4 flex flex-col gap-3">
                            <a :href="route('archives.preview', archive.id)" target="_blank" class="w-full">
                                <Button label="Lihat File" icon="pi pi-eye" severity="secondary" outlined class="w-full" />
                            </a>
                            <a :href="route('archives.download', archive.id)" target="_blank" class="w-full">
                                <Button label="Download" icon="pi pi-download" severity="success" class="w-full shadow-lg shadow-emerald-100" />
                            </a>
                            <div class="text-center mt-2 text-xs text-gray-400">
                                Diunduh {{ archive.download_count }} kali
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                        <h4 class="font-bold text-gray-800 mb-4 text-sm uppercase tracking-wide border-b pb-2">
                            Properti Arsip
                        </h4>
                        <ul class="space-y-4 text-sm">
                            <li class="flex justify-between items-center">
                                <span class="text-gray-500">Status</span>
                                <Tag :value="archive.status" :severity="archive.status === 'published' ? 'success' : 'secondary'" />
                            </li>
                            <li class="flex justify-between items-start">
                                <span class="text-gray-500">Pemilik</span>
                                <span class="font-medium text-gray-800 text-right max-w-[60%]">
                                    {{ archive.organization_unit?.name }}
                                </span>
                            </li>
                            <li class="flex justify-between items-center">
                                <span class="text-gray-500">Diupload Oleh</span>
                                <span class="font-medium text-gray-800">
                                    {{ archive.uploader?.name || 'System' }}
                                </span>
                            </li>
                            <li class="flex justify-between items-center">
                                <span class="text-gray-500">Tanggal Upload</span>
                                <span class="font-medium text-gray-800">
                                    {{ formatDateTime(archive.created_at) }}
                                </span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
:deep(.p-timeline-event-connector) {
    background-color: #e5e7eb;
}
</style>