<script setup>
import { computed, ref, watch } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

// Components
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import Editor from 'primevue/editor';
import Dropdown from 'primevue/dropdown';
import Button from 'primevue/button';
import Calendar from 'primevue/calendar';
import FileUpload from 'primevue/fileupload';
import Card from 'primevue/card';
import Message from 'primevue/message';

const props = defineProps({
    organization: Object,
    parents: Array,
});

const isEdit = computed(() => !!props.organization);

const typeMapping = [
    { label: 'Pimpinan Cabang (PCM)', value: 'PCM', category: 'STRUKTURAL' },
    { label: 'Pimpinan Ranting (PRM)', value: 'PRM', category: 'STRUKTURAL' },
    
    { label: 'TK / PAUD', value: 'TK', category: 'AUM' },
    { label: 'SD / MI', value: 'SD', category: 'AUM' },
    { label: 'SMP / MTs', value: 'SMP', category: 'AUM' },
    { label: 'SMA / MA / SMK', value: 'SMK', category: 'AUM' },
    { label: 'Pondok Pesantren', value: 'PONPES', category: 'AUM' },
    { label: 'Masjid / Musholla', value: 'MASJID', category: 'AUM' },
    { label: 'Klinik / RS', value: 'KLINIK', category: 'AUM' },
    { label: 'Lazismu', value: 'LAZISMU', category: 'AUM' },
    
    { label: 'Aisyiyah', value: 'AISYIYAH', category: 'ORTOM' },
    { label: 'Pemuda Muhammadiyah', value: 'PEMUDA', category: 'ORTOM' },
    { label: 'Nasyiatul Aisyiyah', value: 'NA', category: 'ORTOM' },
    { label: 'IPM (Pelajar)', value: 'IPM', category: 'ORTOM' },
    { label: 'IMM (Mahasiswa)', value: 'IMM', category: 'ORTOM' },
    { label: 'Tapak Suci', value: 'TAPAK_SUCI', category: 'ORTOM' },
    { label: 'Hizbul Wathon', value: 'HW', category: 'ORTOM' },
];

const form = useForm({
    _method: isEdit.value ? 'PUT' : 'POST',
    name: props.organization?.name || '',
    type: props.organization?.type || null,
    category: props.organization?.category || '', 
    parent_id: props.organization?.parent_id || null,
    code: props.organization?.code || '',
    sk_number: props.organization?.sk_number || '',
    establishment_date: props.organization?.establishment_date ? new Date(props.organization.establishment_date) : null,
    email: props.organization?.email || '',
    phone: props.organization?.phone || '',
    address: props.organization?.address || '',
    latitude: props.organization?.latitude || '',
    longitude: props.organization?.longitude || '',
    description: props.organization?.description || '', 
    logo: null,
});

watch(() => form.type, (newType) => {
    const selected = typeMapping.find(t => t.value === newType);
    if (selected) {
        form.category = selected.category;
    }
});

const descriptionLabel = computed(() => {
    return form.category === 'AUM' ? 'Profil Pelayanan & Fasilitas' : 'Deskripsi / Sejarah Singkat';
});

const onFileSelect = (event) => {
    form.logo = event.files[0];
};

const openMapHelper = () => {
    window.open('https://www.google.com/maps', '_blank');
};

const submit = () => {
    const url = isEdit.value ? route('organizations.update', props.organization.id) : route('organizations.store');
    form.post(url, { forceFormData: true });
};
</script>

<template>
    <Head :title="isEdit ? 'Edit Organisasi' : 'Tambah Unit'" />

    <AppLayout>
        <div class="max-w-6xl mx-auto space-y-6">
            
            <div class="flex items-center justify-between">
                <div>
                    <Link :href="route('organizations.index')" class="text-sm font-bold text-gray-500 hover:text-emerald-600 mb-1 block">
                        <i class="pi pi-arrow-left text-xs"></i> Kembali
                    </Link>
                    <h1 class="text-2xl font-black text-gray-800">
                        {{ isEdit ? 'Edit Data Organisasi' : 'Registrasi Unit Baru' }}
                    </h1>
                </div>
            </div>

            <form @submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                
                <div class="lg:col-span-8 space-y-6">
                    <Card class="shadow-sm border border-gray-200 rounded-2xl">
                        <template #title><span class="text-base font-bold text-gray-800">Identitas Unit</span></template>
                        <template #content>
                            <div class="grid grid-cols-1 gap-5">
                                
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="space-y-1">
                                        <label class="text-xs font-bold text-gray-500 uppercase">Tipe Unit <span class="text-red-500">*</span></label>
                                        <Dropdown v-model="form.type" :options="typeMapping" optionLabel="label" optionValue="value" 
                                                  placeholder="Pilih Jenis..." class="w-full" filter />
                                        <small class="text-emerald-600 font-bold" v-if="form.category">
                                            Kategori: {{ form.category }}
                                        </small>
                                    </div>
                                    <div class="space-y-1">
                                        <label class="text-xs font-bold text-gray-500 uppercase">Induk Organisasi</label>
                                        <Dropdown v-model="form.parent_id" :options="parents" optionLabel="name" optionValue="id" 
                                                  placeholder="Pilih Induk (Opsional)" class="w-full" filter showClear 
                                                  :disabled="form.type === 'PCM'" />
                                    </div>
                                </div>

                                <div class="space-y-1">
                                    <label class="text-xs font-bold text-gray-500 uppercase">Nama Resmi Unit <span class="text-red-500">*</span></label>
                                    <InputText v-model="form.name" placeholder="Contoh: PRM Ketenong / SD Muhammadiyah 1" class="w-full font-bold" />
                                    <small class="text-red-500">{{ form.errors.name }}</small>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div class="space-y-1">
                                        <label class="text-xs font-bold text-gray-500 uppercase">Nomor SK</label>
                                        <InputText v-model="form.sk_number" placeholder="No. SK Pendirian" class="w-full" />
                                    </div>
                                    <div class="space-y-1">
                                        <label class="text-xs font-bold text-gray-500 uppercase">Kode Unit</label>
                                        <InputText v-model="form.code" placeholder="Kode Unik / NPSN" class="w-full" />
                                    </div>
                                </div>

                                <div class="space-y-1">
                                    <label class="text-xs font-bold text-gray-500 uppercase">Alamat Lengkap</label>
                                    <Textarea v-model="form.address" rows="5" class="w-full" placeholder="Jalan, Desa/Kelurahan, Kecamatan..." />
                                </div>

                                <div class="space-y-1.5">
                                    <label class="text-xs font-bold text-gray-500 uppercase">{{ descriptionLabel }}</label>
                                    <div class="rounded-xl overflow-hidden border border-gray-300 focus-within:border-emerald-500 focus-within:ring-2 focus-within:ring-emerald-200 transition-all">
                                        <Editor v-model="form.description" editorStyle="height: 300px" placeholder="Tuliskan profil lengkap di sini..." />
                                    </div>
                                </div>

                            </div>
                        </template>
                    </Card>
                </div>

                <div class="lg:col-span-4 space-y-6">
                    
                    <Card class="shadow-sm border border-gray-200 rounded-2xl bg-gray-50">
                        <template #title><span class="text-base font-bold text-gray-800">Logo Unit</span></template>
                        <template #content>
                            <div class="flex flex-col items-center gap-4">
                                <div class="w-32 h-32 bg-white rounded-xl border border-gray-200 flex items-center justify-center overflow-hidden shadow-sm">
                                    <img v-if="organization?.logo_path" :src="'/storage/' + organization.logo_path" class="w-full h-full object-contain p-2">
                                    <i v-else class="pi pi-image text-4xl text-gray-300"></i>
                                </div>
                                <FileUpload mode="basic" name="logo" accept="image/*" :maxFileSize="2000000" 
                                            @select="onFileSelect" chooseLabel="Upload Logo" class="w-full" auto customUpload />
                                <small class="text-center text-gray-500 text-xs">Format: PNG, JPG. Max: 2MB.</small>
                            </div>
                        </template>
                    </Card>

                    <Card class="shadow-sm border border-gray-200 rounded-2xl">
                        <template #title><span class="text-base font-bold text-gray-800">Kontak & Lokasi</span></template>
                        <template #content>
                            <div class="grid grid-cols-1 gap-4"> <div class="space-y-1">
                                    <label class="text-xs font-bold text-gray-500 uppercase">Email</label>
                                    <InputText v-model="form.email" class="w-full p-inputtext-sm" />
                                </div>
                                <div class="space-y-1">
                                    <label class="text-xs font-bold text-gray-500 uppercase">Telepon / WA</label>
                                    <InputText v-model="form.phone" class="w-full p-inputtext-sm" />
                                </div>
                                <div class="grid grid-cols-2 gap-2">
                                    <div class="space-y-1">
                                        <label class="text-xs font-bold text-gray-500 uppercase">Latitude</label>
                                        <InputText v-model="form.latitude" placeholder="-3.xxxx" class="w-full p-inputtext-sm" />
                                    </div>
                                    <div class="space-y-1">
                                        <label class="text-xs font-bold text-gray-500 uppercase">Longitude</label>
                                        <InputText v-model="form.longitude" placeholder="102.xxxx" class="w-full p-inputtext-sm" />
                                    </div>
                                </div>
                                <div class="text-right">
                                    <Button label="Cari di Maps" icon="pi pi-map" text size="small" class="!p-0 !text-xs" @click="openMapHelper" />
                                </div>
                            </div>
                        </template>
                    </Card>

                    <div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-sm space-y-3">
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-gray-500 uppercase">Tanggal Berdiri</label>
                            <Calendar v-model="form.establishment_date" 
                                      dateFormat="dd/mm/yy" 
                                      showIcon 
                                      iconDisplay="input" 
                                      class="w-full" 
                                      inputClass="w-full p-inputtext-sm" 
                                      placeholder="Pilih Tanggal" />
                        </div>
                        
                        <div class="pt-4 border-t border-gray-100">
                            <Button type="submit" :label="isEdit ? 'Simpan Perubahan' : 'Buat Unit Baru'" 
                                    icon="pi pi-check" class="w-full !bg-gray-900 !border-gray-900" 
                                    :loading="form.processing" />
                            
                            <Link :href="route('organizations.index')">
                                <Button label="Batal" severity="secondary" text class="w-full mt-2" />
                            </Link>
                        </div>
                    </div>
                </div>

            </form>
        </div>
    </AppLayout>
</template>

<style scoped>
:deep(.p-editor-toolbar) {
    border: none !important;
    border-bottom: 1px solid #e5e7eb !important;
    background: #f9fafb;
}
:deep(.p-editor-content) {
    border: none !important;
}
</style>