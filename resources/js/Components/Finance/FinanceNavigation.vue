<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

// Helper untuk cek route aktif (mendukung wildcard *)
const isActive = (pattern) => {
    return route().current(pattern + '*');
};

// Helper cek route exists
const safeRoute = (name) => {
    return route().has(name) ? route(name) : '#';
};

// Definisi Menu beserta Judul & Deskripsinya
const navItems = computed(() => [
    { 
        label: 'Transaksi', 
        route: 'finance.transactions.index', 
        pattern: 'finance.transactions',
        icon: 'pi pi-arrow-right-arrow-left',
        // Otomatisasi Judul
        title: 'Transaksi Keuangan',
        desc: 'Catat dan kelola arus kas masuk, keluar, dan transfer dana.'
    },
    { 
        label: 'Buku Besar', 
        route: 'finance.ledgers.index', 
        pattern: 'finance.ledgers',
        icon: 'pi pi-book',
        title: 'Buku Besar & Jurnal',
        desc: 'Lihat riwayat detail mutasi debit/kredit per akun secara rinci.'
    },
    { 
        label: 'Anggaran', 
        route: 'finance.budgets.index', 
        pattern: 'finance.budgets',
        icon: 'pi pi-chart-pie',
        title: 'Rencana Anggaran (RAPB)',
        desc: 'Monitoring realisasi penggunaan anggaran vs rencana tahunan.'
    },
    { 
        label: 'Tutup Buku', 
        route: 'finance.closing-periods.index', 
        pattern: 'finance.closing-periods',
        icon: 'pi pi-lock',
        title: 'Tutup Buku Periode',
        desc: 'Kunci laporan bulanan atau tahunan untuk keperluan audit.'
    },
    { 
        label: 'Master Akun', 
        route: 'finance.accounts.index', 
        pattern: 'finance.accounts',
        icon: 'pi pi-sitemap',
        title: 'Master Data Akun (COA)',
        desc: 'Kelola hierarki akun aset, pendapatan, dan pengeluaran.'
    }
]);

// Cari Item yang sedang aktif untuk ditampilkan Judulnya
const activePage = computed(() => {
    return navItems.value.find(item => isActive(item.pattern));
});
</script>

<template>
    <div class="mb-6">
        <div v-if="activePage" class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 px-4 md:px-0 mb-3">
            <div>
                <h1 class="text-2xl font-black text-gray-800 tracking-tight">
                    {{ activePage.title }}
                </h1>
                <p class="text-gray-500 text-sm mt-1">
                    {{ activePage.desc }}
                </p>
            </div>
            
            <div v-if="$slots.actions">
                <slot name="actions" />
            </div>
        </div>

        <div class="border-b border-gray-100 overflow-x-auto bg-transparent sticky top-0 z-30">
            <nav class="flex items-center gap-6 min-w-max px-4 md:px-0" aria-label="Tabs">
                <Link v-for="tab in navItems" :key="tab.label" 
                      :href="safeRoute(tab.route)"
                      class="group relative py-3 px-1 flex items-center gap-2 text-sm font-medium transition-all duration-300 border-b-2"
                      :class="[
                          isActive(tab.pattern)
                            ? 'border-emerald-500 text-emerald-700 font-bold' 
                            : 'border-transparent text-gray-500 hover:text-emerald-600 hover:border-emerald-200'
                      ]">
                    
                    <i :class="tab.icon" class="text-base mb-0.5"></i>
                    <span>{{ tab.label }}</span>
                </Link>
            </nav>
        </div>
    </div>
</template>