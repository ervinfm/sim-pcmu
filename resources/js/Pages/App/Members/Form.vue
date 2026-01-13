<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

// Components
import InputText from 'primevue/inputtext';
import Dropdown from 'primevue/dropdown';
import Button from 'primevue/button';
import Calendar from 'primevue/calendar';
import FileUpload from 'primevue/fileupload';
import Textarea from 'primevue/textarea';
import SelectButton from 'primevue/selectbutton';
import Card from 'primevue/card';
import Divider from 'primevue/divider';

const props = defineProps({
    member: Object,
    prms: Array, // List PRM
});

const isEdit = computed(() => !!props.member);

const form = useForm({
    _method: isEdit.value ? 'PUT' : 'POST',
    organization_unit_id: props.member?.organization_unit_id || null,
    full_name: props.member?.full_name || '',
    nbm: props.member?.nbm || '',
    nik: props.member?.nik || '',
    gender: props.member?.gender || 'L',
    birth_place: props.member?.birth_place || '',
    birth_date: props.member?.birth_date ? new Date(props.member.birth_date) : null,
    phone_number: props.member?.phone_number || '',
    job: props.member?.job || '',
    last_education: props.member?.last_education || 'SMA',
    education_institution: props.member?.education_institution || '',
    
    // --- UPDATE ALAMAT LENGKAP ---
    address: props.member?.address || '',
    village: props.member?.village || '',   // Baru
    district: props.member?.district || '', // Baru
    regency: props.member?.regency || '',   // Baru
    
    status: props.member?.status || 'ACTIVE',
    photo: null,
});

const onFileSelect = (event) => {
    form.photo = event.files[0];
};

const submit = () => {
    const routeName = isEdit.value ? route('members.update', props.member.id) : route('members.store');
    form.post(routeName, { forceFormData: true });
};

// Opsi Data
const genderOptions = [
    { label: 'Laki-laki', value: 'L' },
    { label: 'Perempuan', value: 'P' }
];

const statusOptions = [
    { label: 'Aktif', value: 'ACTIVE', class: 'text-green-600' },
    { label: 'Tidak Aktif', value: 'INACTIVE', class: 'text-gray-500' },
    { label: 'Pindah', value: 'MOVED', class: 'text-orange-500' },
    { label: 'Meninggal', value: 'DECEASED', class: 'text-red-500' },
];

const educationOptions = ['SD', 'SMP', 'SMA', 'DIPLOMA', 'S1', 'S2', 'S3', 'TIDAK_SEKOLAH'];
</script>

<template>
    <Head :title="isEdit ? 'Edit Anggota' : 'Tambah Anggota'" />

    <AppLayout>
        <div class="max-w-6xl mx-auto">
            
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">{{ isEdit ? 'Edit Data Anggota' : 'Registrasi Anggota Baru' }}</h1>
                    <p class="text-gray-500 text-sm">Lengkapi data diri anggota persyarikatan.</p>
                </div>
                <Link :href="route('members.index')">
                    <Button label="Batal" icon="pi pi-times" severity="secondary" text />
                </Link>
            </div>

            <form @submit.prevent="submit">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    
                    <div class="lg:col-span-2 space-y-6">
                        
                        <Card class="border border-gray-100 shadow-sm">
                            <template #title><span class="text-lg text-emerald-800 font-bold">Identitas Diri</span></template>
                            <template #content>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                    
                                    <div class="md:col-span-2">
                                        <label class="font-semibold text-gray-700 block mb-2">Nama Lengkap (Tanpa Gelar) <span class="text-red-500">*</span></label>
                                        <InputText v-model="form.full_name" class="w-full p-inputtext-lg" placeholder="Contoh: Budi Santoso" :class="{ 'p-invalid': form.errors.full_name }" />
                                        <small class="text-red-500">{{ form.errors.full_name }}</small>
                                    </div>

                                    <div>
                                        <label class="font-semibold text-gray-700 block mb-2">NIK (KTP) <span class="text-red-500">*</span></label>
                                        <InputText v-model="form.nik" class="w-full" placeholder="16 Digit Angka" maxlength="16" />
                                        <small class="text-red-500">{{ form.errors.nik }}</small>
                                    </div>

                                    <div>
                                        <label class="font-semibold text-gray-700 block mb-2">Jenis Kelamin</label>
                                        <SelectButton v-model="form.gender" :options="genderOptions" optionLabel="label" optionValue="value" class="w-full" />
                                    </div>

                                    <div>
                                        <label class="font-semibold text-gray-700 block mb-2">Tempat Lahir</label>
                                        <InputText v-model="form.birth_place" class="w-full" />
                                    </div>

                                    <div>
                                        <label class="font-semibold text-gray-700 block mb-2">Tanggal Lahir</label>
                                        <Calendar v-model="form.birth_date" dateFormat="dd/mm/yy" showIcon class="w-full" />
                                    </div>

                                </div>
                            </template>
                        </Card>

                        <Card class="border border-gray-100 shadow-sm">
                            <template #title><span class="text-lg text-emerald-800 font-bold">Keanggotaan & Pendidikan</span></template>
                            <template #content>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                    
                                    <div>
                                        <label class="font-semibold text-gray-700 block mb-2">Asal Ranting/Cabang (PRM/PCM) <span class="text-red-500">*</span></label>
                                        <Dropdown v-model="form.organization_unit_id" :options="prms" optionLabel="name" optionValue="id" 
                                                  filter placeholder="Pilih Ranting/Cabang Domisili" class="w-full" />
                                        <small class="text-red-500">{{ form.errors.organization_unit_id }}</small>
                                    </div>

                                    <div>
                                        <label class="font-semibold text-gray-700 block mb-2">NBM (Nomor Baku)</label>
                                        <InputText v-model="form.nbm" class="w-full" placeholder="Jika ada" />
                                    </div>

                                    <div>
                                        <label class="font-semibold text-gray-700 block mb-2">Pekerjaan Utama</label>
                                        <InputText v-model="form.job" class="w-full" placeholder="PNS / Wiraswasta / Tani" />
                                    </div>

                                    <div>
                                        <label class="font-semibold text-gray-700 block mb-2">Pendidikan Terakhir</label>
                                        <Dropdown v-model="form.last_education" :options="educationOptions" class="w-full" />
                                    </div>

                                    <div class="md:col-span-2">
                                        <label class="font-semibold text-gray-700 block mb-2">Institusi Pendidikan</label>
                                        <InputText v-model="form.education_institution" class="w-full" placeholder="Nama Kampus / Sekolah Terakhir" />
                                    </div>

                                </div>
                            </template>
                        </Card>

                    </div>

                    <div class="space-y-6">
                        
                        <Card class="border border-gray-100 shadow-sm">
                            <template #title><span class="text-base font-bold">Foto Profil</span></template>
                            <template #content>
                                <div class="flex flex-col items-center">
                                    <div class="w-32 h-32 rounded-full overflow-hidden bg-gray-100 border-4 border-white shadow-md mb-4 relative">
                                        <img v-if="member?.photo_path" :src="'/storage/' + member.photo_path" class="w-full h-full object-cover">
                                        <div v-else class="w-full h-full flex items-center justify-center text-gray-300">
                                            <i class="pi pi-camera text-4xl"></i>
                                        </div>
                                    </div>
                                    <FileUpload mode="basic" name="photo" accept="image/*" :maxFileSize="2000000" 
                                                @select="onFileSelect" chooseLabel="Pilih Foto" 
                                                class="p-button-sm p-button-outlined w-full" />
                                </div>
                            </template>
                        </Card>

                        <Card class="border border-gray-100 shadow-sm">
                            <template #title><span class="text-base font-bold">Status & Kontak</span></template>
                            <template #content>
                                <div class="space-y-4">
                                    <div>
                                        <label class="font-semibold text-gray-700 block mb-2">Status Anggota</label>
                                        <Dropdown v-model="form.status" :options="statusOptions" optionLabel="label" optionValue="value" class="w-full">
                                            <template #option="slotProps">
                                                <span :class="slotProps.option.class">{{ slotProps.option.label }}</span>
                                            </template>
                                        </Dropdown>
                                    </div>

                                    <Divider />

                                    <div>
                                        <label class="font-semibold text-gray-700 block mb-2">No. HP / WhatsApp</label>
                                        <div class="p-inputgroup">
                                            <span class="p-inputgroup-addon me-2"><i class="pi pi-whatsapp"></i></span>
                                            <InputText v-model="form.phone_number" placeholder="08..." />
                                        </div>
                                    </div>

                                    <div>
                                        <label class="font-semibold text-gray-700 block mb-2">Alamat Jalan / RT / RW</label>
                                        <Textarea v-model="form.address" rows="2" class="w-full" placeholder="Jalan, No Rumah, RT/RW..." />
                                        <small class="text-red-500">{{ form.errors.address }}</small>
                                    </div>

                                    <div class="grid grid-cols-2 gap-3">
                                        <div class="col-span-2">
                                            <label class="text-xs font-bold text-gray-500 uppercase">Desa / Kelurahan</label>
                                            <InputText v-model="form.village" class="w-full p-inputtext-sm" placeholder="Nama Desa" />
                                            <small class="text-red-500">{{ form.errors.village }}</small>
                                        </div>
                                        <div>
                                            <label class="text-xs font-bold text-gray-500 uppercase">Kecamatan</label>
                                            <InputText v-model="form.district" class="w-full p-inputtext-sm" />
                                            <small class="text-red-500">{{ form.errors.district }}</small>
                                        </div>
                                        <div>
                                            <label class="text-xs font-bold text-gray-500 uppercase">Kabupaten</label>
                                            <InputText v-model="form.regency" class="w-full p-inputtext-sm" />
                                            <small class="text-red-500">{{ form.errors.regency }}</small>
                                        </div>
                                    </div>

                                </div>
                            </template>
                        </Card>

                        <div class="bg-white p-4 rounded-xl shadow border border-gray-100 sticky top-24">
                            <Button label="Simpan Data" type="submit" icon="pi pi-check" iconPos="right" 
                                    class="w-full !bg-emerald-600 !border-emerald-600 hover:!bg-emerald-700" 
                                    :loading="form.processing" />
                        </div>

                    </div>

                </div>
            </form>
        </div>
    </AppLayout>
</template>

<style scoped>
:deep(.p-selectbutton .p-button.p-highlight) {
    background-color: #10b981;
    border-color: #10b981;
}
</style>