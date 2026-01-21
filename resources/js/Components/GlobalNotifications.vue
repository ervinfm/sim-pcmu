<script setup>
import { watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { useToast } from 'primevue/usetoast';
import Toast from 'primevue/toast';

const page = usePage();
const toast = useToast();

// Watcher untuk memantau perubahan pada Flash Message Inertia
watch(() => page.props.flash, (flash) => {
    if (!flash) return;

    // 1. Success Message
    if (flash.success) {
        toast.add({ 
            severity: 'success', 
            summary: 'Berhasil', 
            detail: flash.success, 
            life: 4000,
            group: 'br' // Bottom Right (Opsional, hapus jika ingin default Top Right)
        });
    }

    // 2. Error Message
    if (flash.error) {
        toast.add({ 
            severity: 'error', 
            summary: 'Gagal', 
            detail: flash.error, 
            life: 5000,
            group: 'br'
        });
    }

    // 3. Warning Message
    if (flash.warning) {
        toast.add({ 
            severity: 'warn', 
            summary: 'Peringatan', 
            detail: flash.warning, 
            life: 4000,
            group: 'br'
        });
    }

    // 4. Info Message
    if (flash.info) {
        toast.add({ 
            severity: 'info', 
            summary: 'Informasi', 
            detail: flash.info, 
            life: 3000,
            group: 'br'
        });
    }
}, { deep: true, immediate: true });
</script>

<template>
    <Toast group="br" />
    
    <Toast /> 
</template>