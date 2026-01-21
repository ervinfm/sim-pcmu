<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import dayjs from 'dayjs';
import 'dayjs/locale/id';

// PrimeVue Components
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import Dialog from 'primevue/dialog';
import Textarea from 'primevue/textarea';

const props = defineProps({
    dispositions: Array
});

// --- HELPER ---
const formatDate = (date) => date ? dayjs(date).locale('id').format('DD MMM YYYY') : '-';
const isOverdue = (date) => date && dayjs(date).isBefore(dayjs(), 'day');

// --- STATE MODAL COMPLETION ---
const completeDialogVisible = ref(false);
const selectedDisposition = ref(null);

const completeForm = useForm({
    completion_note: '',
});

// Buka Modal Selesaikan
const openCompleteDialog = (item) => {
    selectedDisposition.value = item;
    completeForm.completion_note = '';
    completeDialogVisible.value = true;
};

// Submit Penyelesaian
const submitComplete = () => {
    completeForm.patch(route('dispositions.update', selectedDisposition.value.id), {
        onSuccess: () => {
            completeDialogVisible.value = false;
            selectedDisposition.value = null;
        }
    });
};

// Helper Read Status (API Call saat klik detail)
const markAsRead = (item) => {
    if (!item.read_at) {
        axios.post(route('dispositions.read', item.id));
    }
};
</script>

<template>
    <Head title="Disposisi Masuk" />

    <AppLayout title="Kotak Masuk Disposisi">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-800">📥 Kotak Masuk Disposisi</h1>
                <p class="text-gray-500 text-sm">Daftar tugas dan instruksi surat masuk yang perlu Anda tindak lanjuti.</p>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <DataTable :value="dispositions" paginator :rows="10" stripedRows tableStyle="min-width: 50rem">
                    
                    <Column header="Status" style="width: 5%">
                        <template #body="slotProps">
                            <i v-if="slotProps.data.completed_at" class="pi pi-check-circle text-green-500 text-xl" v-tooltip="'Selesai'"></i>
                            <i v-else-if="slotProps.data.read_at" class="pi pi-envelope-open text-blue-400 text-xl" v-tooltip="'Dibaca'"></i>
                            <i v-else class="pi pi-envelope text-gray-400 text-xl" v-tooltip="'Belum Dibaca'"></i>
                        </template>
                    </Column>

                    <Column header="Dokumen Asal" style="width: 25%">
                        <template #body="slotProps">
                            <Link :href="route('archives.show', slotProps.data.archive.id)" 
                                  @click="markAsRead(slotProps.data)"
                                  class="font-bold text-emerald-600 hover:text-emerald-800 hover:underline line-clamp-2">
                                {{ slotProps.data.archive.title }}
                            </Link>
                            <div class="text-xs text-gray-500 mt-1">
                                Dari: {{ slotProps.data.sender?.full_name || 'Admin' }}
                            </div>
                        </template>
                    </Column>

                    <Column header="Instruksi & Catatan" style="width: 30%">
                        <template #body="slotProps">
                            <p class="font-medium text-gray-800">"{{ slotProps.data.instruction }}"</p>
                            <p v-if="slotProps.data.note" class="text-xs text-gray-500 italic mt-1 bg-gray-50 p-1 rounded">
                                Catatan: {{ slotProps.data.note }}
                            </p>
                        </template>
                    </Column>

                    <Column header="Batas Waktu" style="width: 15%">
                        <template #body="slotProps">
                            <div v-if="slotProps.data.due_date">
                                <span :class="{'text-red-600 font-bold': isOverdue(slotProps.data.due_date) && !slotProps.data.completed_at}">
                                    {{ formatDate(slotProps.data.due_date) }}
                                </span>
                                <Tag v-if="isOverdue(slotProps.data.due_date) && !slotProps.data.completed_at" 
                                     value="Telat" severity="danger" class="ml-2 text-[10px]" />
                            </div>
                            <span v-else class="text-gray-400 text-sm">-</span>
                        </template>
                    </Column>

                    <Column header="Aksi" style="width: 15%">
                        <template #body="slotProps">
                            <div class="flex gap-2">
                                <a :href="route('archives.show', slotProps.data.archive.id)" target="_blank">
                                    <Button icon="pi pi-eye" size="small" text rounded severity="secondary" v-tooltip="'Lihat Surat'" />
                                </a>

                                <Button v-if="!slotProps.data.completed_at" 
                                        label="Tindak Lanjuti" 
                                        size="small" 
                                        severity="success" 
                                        outlined 
                                        @click="openCompleteDialog(slotProps.data)" />
                                
                                <Tag v-else severity="success" value="Selesai" icon="pi pi-check" />
                            </div>
                        </template>
                    </Column>

                    <template #empty>
                        <div class="text-center py-8 text-gray-400">Tidak ada disposisi masuk.</div>
                    </template>
                </DataTable>
            </div>

            <Dialog v-model:visible="completeDialogVisible" modal header="Laporan Tindak Lanjut" :style="{ width: '500px' }">
                <div v-if="selectedDisposition">
                    <p class="text-sm text-gray-600 mb-4 bg-blue-50 p-3 rounded border border-blue-100">
                        <span class="font-bold">Instruksi:</span> {{ selectedDisposition.instruction }}
                    </p>

                    <div class="flex flex-col gap-2">
                        <label class="font-semibold text-sm text-gray-700">Hasil Tindak Lanjut / Laporan <span class="text-red-500">*</span></label>
                        <Textarea v-model="completeForm.completion_note" rows="4" 
                                  placeholder="Jelaskan apa yang sudah Anda lakukan..." 
                                  class="w-full" :class="{'p-invalid': completeForm.errors.completion_note}" />
                        <small v-if="completeForm.errors.completion_note" class="text-red-500">{{ completeForm.errors.completion_note }}</small>
                    </div>
                </div>

                <template #footer>
                    <Button label="Batal" text severity="secondary" @click="completeDialogVisible = false" />
                    <Button label="Selesaikan Tugas" icon="pi pi-check-circle" severity="success" 
                            :loading="completeForm.processing" @click="submitComplete" />
                </template>
            </Dialog>

        </div>
    </AppLayout>
</template>