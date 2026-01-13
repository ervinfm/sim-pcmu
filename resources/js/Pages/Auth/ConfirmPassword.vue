<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import Password from 'primevue/password';
import Button from 'primevue/button';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    password: '',
});

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => form.reset(),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Konfirmasi Password" />

        <div class="mb-6">
            <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center mb-4 mx-auto text-yellow-600">
                <i class="pi pi-lock text-xl"></i>
            </div>
            <h3 class="text-2xl font-bold text-gray-800 text-center">Area Dilindungi</h3>
            <p class="text-gray-500 text-sm mt-2 text-center">
                Ini adalah area aman aplikasi. Harap konfirmasi password Anda sebelum melanjutkan.
            </p>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <div class="flex flex-col gap-2">
                <label for="password" class="font-semibold text-gray-700 text-sm">Password Anda</label>
                <Password id="password" v-model="form.password" 
                          class="w-full" inputClass="w-full" :feedback="false" toggleMask autofocus
                          required autocomplete="current-password"
                          placeholder="Masukkan password"
                          :class="{ 'p-invalid': form.errors.password }" />
                <small class="text-red-500" v-if="form.errors.password">{{ form.errors.password }}</small>
            </div>

            <Button label="Konfirmasi & Lanjutkan" type="submit" 
                    icon="pi pi-check" iconPos="right"
                    class="w-full !bg-emerald-600 !border-emerald-600 hover:!bg-emerald-700" 
                    :class="{ 'opacity-25': form.processing }" 
                    :disabled="form.processing" />
        </form>
    </GuestLayout>
</template>