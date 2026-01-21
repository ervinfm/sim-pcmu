<script setup>
import { computed, ref } from 'vue';
import { useForm, Link, Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import dayjs from 'dayjs';

// PrimeVue Components
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import Textarea from 'primevue/textarea';
import Button from 'primevue/button';
import DatePicker from 'primevue/datepicker';
import FileUpload from 'primevue/fileupload';
import Message from 'primevue/message';

const props = defineProps({
    archive: { type: Object, default: null },
    units: { type: Array, default: () => [] },
});

// --- STATE & LOGIC ---
const isEdit = computed(() => !!props.archive);
const selectedFileName = ref(null);

const categories = [
    { value: 'SURAT_MASUK', label: 'Surat Masuk', icon: 'pi pi-inbox' },
    { value: 'SURAT_KELUAR', label: 'Surat Keluar', icon: 'pi pi-send' },
    { value: 'SK', label: 'Surat Keputusan (SK)', icon: 'pi pi-verified' },
    { value: 'PROPOSAL', label: 'Proposal / Penawaran', icon: 'pi pi-book' },
    { value: 'KEUANGAN', label: 'Laporan Keuangan', icon: 'pi pi-wallet' },
    { value: 'LAINNYA', label: 'Lainnya', icon: 'pi pi-folder' },
];

const confidentialities = [
    { value: 'BIASA', label: 'Biasa / Umum' },
    { value: 'RAHASIA', label: 'Rahasia' },
    { value: 'SANGAT_RAHASIA', label: 'Sangat Rahasia' },
];

const form = useForm({
    // Method default POST, nanti di-override di transform jika Edit
    _method: 'POST', 
    organization_unit_id: props.archive?.organization_unit_id || '',
    title: props.archive?.title || '',
    category: props.archive?.category || 'SURAT_MASUK',
    reference_number: props.archive?.reference_number || '',
    classification_code: props.archive?.classification_code || '',
    document_date: props.archive?.document_date ? new Date(props.archive.document_date) : new Date(),
    received_date: props.archive?.received_date ? new Date(props.archive.received_date) : null,
    sender: props.archive?.sender || '',
    receiver: props.archive?.receiver || '',
    confidentiality: props.archive?.confidentiality || 'BIASA',
    description: props.archive?.description || '',
    file: null,
});

const onFileSelect = (event) => {
    const file = event.files[0];
    form.file = file;
    selectedFileName.value = file.name;
};

// --- SUBMIT HANDLER ---
const submit = () => {
    // 1. Validasi Manual Client-Side (Cegah submit kosong)
    if (!isEdit.value && !form.file) {
        alert("Harap pilih file arsip terlebih dahulu!");
        return;
    }

    // 2. Tentukan URL Tujuan
    // Pastikan ID ada jika mode edit. Jika tidak, fallback ke store.
    const url = isEdit.value && props.archive?.id 
        ? route('archives.update', props.archive.id) 
        : route('archives.store');

    console.log("Submitting to URL:", url, "| Mode Edit:", isEdit.value); // Debugging

    form.transform((data) => {
        // Clone data
        const payload = { ...data };

        // Setting Method Spoofing untuk Laravel
        payload._method = isEdit.value ? 'PUT' : 'POST';

        // Format Tanggal
        if (data.document_date) payload.document_date = dayjs(data.document_date).format('YYYY-MM-DD');
        if (data.received_date) payload.received_date = dayjs(data.received_date).format('YYYY-MM-DD');

        // Hapus file jika kosong saat edit (agar tidak dikirim string "null")
        if (isEdit.value && !data.file) {
            delete payload.file;
        }

        return payload;
    }).post(url, {
        forceFormData: true, // Wajib true untuk upload file
        preserveScroll: true,
        onError: (errors) => {
            console.error("Validation Errors:", errors);
        }
    });
};

const theme = { button: 'success' };
</script>

<template>
    <Head :title="isEdit ? 'Edit Arsip' : 'Upload Arsip Baru'" />

    <AppLayout title="Form Arsip">
        <div class="mb-6">
            <div class="flex items-center gap-4">
                <Link :href="route('archives.index')">
                    <Button icon="pi pi-arrow-left" text rounded severity="secondary" v-tooltip="'Kembali ke Daftar'" />
                </Link>
                <div>
                    <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                        {{ isEdit ? 'Edit Dokumen Arsip' : 'Upload Arsip Digital' }}
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">
                        {{ isEdit ? 'Perbarui metadata dokumen atau ganti file fisik.' : 'Lengkapi formulir untuk menyimpan dokumen ke E-Arsip.' }}
                    </p>
                </div>
            </div>
        </div>

        <div class="pb-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <form @submit.prevent="submit">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                        <div class="lg:col-span-8 space-y-6">
                            
                            <div class="bg-white shadow-sm rounded-xl border border-gray-100 p-6 md:p-8">
                                <h3 class="font-bold text-gray-800 text-lg border-b pb-3 mb-5 flex items-center gap-2">
                                    <i class="pi pi-info-circle text-emerald-600"></i> Informasi Utama
                                </h3>

                                <div class="grid grid-cols-1 gap-5">
                                    <div class="flex flex-col gap-2">
                                        <label class="font-semibold text-gray-600 text-sm">Unit Pemilik Arsip <span class="text-red-500">*</span></label>
                                        <Select v-model="form.organization_unit_id" :options="units" optionLabel="name" optionValue="id" 
                                                filter placeholder="Pilih Unit Organisasi" class="w-full" 
                                                :class="{'p-invalid': form.errors.organization_unit_id}">
                                            <template #option="slotProps">
                                                <div class="flex flex-col py-1">
                                                    <span class="font-bold text-gray-800">{{ slotProps.option.name }}</span>
                                                    <span class="text-xs text-gray-500">{{ slotProps.option.type }}</span>
                                                </div>
                                            </template>
                                        </Select>
                                        <small v-if="form.errors.organization_unit_id" class="text-red-500">{{ form.errors.organization_unit_id }}</small>
                                    </div>

                                    <div class="flex flex-col gap-2">
                                        <label class="font-semibold text-gray-600 text-sm">Perihal / Judul Dokumen <span class="text-red-500">*</span></label>
                                        <InputText v-model="form.title" class="w-full p-inputtext-lg font-bold" placeholder="Contoh: Surat Undangan Rapat Kerja" :class="{'p-invalid': form.errors.title}" />
                                        <small v-if="form.errors.title" class="text-red-500">{{ form.errors.title }}</small>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                        <div class="flex flex-col gap-2">
                                            <label class="font-semibold text-gray-600 text-sm">Kategori</label>
                                            <Select v-model="form.category" :options="categories" optionLabel="label" optionValue="value" 
                                                    placeholder="Pilih Kategori" class="w-full">
                                                <template #option="slotProps">
                                                    <div class="flex items-center gap-2">
                                                        <i :class="slotProps.option.icon" class="text-emerald-600"></i>
                                                        <span>{{ slotProps.option.label }}</span>
                                                    </div>
                                                </template>
                                                <template #value="slotProps">
                                                     <div v-if="slotProps.value" class="flex items-center gap-2">
                                                        <i :class="categories.find(c => c.value === slotProps.value)?.icon"></i>
                                                        <span>{{ categories.find(c => c.value === slotProps.value)?.label }}</span>
                                                    </div>
                                                    <span v-else>{{ slotProps.placeholder }}</span>
                                                </template>
                                            </Select>
                                        </div>

                                        <div class="flex flex-col gap-2">
                                            <label class="font-semibold text-gray-600 text-sm">Sifat Dokumen</label>
                                            <Select v-model="form.confidentiality" :options="confidentialities" optionLabel="label" optionValue="value" 
                                                    class="w-full" />
                                        </div>
                                    </div>

                                    <div class="flex flex-col gap-2">
                                        <label class="font-semibold text-gray-600 text-sm">Ringkasan / Keterangan</label>
                                        <Textarea v-model="form.description" rows="3" placeholder="Catatan singkat isi dokumen..." class="w-full" />
                                        <small v-if="form.errors.description" class="text-red-500">{{ form.errors.description }}</small>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-white shadow-sm rounded-xl border border-gray-100 p-6 md:p-8">
                                <h3 class="font-bold text-gray-800 text-lg border-b pb-3 mb-5 flex items-center gap-2">
                                    <i class="pi pi-list text-emerald-600"></i> Detail Surat
                                </h3>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                    <div class="flex flex-col gap-2">
                                        <label class="font-semibold text-gray-600 text-sm">Nomor Surat</label>
                                        <InputText v-model="form.reference_number" placeholder="Nomor resmi..." class="w-full" />
                                    </div>

                                    <div class="flex flex-col gap-2">
                                        <label class="font-semibold text-gray-600 text-sm">Kode Klasifikasi</label>
                                        <InputText v-model="form.classification_code" placeholder="Contoh: I.A / 2.b" class="w-full" />
                                    </div>

                                    <div class="flex flex-col gap-2">
                                        <label class="font-semibold text-gray-600 text-sm">Tanggal Dokumen</label>
                                        <DatePicker v-model="form.document_date" dateFormat="dd/mm/yy" showIcon fluid showButtonBar class="w-full" />
                                    </div>

                                    <div class="flex flex-col gap-2">
                                        <label class="font-semibold text-gray-600 text-sm">Tanggal Terima (Surat Masuk)</label>
                                        <DatePicker v-model="form.received_date" dateFormat="dd/mm/yy" showIcon fluid showButtonBar class="w-full" placeholder="Kosongkan jika tidak perlu" />
                                    </div>

                                    <div class="flex flex-col gap-2">
                                        <label class="font-semibold text-gray-600 text-sm">Pengirim / Instansi Asal</label>
                                        <InputText v-model="form.sender" placeholder="Nama Pengirim..." class="w-full" />
                                    </div>

                                    <div class="flex flex-col gap-2">
                                        <label class="font-semibold text-gray-600 text-sm">Ditujukan Kepada</label>
                                        <InputText v-model="form.receiver" placeholder="Nama Penerima..." class="w-full" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="lg:col-span-4 flex flex-col gap-6">
                            
                            <div class="bg-white shadow-sm rounded-xl border border-gray-100 p-6">
                                <label class="font-semibold text-gray-700 mb-3 block flex items-center gap-2">
                                    <i class="pi pi-file text-emerald-600"></i> Upload Berkas Fisik
                                </label>

                                <div class="border-2 border-dashed rounded-xl p-6 flex flex-col items-center justify-center text-center transition-all hover:bg-emerald-50"
                                     :class="[form.file ? 'border-emerald-400 bg-emerald-50' : 'border-gray-300']">
                                    
                                    <div v-if="!form.file" class="mb-3">
                                        <i class="pi pi-cloud-upload text-4xl text-gray-300"></i>
                                    </div>

                                    <FileUpload mode="basic" name="file" 
                                                accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.zip" 
                                                :maxFileSize="10000000" 
                                                @select="onFileSelect" 
                                                chooseLabel="Pilih File" 
                                                auto customUpload 
                                                class="p-button-outlined p-button-sm mb-2 w-full" 
                                                :class="{'p-button-success': form.file, 'p-button-secondary': !form.file}" />
                                    
                                    <div v-if="form.file" class="mt-2 text-center">
                                        <span class="text-sm text-emerald-700 font-bold block break-all">
                                            <i class="pi pi-check-circle mr-1"></i> {{ selectedFileName }}
                                        </span>
                                        <span class="text-xs text-gray-500">Siap diupload</span>
                                    </div>
                                    <div v-else class="mt-1">
                                        <span class="text-xs text-gray-400">Max 10MB (PDF/Office/Image)</span>
                                    </div>
                                </div>
                                <small v-if="form.errors.file" class="text-red-500 block mt-2 text-center">{{ form.errors.file }}</small>

                                <div v-if="isEdit && props.archive.file_path && !form.file" class="mt-4 p-3 border rounded-lg bg-blue-50 border-blue-100 flex items-center justify-between">
                                    <div class="flex items-center gap-2 overflow-hidden">
                                        <div class="w-8 h-8 rounded bg-blue-200 flex items-center justify-center text-blue-600 flex-shrink-0">
                                            <i class="pi pi-file"></i>
                                        </div>
                                        <div class="flex flex-col min-w-0">
                                            <span class="text-xs text-gray-500">File Saat Ini</span>
                                            <span class="text-sm font-bold text-gray-700 truncate block">{{ props.archive.file_extension?.toUpperCase() }} File</span>
                                        </div>
                                    </div>
                                    <a :href="route('archives.preview', props.archive.id)" target="_blank" class="text-xs font-bold text-blue-600 hover:text-blue-800 bg-white px-2 py-1 rounded shadow-sm border border-blue-100">
                                        Lihat
                                    </a>
                                </div>
                            </div>

                            <div class="flex flex-col gap-3">
                                <Button type="submit" 
                                        :label="isEdit ? 'Simpan Perubahan' : 'Upload Arsip'" 
                                        icon="pi pi-check" 
                                        :severity="theme.button" 
                                        :loading="form.processing" 
                                        raised 
                                        class="w-full py-3 text-lg font-bold shadow-lg shadow-emerald-200" />
                                
                                <Link :href="route('archives.index')" class="w-full">
                                    <Button label="Batal" icon="pi pi-times" text severity="secondary" class="w-full" />
                                </Link>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
:deep(.p-fileupload-choose) {
    width: 100%;
}
</style>