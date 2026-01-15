<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    // Prop ini opsional, default true agar tidak merah kalau backend belum kirim
    hasOpeningBalance: { 
        type: Boolean, 
        default: true 
    }
});

// Helper untuk cek route aktif (mendukung wildcard *)
// Contoh: 'finance.transactions.*' akan aktif di index, create, edit
const isActive = (routeName) => {
    return route().current(routeName + '*');
};

// Helper cek route exists (agar tidak error jika route belum dibuat di web.php)
const safeRoute = (name) => {
    return route().has(name) ? route(name) : '#';
};

const navItems = computed(() => [
    { 
        label: 'Transaksi', 
        route: 'finance.transactions.index', 
        pattern: 'finance.transactions', // Pattern untuk active state
        icon: 'pi pi-list' 
    },
    { 
        label: 'Laporan', 
        route: 'finance.reports.index', 
        pattern: 'finance.reports',
        icon: 'pi pi-chart-bar' 
    },
    { 
        label: 'Saldo Awal', 
        route: 'finance.opening-balances.index', 
        pattern: 'finance.opening-balances',
        icon: 'pi pi-wallet',
        showWarning: !props.hasOpeningBalance // Tampilkan warning jika belum setup
    },
    { 
        label: 'Tutup Buku', 
        route: 'finance.closing-periods.index', 
        pattern: 'finance.closing-periods',
        icon: 'pi pi-lock' 
    },
    { 
        label: 'Master Akun', 
        route: 'finance.accounts.index', 
        pattern: 'finance.accounts',
        icon: 'pi pi-book' 
    }
]);
</script>

<template>
    <div class="border-b border-gray-100 overflow-x-auto top-0 z-30">
        <nav class="flex items-center gap-6 min-w-max px-4 md:px-0" aria-label="Tabs">
            <Link v-for="tab in navItems" :key="tab.label" 
                  :href="safeRoute(tab.route)"
                  class="group relative py-2 px-1 flex items-center gap-2 text-sm font-medium transition-all duration-300 border-b-2"
                  :class="[
                      isActive(tab.pattern)
                        ? 'border-emerald-500 text-emerald-700 font-bold' 
                        : 'border-transparent text-gray-500 hover:text-emerald-600 hover:border-emerald-200'
                  ]">
                
                <i :class="tab.icon" class="text-base mb-0.5"></i>
                <span>{{ tab.label }}</span>
                
                <span v-if="tab.showWarning" class="relative flex h-2 w-2">
                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                  <span class="relative inline-flex rounded-full h-2 w-2 bg-red-500"></span>
                </span>
            </Link>
        </nav>
    </div>

</template>