<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

// PrimeVue Components
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import Dropdown from 'primevue/dropdown';
import Checkbox from 'primevue/checkbox';
import Button from 'primevue/button';
import Card from 'primevue/card';
import Tag from 'primevue/tag';

const props = defineProps({
    user: Object,
    groupedUnits: Array, // Data Unit yang sudah dikelompokkan
});

const isEdit = computed(() => !!props.user);

const form = useForm({
    name: props.user?.name || '',
    username: props.user?.username || '',
    email: props.user?.email || '',
    role: props.user?.role || 'member',
    organization_unit_id: props.user?.organization_unit_id || null,
    is_active: props.user?.is_active ?? true,
    password: '',
    password_confirmation: '',
});

// Pilihan Role disederhanakan agar mudah dipahami admin
const roleOptions = [
    { label: 'SUPER ADMIN (Pimpinan Cabang)', value: 'super_admin', desc: 'Akses penuh ke seluruh sistem.' },
    { label: 'ADMIN RANTING (PRM)', value: 'admin_prm', desc: 'Mengelola data anggota & kegiatan di 1 Ranting.' },
    { label: 'ADMIN AUM / ORTOM', value: 'admin_aum', desc: 'Mengelola Sekolah, Masjid, atau Ortom tertentu.' },
    { label: 'MEMBER (Anggota Biasa)', value: 'member', desc: 'Hanya akses profil pribadi.' },
];

const submit = () => {
    const routeName = isEdit.value ? route('users.update', props.user.id) : route('users.store');
    isEdit.value ? form.put(routeName) : form.post(routeName);
};
</script>

<template>
    <Head :title="isEdit ? 'Edit User' : 'Tambah User'" />

    <AppLayout>
        <div class="max-w-4xl mx-auto space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-black text-gray-800">{{ isEdit ? 'Edit Pengguna' : 'Tambah Pengguna Baru' }}</h1>
                    <p class="text-gray-500 text-sm">Pastikan hak akses diberikan sesuai tanggung jawab.</p>
                </div>
                <Link :href="route('users.index')">
                    <Button label="Kembali" icon="pi pi-arrow-left" text severity="secondary" />
                </Link>
            </div>

            <form @submit.prevent="submit">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    
                    <div class="md:col-span-2 space-y-6">
                        <Card class="border border-gray-100 shadow-sm rounded-xl">
                            <template #title><span class="text-lg font-bold">Informasi Akun</span></template>
                            <template #content>
                                <div class="space-y-4">
                                    <div class="flex flex-col gap-2">
                                        <label class="font-bold text-sm text-gray-700">Nama Lengkap</label>
                                        <InputText v-model="form.name" placeholder="Nama Admin" class="w-full" />
                                        <small class="text-red-500">{{ form.errors.name }}</small>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div class="flex flex-col gap-2">
                                            <label class="font-bold text-sm text-gray-700">Email</label>
                                            <InputText v-model="form.email" placeholder="email@contoh.com" class="w-full" />
                                            <small class="text-red-500">{{ form.errors.email }}</small>
                                        </div>
                                        <div class="flex flex-col gap-2">
                                            <label class="font-bold text-sm text-gray-700">Username</label>
                                            <InputText v-model="form.username" placeholder="opsional" class="w-full" />
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div class="flex flex-col gap-2">
                                            <label class="font-bold text-sm text-gray-700">Password</label>
                                            <Password v-model="form.password" :feedback="false" toggleMask class="w-full" inputClass="w-full" placeholder="******" />
                                            <small class="text-red-500">{{ form.errors.password }}</small>
                                        </div>
                                        <div class="flex flex-col gap-2">
                                            <label class="font-bold text-sm text-gray-700">Konfirmasi</label>
                                            <Password v-model="form.password_confirmation" :feedback="false" toggleMask class="w-full" inputClass="w-full" placeholder="******" />
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </Card>
                    </div>

                    <div class="space-y-6">
                        <Card class="border border-gray-100 shadow-sm rounded-xl bg-slate-50">
                            <template #title><span class="text-lg font-bold text-slate-800">Hak Akses</span></template>
                            <template #content>
                                <div class="space-y-5">
                                    <div class="flex flex-col gap-2">
                                        <label class="font-bold text-sm text-gray-700">Level Admin</label>
                                        <Dropdown v-model="form.role" :options="roleOptions" optionLabel="label" optionValue="value" class="w-full">
                                            <template #option="slotProps">
                                                <div class="flex flex-col py-1">
                                                    <span class="font-bold text-sm">{{ slotProps.option.label }}</span>
                                                    <span class="text-xs text-gray-500">{{ slotProps.option.desc }}</span>
                                                </div>
                                            </template>
                                        </Dropdown>
                                    </div>

                                    <div v-if="form.role !== 'super_admin'" class="flex flex-col gap-2 animate-fade-in">
                                        <label class="font-bold text-sm text-gray-700">Unit Penugasan</label>
                                        
                                        <Dropdown v-model="form.organization_unit_id" 
                                                  :options="groupedUnits" 
                                                  optionLabel="name" 
                                                  optionValue="id" 
                                                  optionGroupLabel="label" 
                                                  optionGroupChildren="items"
                                                  filter placeholder="Pilih Unit Kerja..." class="w-full" showClear>
                                            <template #option="slotProps">
                                                <div class="flex items-center gap-2">
                                                    <Tag :value="slotProps.option.type" severity="secondary" class="!text-[9px] !px-1" />
                                                    <span>{{ slotProps.option.name }}</span>
                                                </div>
                                            </template>
                                        </Dropdown>
                                        
                                        <small class="text-blue-600 bg-blue-50 p-2 rounded text-xs border border-blue-100">
                                            User ini hanya bisa mengelola data milik unit yang dipilih di atas.
                                        </small>
                                        <small class="text-red-500">{{ form.errors.organization_unit_id }}</small>
                                    </div>

                                    <div v-else class="bg-emerald-100 p-3 rounded-lg border border-emerald-200 text-emerald-800 text-sm">
                                        <i class="pi pi-check-circle"></i> <strong>Super Admin</strong> memiliki akses penuh ke seluruh data unit organisasi.
                                    </div>

                                    <div class="flex items-center gap-2 pt-2 border-t border-gray-200">
                                        <Checkbox v-model="form.is_active" :binary="true" inputId="active" />
                                        <label for="active" class="cursor-pointer text-gray-700 text-sm">Akun Aktif</label>
                                    </div>
                                </div>
                            </template>
                        </Card>

                        <Button :label="isEdit ? 'Simpan Perubahan' : 'Buat Akun'" type="submit" 
                                icon="pi pi-save" class="w-full !bg-gray-900 !border-gray-900 font-bold shadow-lg" 
                                :loading="form.processing" />
                    </div>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

<style>
.animate-fade-in { animation: fadeIn 0.3s ease-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(-5px); } to { opacity: 1; transform: translateY(0); } }
</style>