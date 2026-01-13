<script setup>
import { computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

// Components
import Card from 'primevue/card';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import Button from 'primevue/button';
import FileUpload from 'primevue/fileupload';
import Message from 'primevue/message';
import Badge from 'primevue/badge';
import Tag from 'primevue/tag';
import Divider from 'primevue/divider';

const props = defineProps({ user: Object });

const form = useForm({
    _method: 'PUT',
    name: props.user.name,
    email: props.user.email,
    current_password: '',
    new_password: '',
    new_password_confirmation: '',
    photo: null,
});

const onFileSelect = (e) => {
    form.photo = e.files[0];
};

const submit = () => {
    form.post(route('profile.update'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('current_password', 'new_password', 'new_password_confirmation', 'photo');
        },
    });
};

const userInitials = computed(() => {
    return props.user.name
        .split(' ')
        .map((n) => n[0])
        .join('')
        .substring(0, 2)
        .toUpperCase();
});
</script>

<template>
    <Head title="Pengaturan Akun" />
    <AppLayout>
        <div class="max-w-6xl mx-auto">
            
            <div class="mb-8">
                <h1 class="text-3xl font-black text-gray-800 tracking-tight">Account Control</h1>
                <p class="text-gray-500 mt-1">Kelola keamanan, privasi, dan preferensi akun Anda.</p>
            </div>

            <form @submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-12 gap-8 mt-6">
                
                <div class="lg:col-span-4 space-y-6">
                    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden relative group">
                        
                        <div class="h-24 bg-gradient-to-br from-slate-800 to-gray-900 relative overflow-hidden">
                            <div class="absolute inset-0 opacity-20" style="background-image: radial-gradient(#fff 1px, transparent 1px); background-size: 16px 16px;"></div>
                        </div>

                        <div class="px-6 pb-6 relative text-center -mt-12">
                            <div class="relative inline-block group/avatar">
                                <div class="w-28 h-28 rounded-full border-4 border-white shadow-lg overflow-hidden bg-gray-50 relative z-10">
                                    <img v-if="user.photo" :src="'/storage/' + user.photo" class="w-full h-full object-cover transition-transform duration-500 group-hover/avatar:scale-110">
                                    <div v-else class="w-full h-full flex items-center justify-center bg-emerald-100 text-emerald-600 text-3xl font-bold tracking-widest">
                                        {{ userInitials }}
                                    </div>
                                    
                                    <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover/avatar:opacity-100 transition duration-300 backdrop-blur-sm cursor-pointer">
                                        <i class="pi pi-camera text-white text-2xl"></i>
                                    </div>
                                </div>
                                
                                <div class="absolute bottom-0 right-0 z-20">
                                    <FileUpload mode="basic" name="photo" accept="image/*" :maxFileSize="1000000" 
                                                @select="onFileSelect" auto customUpload 
                                                chooseLabel="" chooseIcon="pi pi-pencil" 
                                                class="p-button-rounded p-button-sm !w-8 !h-8 !p-0" />
                                </div>
                            </div>

                            <h2 class="text-xl font-bold text-gray-800 mt-4">{{ user.name }}</h2>
                            <div class="flex justify-center items-center gap-2 mt-1">
                                <span class="text-sm text-gray-500">{{ user.email }}</span>
                                <i class="pi pi-verified text-blue-500 text-xs" title="Verified Account"></i>
                            </div>

                            <div class="mt-4 flex justify-center gap-2">
                                <Tag :value="user.role" severity="contrast" class="uppercase text-[10px] tracking-wider" />
                                <Tag value="ACTIVE" severity="success" class="uppercase text-[10px] tracking-wider" />
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 rounded-2xl p-5 border border-dashed border-gray-200">
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3">System Information</h4>
                        <ul class="space-y-3 text-sm">
                            <li class="flex justify-between">
                                <span class="text-gray-500">User ID</span>
                                <span class="font-mono font-medium text-gray-700">#{{ String(user.id).padStart(6, '0') }}</span>
                            </li>
                            <li class="flex justify-between">
                                <span class="text-gray-500">Bergabung</span>
                                <span class="font-medium text-gray-700">{{ new Date(user.created_at).toLocaleDateString() }}</span>
                            </li>
                            <li class="flex justify-between">
                                <span class="text-gray-500">Last Login</span>
                                <span class="font-medium text-gray-700">Just Now</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="lg:col-span-8">
                    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden p-8">
                        
                        <div class="mb-8">
                            <div class="flex items-center gap-3 mb-6">
                                <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600">
                                    <i class="pi pi-id-card text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-gray-800">Informasi Dasar</h3>
                                    <p class="text-xs text-gray-500">Perbarui nama tampilan dan alamat email Anda.</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <label class="text-sm font-semibold text-gray-700">Nama Lengkap</label>
                                    <span class="p-input-icon-left w-full">
                                        <i class="pi pi-user ms-2" />
                                        <InputText v-model="form.name" class="w-full" placeholder="Nama Anda" />
                                    </span>
                                    <small class="text-red-500" v-if="form.errors.name">{{ form.errors.name }}</small>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-sm font-semibold text-gray-700">Alamat Email</label>
                                    <span class="p-input-icon-left w-full">
                                        <i class="pi pi-envelope ms-2" />
                                        <InputText v-model="form.email" class="w-full" placeholder="email@domain.com" />
                                    </span>
                                    <small class="text-red-500" v-if="form.errors.email">{{ form.errors.email }}</small>
                                </div>
                            </div>
                        </div>

                        <Divider />

                        <div class="mb-8 mt-8">
                            <div class="flex items-center gap-3 mb-6">
                                <div class="w-10 h-10 rounded-xl bg-red-50 flex items-center justify-center text-red-600">
                                    <i class="pi pi-lock text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-gray-800">Keamanan & Password</h3>
                                    <p class="text-xs text-gray-500">Pastikan akun Anda tetap aman dengan password yang kuat.</p>
                                </div>
                            </div>

                            <div class="bg-red-50/50 rounded-xl p-6 border border-red-100/50 space-y-6">
                                <div class="space-y-2">
                                    <label class="text-sm font-semibold text-gray-700">Password Saat Ini</label>
                                    <Password v-model="form.current_password" toggleMask class="w-full" inputClass="w-full border-gray-300" :feedback="false" placeholder="••••••••" />
                                    <small class="text-red-500" v-if="form.errors.current_password">{{ form.errors.current_password }}</small>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="space-y-2">
                                        <label class="text-sm font-semibold text-gray-700">Password Baru</label>
                                        <Password v-model="form.new_password" toggleMask class="w-full" inputClass="w-full border-gray-300" 
                                                  promptLabel="Masukkan password baru" weakLabel="Lemah" mediumLabel="Sedang" strongLabel="Kuat" placeholder="••••••••" />
                                        <small class="text-red-500" v-if="form.errors.new_password">{{ form.errors.new_password }}</small>
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-sm font-semibold text-gray-700">Konfirmasi Password</label>
                                        <Password v-model="form.new_password_confirmation" toggleMask class="w-full" inputClass="w-full border-gray-300" :feedback="false" placeholder="••••••••" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                            <span class="text-xs text-gray-400 italic">Terakhir diperbarui: {{ new Date(user.updated_at).toLocaleDateString() }}</span>
                            <div class="flex gap-3">
                                <Button label="Reset" type="button" severity="secondary" text @click="form.reset()" />
                                <Button label="Simpan Perubahan" type="submit" icon="pi pi-check" 
                                        class="!bg-emerald-600 !border-emerald-600 hover:!bg-emerald-700 px-6" 
                                        :loading="form.processing" />
                            </div>
                        </div>

                    </div>
                </div>

            </form>
        </div>
    </AppLayout>
</template>

<style scoped>
:deep(.p-password-input) {
    width: 100%;
}
</style>