<script setup>
import { computed } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import Button from 'primevue/button';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    status: {
        type: String,
    },
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(() => props.status === 'verification-link-sent');
</script>

<template>
    <GuestLayout>
        <Head title="Verifikasi Email" />

        <div class="mb-6">
            <h3 class="text-2xl font-bold text-gray-800">Verifikasi Email Anda</h3>
            <p class="text-gray-500 text-sm mt-2 leading-relaxed">
                Terima kasih telah mendaftar! Sebelum memulai, mohon verifikasi alamat email Anda dengan mengklik tautan yang baru saja kami kirimkan ke email Anda.
            </p>
        </div>

        <div v-if="verificationLinkSent" class="mb-6 font-medium text-sm text-emerald-600 bg-emerald-50 p-4 rounded-lg border border-emerald-200 flex items-start gap-2">
            <i class="pi pi-check-circle mt-0.5"></i>
            <span>Tautan verifikasi baru telah dikirim ke alamat email yang Anda berikan saat pendaftaran.</span>
        </div>

        <form @submit.prevent="submit">
            <div class="space-y-4">
                <Button label="Kirim Ulang Email Verifikasi" type="submit" 
                        icon="pi pi-envelope" iconPos="right"
                        class="w-full !bg-emerald-600 !border-emerald-600 hover:!bg-emerald-700" 
                        :class="{ 'opacity-25': form.processing }" 
                        :disabled="form.processing" />

                <div class="flex justify-between items-center mt-6">
                    <Link :href="route('profile.edit')" class="text-sm text-gray-600 hover:text-gray-900 underline rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500">
                        Edit Profil
                    </Link>

                    <Link :href="route('logout')" method="post" as="button" class="text-sm text-red-600 hover:text-red-800 underline rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        Keluar (Logout)
                    </Link>
                </div>
            </div>
        </form>
    </GuestLayout>
</template>