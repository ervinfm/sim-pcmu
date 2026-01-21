<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import ProgressBar from 'primevue/progressbar';
import axios from 'axios';

const page = usePage();

// --- KONFIGURASI ---
// Ambil durasi sesi dari server (menit), konversi ke milidetik
// Default 120 menit jika config gagal load
const SESSION_LIFETIME_MINUTES = page.props.session?.lifetime || 120; 
const SESSION_TIMEOUT_MS = SESSION_LIFETIME_MINUTES * 60 * 1000;

// Munculkan warning 5 menit sebelum habis (300.000 ms)
const WARNING_THRESHOLD_MS = 2 * 60 * 1000; 

// Key untuk localStorage (Sinkronisasi antar tab)
const STORAGE_KEY = 'app_last_activity';

// --- STATE ---
const visible = ref(false);
const timeLeft = ref(0);
const progressValue = ref(100);
const lastCheckTime = ref(Date.now());
let timerInterval = null;

// --- LOGIC ---

// 1. Update waktu aktivitas terakhir (Dipanggil saat user klik/ketik/pindah halaman)
const recordActivity = () => {
    const now = Date.now();
    localStorage.setItem(STORAGE_KEY, now.toString());
    
    // Jika warning sedang muncul tapi user aktif di tab lain, tutup warning ini
    if (visible.value) {
        visible.value = false;
    }
};

// 2. Cek status sesi (Dijalankan setiap 1 detik)
const checkSessionStatus = () => {
    const now = Date.now();

    if (now - lastCheckTime.value > 10000) {
        // Paksa cek ulang aktivitas dari storage agar sinkron
        // Atau langsung logout jika sudah expired jauh
    }
    
    lastCheckTime.value = now; // Update waktu cek terakhir

    // Ambil waktu aktivitas terakhir dari localStorage (bisa dari tab manapun)
    const lastActivity = parseInt(localStorage.getItem(STORAGE_KEY) || now);
    
    // Hitung kapan sesi habis
    const expireTime = lastActivity + SESSION_TIMEOUT_MS;
    const timeRemaining = expireTime - now;

    // Simpan sisa waktu untuk display
    timeLeft.value = timeRemaining;

    // A. Jika waktu habis -> Logout Paksa
    if (timeRemaining <= 0) {
        clearInterval(timerInterval);
        logout();
        return;
    }

    // B. Jika masuk masa kritis (Warning) -> Tampilkan Modal
    if (timeRemaining <= WARNING_THRESHOLD_MS) {
        if (!visible.value) {
            visible.value = true;
        }
        // Hitung persentase progress bar (sisa waktu / 5 menit)
        progressValue.value = (timeRemaining / WARNING_THRESHOLD_MS) * 100;
    } else {
        // Jika waktu masih panjang (misal diperpanjang dari tab lain), sembunyikan modal
        if (visible.value) {
            visible.value = false;
        }
    }
};

// 3. Format Waktu (MM:SS) untuk display
const formattedTimeLeft = computed(() => {
    const totalSeconds = Math.floor(timeLeft.value / 1000);
    const minutes = Math.floor(totalSeconds / 60);
    const seconds = totalSeconds % 60;
    return `${minutes}:${seconds.toString().padStart(2, '0')}`;
});

// 4. Aksi: Tetap Login
const stayLoggedIn = async () => {
    try {
        // Panggil endpoint keep-alive untuk menyentuh session server
        await axios.post(route('keep-alive'));
        
        // Update aktivitas lokal
        recordActivity();
        visible.value = false;
    } catch (error) {
        console.error("Gagal memperpanjang sesi", error);
        // Jika gagal (misal internet putus), logout manual agar aman
        logout();
    }
};

// 5. Aksi: Logout
const logout = () => {
    visible.value = false;
    router.post(route('logout'));
};

// --- LIFECYCLE ---
onMounted(() => {
    // Set aktivitas awal
    recordActivity();

    // Jalankan pengecekan setiap 1 detik
    timerInterval = setInterval(checkSessionStatus, 1000);

    // Listener interaksi user di tab ini
    window.addEventListener('mousemove', recordActivity);
    window.addEventListener('keydown', recordActivity);
    window.addEventListener('click', recordActivity);
    
    // Listener Inertia (saat pindah halaman tanpa reload)
    router.on('finish', recordActivity);
});

onUnmounted(() => {
    clearInterval(timerInterval);
    window.removeEventListener('mousemove', recordActivity);
    window.removeEventListener('keydown', recordActivity);
    window.removeEventListener('click', recordActivity);
});
</script>

<template>
    <Dialog v-model:visible="visible" modal :closable="false" :draggable="false"
            :style="{ width: '450px' }"
            :pt="{ 
                root: { class: '!border-none !bg-transparent !shadow-none' },
                mask: { class: '!backdrop-blur-md !bg-gray-900/60' } 
            }">
        
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden border border-gray-100">
            
            <div class="bg-amber-50 p-6 flex flex-col items-center justify-center border-b border-amber-100">
                <div class="w-16 h-16 bg-amber-100 rounded-full flex items-center justify-center mb-3 animate-pulse">
                    <i class="pi pi-clock text-3xl text-amber-600"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800">Sesi Segera Berakhir</h3>
                <p class="text-gray-500 text-sm text-center mt-1">
                    Demi keamanan, sesi Anda akan berakhir otomatis.
                </p>
            </div>

            <div class="p-6 text-center">
                <div class="text-4xl font-black text-gray-800 tabular-nums mb-2 tracking-tight">
                    {{ formattedTimeLeft }}
                </div>
                <p class="text-sm text-gray-400 mb-6">Waktu tersisa sebelum logout otomatis</p>

                <ProgressBar :value="progressValue" :showValue="false" style="height: 6px" 
                    :pt="{ 
                        root: { class: '!bg-gray-100 !rounded-full' },
                        value: { class: progressValue < 30 ? '!bg-red-500' : (progressValue < 60 ? '!bg-amber-500' : '!bg-emerald-500') }
                    }" 
                />
            </div>

            <div class="p-4 bg-gray-50 flex gap-3 justify-center border-t border-gray-100">
                <Button label="Keluar Sekarang" icon="pi pi-power-off" severity="secondary" text 
                        @click="logout" />
                <Button label="Saya Masih Disini" icon="pi pi-check-circle" severity="success" 
                        class="!px-6 !font-bold shadow-lg shadow-emerald-200" 
                        @click="stayLoggedIn" />
            </div>

        </div>
    </Dialog>
</template>