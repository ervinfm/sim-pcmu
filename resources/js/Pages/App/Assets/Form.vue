<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

// --- PRIMEVUE IMPORTS ---
import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import Textarea from 'primevue/textarea';
import Dropdown from 'primevue/dropdown';
import DatePicker from 'primevue/datepicker'; // PrimeVue v4 (sebelumnya Calendar)
import FileUpload from 'primevue/fileupload';
import Message from 'primevue/message';
import Button from 'primevue/button';
import Tag from 'primevue/tag';

// --- PROPS ---
const props = defineProps({
    asset: Object,          // Data Aset (jika Edit)
    schemas: Object,        // JSON Schema untuk Spesifikasi Dinamis
    organization_units: Array,
    locations: Array,
    asset_units: Array,
    user_organization_id: Number,
});

// --- STATE & FORM INIT ---
const isEditMode = computed(() => !!props.asset);
const previewImages = ref([]); // Untuk menyimpan preview gambar baru

// Inisialisasi Form Inertia
const form = useForm({
    _method: isEditMode.value ? 'PUT' : 'POST', // Laravel Multipart Trick
    organization_unit_id: props.asset?.organization_unit_id || props.user_organization_id,
    name: props.asset?.name || '',
    category: props.asset?.category || '',
    asset_unit_id: props.asset?.asset_unit_id || null,
    asset_location_id: props.asset?.asset_location_id || null,
    acquisition_date: props.asset?.acquisition_date ? new Date(props.asset.acquisition_date) : new Date(),
    acquisition_value: Number(props.asset?.acquisition_value) || 0,
    source_of_acquisition: props.asset?.source_of_acquisition || 'PURCHASE',
    condition: props.asset?.condition || 'GOOD',
    description: props.asset?.description || '',
    
    // Dynamic Specs
    specifications: props.asset?.specifications || {},

    // Files (Array Kosong saat init)
    images: [], 
    documents: [],
    
    // Edit Only: Foto baru tambahan
    new_images: [], 
});

// --- DYNAMIC SCHEMA LOGIC ---
const activeSchema = computed(() => {
    if (!form.category) return null;
    return props.schemas[form.category];
});

// Reset spesifikasi jika kategori berubah (Hanya saat Create)
watch(() => form.category, (newVal, oldVal) => {
    if (!isEditMode.value && newVal !== oldVal) {
        form.specifications = {};
        // Pre-fill keys agar reaktif
        if (props.schemas[newVal]) {
            props.schemas[newVal].fields.forEach(field => {
                form.specifications[field.key] = null;
            });
        }
    }
});

// --- FILE UPLOAD LOGIC ---
const handleImageSelect = (event) => {
    const files = event.files;
    
    // Simpan ke form inertia
    if (isEditMode.value) {
        form.new_images = files;
    } else {
        form.images = files;
    }

    // Generate Preview
    previewImages.value = [];
    for (let file of files) {
        const reader = new FileReader();
        reader.onload = (e) => previewImages.value.push(e.target.result);
        reader.readAsDataURL(file);
    }
};

const handleDocSelect = (event) => {
    form.documents = event.files;
};

// --- SUBMIT ---
const submit = () => {
    if (isEditMode.value) {
        // Gunakan route update dengan parameter ID
        form.post(route('assets.update', props.asset.id), {
            forceFormData: true, // Wajib untuk upload file dengan PUT (spoofing)
            preserveScroll: true,
            onSuccess: () => { /* Handle success toast if needed */ }
        });
    } else {
        form.post(route('assets.store'), {
            preserveScroll: true,
        });
    }
};

// --- OPTIONS DATA (UPDATED) ---
// Diperbarui dengan icon dan color mapping untuk tampilan yang lebih bagus
const conditionOptions = [
    { label: 'Baik (Good)', value: 'GOOD', color: 'green', icon: 'pi pi-check-circle' },
    { label: 'Rusak Ringan', value: 'SLIGHTLY_DAMAGED', color: 'yellow', icon: 'pi pi-exclamation-triangle' },
    { label: 'Rusak Berat', value: 'HEAVILY_DAMAGED', color: 'red', icon: 'pi pi-times-circle' },
];

const sourceOptions = [
    { label: 'Pembelian (Purchase)', value: 'PURCHASE' },
    { label: 'Hibah / Wakaf', value: 'WAKAF' },
    { label: 'Bantuan Pemerintah', value: 'GOV_AID' },
    { label: 'Sumbangan Lain', value: 'GRANT' },
];
</script>

<template>
    <Head :title="isEditMode ? 'Edit Aset' : 'Tambah Aset Baru'" />

    <AppLayout>
        <form @submit.prevent="submit" class="max-w-7xl mx-auto pb-12 space-y-8">
            
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-gray-200 pb-5">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 tracking-tight">
                        {{ isEditMode ? 'Edit Data Aset' : 'Registrasi Aset Baru' }}
                    </h1>
                    <p class="text-gray-500 mt-1 text-sm">
                        Lengkapi formulir di bawah ini untuk {{ isEditMode ? 'memperbarui' : 'mencatat' }} inventaris.
                    </p>
                </div>
                <div class="flex gap-3">
                    <Link :href="route('assets.index')" class="px-5 py-2.5 bg-white border border-gray-300 text-gray-700 rounded-xl text-sm font-semibold hover:bg-gray-50 transition shadow-sm">
                        Batal
                    </Link>
                    <Button type="submit" :loading="form.processing" label="Simpan Data" icon="pi pi-check" class="!bg-blue-600 !border-blue-600 !rounded-xl !px-6 !font-semibold shadow-lg shadow-blue-200" />
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

                <div class="lg:col-span-4 space-y-6">
                    
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                        <label class="block text-sm font-bold text-gray-700 mb-4 flex items-center gap-2">
                            <i class="pi pi-camera text-blue-500"></i> Foto Aset
                        </label>

                        <FileUpload 
                            mode="basic" 
                            name="images[]" 
                            :multiple="true" 
                            accept="image/*" 
                            :maxFileSize="2000000"
                            @select="handleImageSelect" 
                            :auto="false"
                            chooseLabel="Pilih Foto"
                            class="w-full !font-medium" 
                        />
                        <small class="text-gray-400 text-xs mt-2 block">* Maks 2MB/foto. Foto pertama jadi cover.</small>

                        <div v-if="previewImages.length > 0" class="grid grid-cols-3 gap-2 mt-4">
                            <div v-for="(src, idx) in previewImages" :key="idx" class="relative aspect-square rounded-lg overflow-hidden border border-gray-200 group">
                                <img :src="src" class="w-full h-full object-cover" />
                                <div v-if="idx === 0" class="absolute bottom-0 left-0 right-0 bg-blue-600 text-white text-[9px] text-center py-0.5 font-bold">COVER</div>
                            </div>
                        </div>

                        <div v-if="isEditMode && asset.images && previewImages.length === 0" class="mt-4">
                            <p class="text-xs font-semibold text-gray-500 mb-2">Foto Saat Ini:</p>
                            <div class="grid grid-cols-3 gap-2">
                                <div v-for="img in asset.images" :key="img.id" class="relative aspect-square rounded-lg overflow-hidden border border-gray-200">
                                    <img :src="`/storage/${img.image_path}`" class="w-full h-full object-cover opacity-75 hover:opacity-100 transition" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 space-y-5">
                        <div class="border-l-4 border-blue-500 pl-4 py-1">
                            <h3 class="text-lg font-bold text-gray-800">
                                <i class="pi pi-objects-column text-blue-500 pe-2"></i>
                                Identitas Barang
                            </h3>
                        </div>

                        <div class="space-y-1">
                            <label class="text-sm font-medium text-gray-700">Nama Aset <span class="text-red-500">*</span></label>
                            <InputText v-model="form.name" placeholder="Contoh: Laptop Asus ROG" class="w-full" :class="{'p-invalid': form.errors.name}" />
                            <small class="text-red-500" v-if="form.errors.name">{{ form.errors.name }}</small>
                        </div>

                        <div class="space-y-1">
                            <label class="text-sm font-medium text-gray-700">Kategori <span class="text-red-500">*</span></label>
                            <Dropdown 
                                v-model="form.category" 
                                :options="Object.keys(schemas).map(key => ({ label: schemas[key].label, value: key }))" 
                                optionLabel="label" 
                                optionValue="value" 
                                placeholder="Pilih Kategori" 
                                class="w-full"
                                :class="{'p-invalid': form.errors.category}"
                                filter
                            />
                            <small class="text-gray-400 text-xs">Menentukan form spesifikasi.</small>
                            <small class="text-red-500 block" v-if="form.errors.category">{{ form.errors.category }}</small>
                        </div>

                        <div class="space-y-1">
                            <label class="text-sm font-medium text-gray-700">Satuan <span class="text-red-500">*</span></label>
                            <Dropdown 
                                v-model="form.asset_unit_id" 
                                :options="asset_units" 
                                optionLabel="name" 
                                optionValue="id" 
                                placeholder="Pilih Satuan" 
                                class="w-full"
                                filter
                            />
                        </div>

                        <div v-if="isEditMode" class="bg-gray-50 p-3 rounded-lg border border-gray-200">
                            <p class="text-xs text-gray-500 font-mono">Kode Inventaris (Auto):</p>
                            <p class="font-bold text-gray-800 font-mono tracking-wide">{{ asset.inventory_code }}</p>
                        </div>
                    </div>

                </div>

                <div class="lg:col-span-8 space-y-6">

                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                        <h3 class="text-sm font-bold text-gray-900 mb-5 flex items-center gap-2 uppercase tracking-wider">
                            <i class="pi pi-map-marker text-blue-500"></i> Lokasi & Nilai Aset
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-1">
                                <label class="text-sm font-medium text-gray-700">Lokasi Penempatan</label>
                                <Dropdown 
                                    v-model="form.asset_location_id" 
                                    :options="locations" 
                                    optionLabel="name" 
                                    optionValue="id" 
                                    placeholder="Pilih Ruangan/Lokasi" 
                                    class="w-full"
                                    filter
                                    showClear
                                />
                            </div>

                            <div class="space-y-1">
                                <label class="text-sm font-medium text-gray-700">Tanggal Perolehan <span class="text-red-500">*</span></label>
                                <DatePicker v-model="form.acquisition_date" showIcon dateFormat="dd/mm/yy" class="w-full" />
                            </div>

                            <div class="space-y-1">
                                <label class="text-sm font-medium text-gray-700">Nilai Perolehan (IDR) <span class="text-red-500">*</span></label>
                                <InputNumber 
                                    v-model="form.acquisition_value" 
                                    mode="currency" 
                                    currency="IDR" 
                                    locale="id-ID" 
                                    placeholder="0" 
                                    class="w-full" 
                                />
                            </div>

                            <div class="space-y-1">
                                <label class="text-sm font-medium text-gray-700">Sumber Dana</label>
                                <Dropdown 
                                    v-model="form.source_of_acquisition" 
                                    :options="sourceOptions" 
                                    optionLabel="label" 
                                    optionValue="value" 
                                    class="w-full"
                                />
                            </div>

                            <div class="space-y-1 md:col-span-2">
                                <label class="text-sm font-medium text-gray-700 mb-2 block">Kondisi Fisik Saat Ini</label>
                                <div class="flex gap-3 flex-wrap">
                                    <div v-for="opt in conditionOptions" :key="opt.value" class="flex items-center">
                                        <input type="radio" :id="opt.value" :value="opt.value" v-model="form.condition" class="hidden peer">
                                        
                                        <label :for="opt.value" 
                                            class="cursor-pointer px-4 py-2.5 rounded-xl border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50 hover:border-gray-300 peer-checked:bg-blue-50 peer-checked:text-blue-700 peer-checked:border-blue-500 peer-checked:shadow-sm transition-all duration-200 flex items-center gap-3 select-none">
                                            
                                            <i :class="[opt.icon, `text-${opt.color}-500`]" class="text-lg"></i>
                                            
                                            <span>{{ opt.label }}</span>
                                            
                                            <i v-if="form.condition === opt.value" class="pi pi-check text-blue-600 text-xs ml-1 font-bold"></i>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <transition name="fade" mode="out-in">
                        <div v-if="activeSchema" class="bg-blue-50/50 rounded-2xl p-6 shadow-sm border border-blue-100">
                            <h3 class="text-sm font-bold text-gray-900 mb-5 flex items-center gap-2 uppercase tracking-wider">
                                <i class="pi pi-sliders-h text-blue-600"></i> Spesifikasi: {{ activeSchema.label }}
                            </h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div v-for="(field, idx) in activeSchema.fields" :key="idx" class="space-y-1">
                                    <label class="text-sm font-medium text-gray-700">
                                        {{ field.label }}
                                    </label>

                                    <InputNumber v-if="field.type === 'number'" v-model="form.specifications[field.key]" class="w-full" :placeholder="field.label" />
                                    
                                    <DatePicker v-else-if="field.type === 'date'" v-model="form.specifications[field.key]" showIcon class="w-full" />
                                    
                                    <Textarea v-else-if="field.type === 'textarea'" v-model="form.specifications[field.key]" rows="3" class="w-full" />
                                    
                                    <InputText v-else v-model="form.specifications[field.key]" class="w-full" />
                                </div>
                            </div>
                        </div>
                    </transition>

                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 space-y-6">
                        
                        <div class="space-y-1">
                            <label class="text-sm font-medium text-gray-700">Catatan / Deskripsi Tambahan</label>
                            <Textarea v-model="form.description" rows="4" placeholder="Tuliskan detail lain yang tidak tercakup di atas..." class="w-full" />
                        </div>

                        <div class="pt-4 border-t border-gray-100">
                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center gap-2">
                                <i class="pi pi-file text-orange-500"></i> Dokumen Legalitas (PDF)
                            </label>
                            
                            <FileUpload 
                                mode="advanced" 
                                name="documents[]" 
                                :multiple="true" 
                                accept=".pdf,.doc,.docx" 
                                :maxFileSize="5000000"
                                @select="handleDocSelect" 
                                :auto="false"
                                chooseLabel="Pilih File"
                                :showUploadButton="false"
                                :showCancelButton="false"
                                class="w-full"
                            >
                                <template #empty>
                                    <div class="flex flex-col items-center justify-center py-6 text-gray-400">
                                        <i class="pi pi-cloud-upload text-4xl mb-2"></i>
                                        <p class="text-sm">Drag & drop file dokumen (Sertifikat, Faktur, dll) di sini.</p>
                                    </div>
                                </template>
                            </FileUpload>
                        </div>

                    </div>

                </div>
            </div>

        </form>
    </AppLayout>
</template>

<style scoped>
/* Styling Tambahan untuk Transisi */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

/* Custom Overrides PrimeVue agar lebih 'Soft' */
:deep(.p-inputtext), :deep(.p-dropdown), :deep(.p-inputnumber-input), :deep(.p-textarea) {
    border-radius: 0.75rem; /* rounded-xl */
}
:deep(.p-inputtext:enabled:focus) {
    box-shadow: 0 0 0 1px #3b82f6;
    border-color: #3b82f6;
}
:deep(.p-fileupload-choose) {
    border-radius: 0.75rem;
}
</style>