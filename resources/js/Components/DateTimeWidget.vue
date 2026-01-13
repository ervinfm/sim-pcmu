<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

// State Reactif
const time = ref('');
const dateMasehi = ref('');
const dateHijri = ref('');
let intervalId;

// Fungsi Utama Update Waktu
const updateDateTime = () => {
    const now = new Date();

    // 1. JAM DIGITAL (Format 24 Jam: 13:45:00)
    time.value = now.toLocaleTimeString('id-ID', {
        hour12: false,
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    }).replace(/\./g, ':'); // Pastikan pemisah adalah titik dua

    // 2. TANGGAL MASEHI (Contoh: Kamis, 8 Januari 2026)
    dateMasehi.value = now.toLocaleDateString('id-ID', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    });

    // 3. TANGGAL HIJRIAH (Menggunakan Native Intl API)
    // Note: 'islamic-civil' adalah pendekatan aritmatika yang umum.
    // Muhammadiyah menggunakan Hisab Hakiki, namun selisihnya biasanya 0-1 hari
    // dari kalender sistem. Untuk widget web umum ini sudah cukup akurat.
    const hijriRaw = new Intl.DateTimeFormat('id-ID-u-ca-islamic-civil', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    }).format(now);

    // Menghapus kata "Tahun" jika browser menambahkannya, dan tambah suffix H
    dateHijri.value = hijriRaw.replace('Tahun', '').trim() + ' H';
};

// Lifecycle Hooks (Agar jam jalan terus dan mati saat pindah halaman)
onMounted(() => {
    updateDateTime(); // Jalankan langsung saat load
    intervalId = setInterval(updateDateTime, 1000); // Update setiap 1 detik
});

onUnmounted(() => {
    if (intervalId) clearInterval(intervalId); // Hapus interval agar tidak memory leak
});
</script>

<template>
    <div class="flex items-center gap-4 bg-white/10 backdrop-blur-sm border border-white/20 p-3 rounded-xl shadow-lg">
        <div class="flex flex-col">
            <div class="text-xl md:text-2xl font-mono font-bold text-white tracking-wider leading-none">
                {{ time }}
            </div>
            
            <div class="flex flex-col md:flex-row gap-1 md:gap-2 text-xs md:text-sm mt-1">
                <span class="text-emerald-100 font-medium">{{ dateMasehi }}</span>
                <span class="hidden md:inline text-white/50">|</span>
                <span class="text-yellow-300 font-bold tracking-wide">{{ dateHijri }}</span>
            </div>
        </div>
    </div>
</template>