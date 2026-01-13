<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import Button from 'primevue/button';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    email: {
        type: String,
        required: true,
    },
    token: {
        type: String,
        required: true,
    },
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Reset Password" />

        <div class="mb-6">
            <h3 class="text-2xl font-bold text-gray-800">Buat Password Baru</h3>
            <p class="text-gray-500 text-sm mt-2">
                Silakan buat password baru yang aman untuk akun Anda.
            </p>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <div class="flex flex-col gap-2">
                <label for="email" class="font-semibold text-gray-700 text-sm">Email</label>
                <InputText id="email" type="email" class="w-full bg-gray-100" 
                           v-model="form.email" required autofocus autocomplete="username" readonly 
                           :class="{ 'p-invalid': form.errors.email }" />
                <small class="text-red-500" v-if="form.errors.email">{{ form.errors.email }}</small>
            </div>

            <div class="flex flex-col gap-2">
                <label for="password" class="font-semibold text-gray-700 text-sm">Password Baru</label>
                <Password id="password" v-model="form.password" 
                          class="w-full" inputClass="w-full" toggleMask
                          required autocomplete="new-password"
                          placeholder="Minimal 8 karakter"
                          :class="{ 'p-invalid': form.errors.password }" 
                          promptLabel="Masukkan password" weakLabel="Lemah" mediumLabel="Sedang" strongLabel="Kuat" />
                <small class="text-red-500" v-if="form.errors.password">{{ form.errors.password }}</small>
            </div>

            <div class="flex flex-col gap-2">
                <label for="password_confirmation" class="font-semibold text-gray-700 text-sm">Ulangi Password Baru</label>
                <Password id="password_confirmation" v-model="form.password_confirmation" 
                          class="w-full" inputClass="w-full" toggleMask :feedback="false"
                          required autocomplete="new-password"
                          placeholder="Ketik ulang password"
                          :class="{ 'p-invalid': form.errors.password_confirmation }" />
                <small class="text-red-500" v-if="form.errors.password_confirmation">{{ form.errors.password_confirmation }}</small>
            </div>

            <Button label="Simpan Password Baru" type="submit" 
                    icon="pi pi-check-circle" iconPos="right"
                    class="w-full !bg-emerald-600 !border-emerald-600 hover:!bg-emerald-700" 
                    :loading="form.processing" />
        </form>
    </GuestLayout>
</template>