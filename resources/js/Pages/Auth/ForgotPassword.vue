<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <GuestLayout>
        <Head title="Lupa Password" />

        <div class="mb-6">
            <h3 class="text-2xl font-bold text-gray-800">Lupa Password?</h3>
            <p class="text-gray-500 text-sm mt-2">
                Jangan khawatir. Masukkan alamat email Anda, dan kami akan mengirimkan link untuk mereset password.
            </p>
        </div>

        <div v-if="status" class="mb-6 font-medium text-sm text-emerald-600 bg-emerald-50 p-3 rounded-lg border border-emerald-200">
            <i class="pi pi-check-circle mr-2"></i>
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <div class="flex flex-col gap-2">
                <label for="email" class="font-semibold text-gray-700 text-sm">Email Terdaftar</label>
                <InputText id="email" type="email" class="w-full" 
                           v-model="form.email" required autofocus autocomplete="username"
                           placeholder="admin@pcm.com"
                           :class="{ 'p-invalid': form.errors.email }" />
                <small class="text-red-500" v-if="form.errors.email">{{ form.errors.email }}</small>
            </div>

            <Button label="Kirim Link Reset Password" type="submit" 
                    icon="pi pi-send" iconPos="right"
                    class="w-full !bg-emerald-600 !border-emerald-600 hover:!bg-emerald-700" 
                    :loading="form.processing" />

            <div class="text-center mt-4">
                <Link :href="route('login')" class="text-sm text-gray-500 hover:text-gray-800 flex items-center justify-center gap-2">
                    <i class="pi pi-arrow-left text-xs"></i> Kembali ke Halaman Login
                </Link>
            </div>
        </form>
    </GuestLayout>
</template>