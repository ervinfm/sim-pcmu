<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth.user);
const userRole = computed(() => user.value?.role);

// --- LOGIKA ROLE ---
const isSuperAdmin = computed(() => userRole.value === 'super_admin'); 
const isPrmAdmin   = computed(() => userRole.value === 'admin_prm');   
const isAumAdmin   = computed(() => userRole.value === 'admin_aum');   

const canManageMembers = computed(() => ['super_admin', 'admin_prm'].includes(userRole.value));
const canManageResources = computed(() => ['super_admin', 'admin_prm', 'admin_aum'].includes(userRole.value));

// --- STRUKTUR MENU ENTERPRISE (Updated Routes & Active Logic) ---
const menuItems = computed(() => [
    {
        header: 'OVERVIEW',
        show: true, 
        items: [
            { label: 'Dashboard', route: 'dashboard', icon: 'pi pi-home', show: true },
        ]
    },
    {
        header: 'SUMBER DAYA ORGANISASI', 
        show: canManageResources.value, 
        items: [
            { 
                label: 'Struktur Organisasi', 
                route: 'organizations.index', 
                icon: 'pi pi-sitemap', show: 
                true, activePaths: ['organizations.*'] 
            },
            { 
                label: 'Database Anggota', 
                route: 'members.index', 
                icon: 'pi pi-users', 
                show: canManageMembers.value,
                activePaths: ['members.*']
            },
                        { 
                label: 'Manajemen Keuangan', 
                route: 'finance.transactions.index', 
                icon: 'pi pi-wallet', 
                show: true,
                activePaths: ['finance.*']
            },
            { 
                label: 'Manajemen Aset', 
                route: 'assets.index', 
                icon: 'pi pi-box', 
                show: true, 
                activePaths: ['assets.*'] 
            },
        ]
    },
    {
        header: 'TATA KELOLA ADMINISTRASI',
        show: canManageResources.value,
        items: [
            { label: 'E-Arsip & Persuratan', route: 'archives.index', icon: 'pi pi-envelope', show: true },
        ]
    },
    {
        header: 'ANALISIS DATA & LAPORAN',
        show: canManageResources.value, 
        items: [
            { label: 'Peta Sebaran Dakwah', route: 'maps.index', icon: 'pi pi-map', show: true },
            
            // [UPDATE] Route Reports
            { 
                label: 'Pusat Laporan Terpadu', 
                route: 'reports.index', 
                icon: 'pi pi-chart-pie', 
                show: true,
                activePaths: ['reports.*'] 
            },
        ]
    },
    {
        header: 'SYSTEM & SECURITY',
        show: isSuperAdmin.value, 
        items: [
            { label: 'Pengguna & Hak Akses', route: 'users.index', icon: 'pi pi-shield', show: true, activePaths: ['users.*'] },
        ]
    },
    {
        header: 'PUBLIKASI & CMS',
        show: isSuperAdmin.value, 
        items: [
            { label: 'Portal Berita', route: 'posts.index', icon: 'pi pi-megaphone', show: true },
            { label: 'Identitas Website', route: 'settings.edit', icon: 'pi pi-globe', show: true },
        ]
    }
]);

// --- LOGIKA ACTIVE STATE (SMART) ---
const isActive = (item) => {
    // 1. Cek keamanan: Jika route kosong/placeholder
    if (!item.route) return false;

    // 2. Cek Custom Active Paths (Array)
    // Jika item punya definisi activePaths, cek apakah route saat ini cocok dengan salah satunya
    if (item.activePaths) {
        return item.activePaths.some(pattern => route().current(pattern));
    }

    // 3. Default Check (Route Name + Wildcard)
    return route().current(item.route + '*');
};
</script>

<template>
    <div class="h-screen sticky top-0 flex flex-col bg-white/80 supports-[backdrop-filter]:bg-white/60 backdrop-blur-xl border-r border-white/50 shadow-[4px_0_16px_rgba(0,0,0,0.02)] z-30">
        
        <div class="h-16 shrink-0 flex items-center justify-center px-6 relative mb-2 border-b border-dashed border-emerald-100/50">
            <Link :href="route('dashboard')" class="flex items-center gap-3.5 group relative z-10 p-2 rounded-xl transition-colors duration-300 hover:bg-white/40">
                <div class="absolute inset-0 bg-gradient-to-tr from-emerald-400/30 to-teal-400/0 blur-2xl rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                <div class="relative">
                    <div class="absolute inset-0 bg-white rounded-full blur-sm opacity-60"></div>
                    <img src="/images/logo.png" class="h-11 w-auto relative z-10 drop-shadow-lg" alt="Logo Muhammadiyah">
                </div>
                <div class="flex flex-col">
                    <span class="font-black text-xl leading-none tracking-tight text-emerald-900 group-hover:text-emerald-700 transition-colors duration-300">
                        SIM PCM
                    </span>
                    <div class="flex items-center gap-1 mt-1">
                        <span class="w-8 h-[1px] bg-emerald-300"></span>
                        <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest group-hover:text-emerald-600 whitespace-nowrap transition-colors">
                            Muara Aman
                        </span>
                    </div>
                </div>
            </Link>
        </div>

        <div class="flex-1 overflow-y-auto py-4 px-4 space-y-6 custom-scrollbar">
            <template v-for="(group, index) in menuItems" :key="index">
                <div v-if="group.show !== false">
                    
                    <div v-if="group.header" class="px-4 mb-3 text-[10px] font-extrabold text-gray-400 uppercase tracking-widest">
                        {{ group.header }}
                    </div>

                    <div class="space-y-1">
                        <template v-for="(item, itemIndex) in group.items" :key="itemIndex">
                            
                            <Link v-if="item.show !== false"
                                  :href="item.route && route().has(item.route) ? route(item.route) : '#'"
                                  class="flex items-center gap-3 px-4 py-2 rounded-xl text-sm font-medium transition-all duration-300 relative overflow-hidden group"
                                  :class="isActive(item) 
                                    ? 'text-emerald-800 font-bold bg-emerald-50/50' 
                                    : 'text-gray-500 hover:text-emerald-600 hover:bg-gray-50'">
                                
                                <div v-if="isActive(item)" 
                                     class="absolute left-0 top-1/2 -translate-y-1/2 h-6 w-1 bg-emerald-500 rounded-r-full shadow-lg shadow-emerald-200">
                                </div>

                                <i :class="[item.icon, isActive(item) ? 'text-emerald-600' : 'text-gray-400 group-hover:text-emerald-500']"
                                   class="relative z-10 text-lg transition-transform duration-300 group-hover:-translate-y-0.5"></i>
                                
                                <span class="relative z-10">{{ item.label }}</span>
                            </Link>
                        </template>
                    </div>
                </div>
            </template>

            <div class="h-8"></div>
        </div>

    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 5px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background-color: rgba(167, 243, 208, 0.5);
    border-radius: 20px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background-color: rgba(52, 211, 153, 0.8);
}
</style>