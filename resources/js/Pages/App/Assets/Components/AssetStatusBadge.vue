<script setup>
import { computed } from 'vue';
import Tag from 'primevue/tag';

const props = defineProps({
    status: {
        type: String,
        required: true
    }
});

// Mapping Status ke Severity (Warna) PrimeVue
const severity = computed(() => {
    switch (props.status) {
        case 'ACTIVE': return 'success';        // Hijau (Tersedia)
        case 'BORROWED': return 'info';         // Biru (Sedang Dipinjam)
        case 'MAINTENANCE': return 'warning';   // Kuning (Perbaikan)
        case 'BROKEN': 
        case 'HEAVILY_DAMAGED': return 'danger';// Merah (Rusak)
        case 'WRITE_OFF': return 'secondary';   // Abu-abu (Dihapus)
        case 'LOST': return 'danger';           // Merah (Hilang)
        default: return 'contrast';             // Hitam (Default)
    }
});

// Mapping Label (Opsional: Jika ingin translate ke Bhs Indonesia)
const label = computed(() => {
    const map = {
        'ACTIVE': 'Tersedia',
        'BORROWED': 'Dipinjam',
        'MAINTENANCE': 'Perbaikan',
        'BROKEN': 'Rusak',
        'WRITE_OFF': 'Dihapuskan',
        'LOST': 'Hilang',
        'GOOD': 'Baik',
        'SLIGHTLY_DAMAGED': 'Rusak Ringan',
        'HEAVILY_DAMAGED': 'Rusak Berat'
    };
    return map[props.status] || props.status;
});
</script>

<template>
    <Tag :value="label" :severity="severity" rounded class="uppercase text-[10px] tracking-wider font-bold" />
</template>