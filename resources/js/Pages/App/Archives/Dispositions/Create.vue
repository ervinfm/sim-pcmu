<script setup>
import { useForm, Link, Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import dayjs from 'dayjs';

// PrimeVue Components
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import Textarea from 'primevue/textarea';
import Button from 'primevue/button';
import DatePicker from 'primevue/datepicker';
import Card from 'primevue/card';

const props = defineProps({
    archive: Object,
    members: Array // List Penerima
});

// Opsi Instruksi Umum (Shortcut)
const commonInstructions = [
    'Tindak Lanjuti', 'Untuk Diketahui', 'Edarkan', 'Wakili', 'Siapkan Bahan', 'Arsipkan'
];

const form = useForm({
    receiver_member_id: null,
    instruction: '',
    due_date: null,
    note: '',
});

const submit = () => {
    form.transform((data) => ({
        ...data,
        // Format tanggal jika ada
        due_date: data.due_date ? dayjs(data.due_date).format('YYYY-MM-DD') : null,
    })).post(route('dispositions.store', props.archive.id));
};
</script>

<template>
    <Head title="Buat Disposisi" />

    <AppLayout title="Buat Disposisi">
        <div class="max-w-4xl mx-auto py-8 px-4">
            
            <div class="mb-6 flex items-center gap-4">
                <Link :href="route('archives.show', archive.id)">
                    <Button icon="pi pi-arrow-left" text rounded severity="secondary" />
                </Link>
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">Disposisi Surat</h2>
                    <p class="text-gray-500 text-sm">Instruksikan tindak lanjut untuk dokumen ini.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                
                <div class="md:col-span-1">
                    <div class="bg-gray-50 border border-gray-200 rounded-xl p-5 sticky top-6">
                        <h3 class="font-bold text-gray-700 mb-3 text-sm uppercase">Dokumen Referensi</h3>
                        
                        <div class="space-y-4 text-sm">
                            <div>
                                <label class="text-xs text-gray-400 block">Perihal</label>
                                <p class="font-medium text-gray-800">{{ archive.title }}</p>
                            </div>
                            <div>
                                <label class="text-xs text-gray-400 block">Nomor Surat</label>
                                <p class="font-mono text-gray-600">{{ archive.reference_number || '-' }}</p>
                            </div>
                            <div>
                                <label class="text-xs text-gray-400 block">Asal Surat</label>
                                <p class="text-gray-600">{{ archive.sender || '-' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="md:col-span-2">
                    <form @submit.prevent="submit" class="bg-white shadow-sm border border-gray-100 rounded-xl p-6 md:p-8">
                        
                        <div class="space-y-6">
                            <div class="flex flex-col gap-2">
                                <label class="font-semibold text-gray-700">Tujuan Disposisi <span class="text-red-500">*</span></label>
                                <Select v-model="form.receiver_member_id" :options="members" optionLabel="name" optionValue="id" 
                                        filter placeholder="Pilih Penerima (Ketik nama...)" class="w-full" 
                                        :class="{'p-invalid': form.errors.receiver_member_id}" />
                                <small v-if="form.errors.receiver_member_id" class="text-red-500">{{ form.errors.receiver_member_id }}</small>
                            </div>

                            <div class="flex flex-col gap-2">
                                <label class="font-semibold text-gray-700">Instruksi <span class="text-red-500">*</span></label>
                                <InputText v-model="form.instruction" placeholder="Contoh: Hadiri dan laporkan hasilnya" class="w-full" />
                                
                                <div class="flex flex-wrap gap-2 mt-1">
                                    <button type="button" v-for="inst in commonInstructions" :key="inst"
                                        @click="form.instruction = inst"
                                        class="px-3 py-1 bg-gray-100 hover:bg-emerald-100 hover:text-emerald-700 text-xs rounded-full transition text-gray-600 border border-gray-200">
                                        {{ inst }}
                                    </button>
                                </div>
                                <small v-if="form.errors.instruction" class="text-red-500">{{ form.errors.instruction }}</small>
                            </div>

                            <div class="flex flex-col gap-2">
                                <label class="font-semibold text-gray-700">Batas Waktu Penyelesaian</label>
                                <DatePicker v-model="form.due_date" dateFormat="dd/mm/yy" showIcon fluid showButtonBar class="w-full" placeholder="Kosongkan jika tidak ada tenggat" />
                            </div>

                            <div class="flex flex-col gap-2">
                                <label class="font-semibold text-gray-700">Catatan Tambahan</label>
                                <Textarea v-model="form.note" rows="3" placeholder="Pesan khusus untuk penerima..." class="w-full" />
                            </div>
                        </div>

                        <div class="mt-8 flex justify-end gap-3 pt-6 border-t border-gray-100">
                            <Link :href="route('archives.show', archive.id)">
                                <Button label="Batal" text severity="secondary" />
                            </Link>
                            <Button type="submit" label="Kirim Disposisi" icon="pi pi-send" :loading="form.processing" severity="success" />
                        </div>

                    </form>
                </div>

            </div>
        </div>
    </AppLayout>
</template>