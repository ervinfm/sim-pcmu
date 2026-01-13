<script setup>
import Checkbox from 'primevue/checkbox';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import Button from 'primevue/button';
import Message from 'primevue/message';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Log in" />

        <div class="mb-6">
            <h3 class="text-2xl font-bold text-gray-800">Selamat Datang Kembali</h3>
            <p class="text-gray-500 text-sm">Masukan kredensial Anda untuk mengakses dashboard.</p>
        </div>

        <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <div class="flex flex-col gap-2">
                <label for="email" class="font-semibold text-gray-700 text-sm">Email</label>
                <InputText id="email" type="email" class="w-full" 
                           v-model="form.email" required autofocus autocomplete="username" 
                           placeholder="admin@pcm.com"
                           :class="{ 'p-invalid': form.errors.email }" />
                <small class="text-red-500" v-if="form.errors.email">{{ form.errors.email }}</small>
            </div>

            <div class="flex flex-col gap-2">
                <label for="password" class="font-semibold text-gray-700 text-sm">Password</label>
                <Password id="password" v-model="form.password" 
                          class="w-full" inputClass="w-full" :feedback="false" toggleMask
                          required autocomplete="current-password" 
                          placeholder="••••••••"
                          :class="{ 'p-invalid': form.errors.password }" />
                <small class="text-red-500" v-if="form.errors.password">{{ form.errors.password }}</small>
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <Checkbox v-model="form.remember" binary inputId="remember" />
                    <label for="remember" class="text-sm text-gray-600 cursor-pointer">Ingat Saya</label>
                </div>
                
                <Link v-if="canResetPassword" :href="route('password.request')" 
                      class="text-sm text-emerald-600 hover:text-emerald-800 font-medium">
                    Lupa Password?
                </Link>
            </div>

            <Button label="Masuk ke Dashboard" type="submit" 
                    icon="pi pi-sign-in" iconPos="right"
                    class="w-full !bg-emerald-600 !border-emerald-600 hover:!bg-emerald-700" 
                    :loading="form.processing" />

            <div class="text-center mt-4">
                <Link href="/" class="text-sm text-gray-500 hover:text-gray-800 flex items-center justify-center gap-2">
                    <i class="pi pi-arrow-left text-xs"></i> Kembali ke Website Utama
                </Link>
            </div>
        </form>
    </GuestLayout>
</template>