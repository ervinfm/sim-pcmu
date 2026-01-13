<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth.user);
const userRole = computed(() => user.value?.role);

// --- LOGIKA ROLE (Tetap Sama) ---
const isSuperAdmin = computed(() => userRole.value === 'super_admin'); 
const isPrmAdmin   = computed(() => userRole.value === 'admin_prm');   
const isAumAdmin   = computed(() => userRole.value === 'admin_aum');   

const canManageMembers = computed(() => ['super_admin', 'admin_prm'].includes(userRole.value));
const canManageResources = computed(() => ['super_admin', 'admin_prm', 'admin_aum'].includes(userRole.value));

const menuItems = computed(() => [
    {
        header: 'UTAMA',
        show: true, 
        items: [
            { label: 'Dashboard', route: 'dashboard', icon: 'pi pi-home', show: true },
        ]
    },
    {
        header: 'MODUL',
        show: canManageResources.value, 
        items: [
            { label: 'Data Anggota', route: 'members.index', icon: 'pi pi-users', show: canManageMembers.value },
            { label: 'Keuangan', route: 'transactions.index', icon: 'pi pi-wallet', show: true },
            { label: 'Aset & Inventaris', route: 'assets.index', icon: 'pi pi-box', show: true },
        ]
    },
    {
        header: 'ADMINISTRASI',
        show: isSuperAdmin.value, 
        items: [
            { label: 'Manajemen User', route: 'users.index', icon: 'pi pi-user-edit', show: true },
            { label: 'Master Organisasi', route: 'organizations.index', icon: 'pi pi-sitemap', show: true },
        ]
    },
    {
        header: 'WEB PUBLIK',
        show: isSuperAdmin.value, 
        items: [
            { label: 'Berita & Artikel', route: 'posts.index', icon: 'pi pi-megaphone', show: true },
            { label: 'Pengaturan Web', route: 'settings.edit', icon: 'pi pi-cog', show: true },
        ]
    }
]);

const isActive = (routeName) => {
    return route().current(routeName + '*');
};

const roleLabel = computed(() => {
    if (isSuperAdmin.value) return 'Administrator Cabang';
    if (isPrmAdmin.value) return 'Admin Ranting';
    if (isAumAdmin.value) return 'Admin Amal Usaha';
    return 'Anggota';
});
</script>

<template>
    <div class="h-full flex flex-col bg-white/80 supports-[backdrop-filter]:bg-white/60 backdrop-blur-xl border-r border-white/50 shadow-[4px_0_16px_rgba(0,0,0,0.02)]">
        
        <div class="h-16 flex items-center justify-center px-6 relative mb-2 border-b border-dashed border-emerald-100/50">
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
                    
                    <div v-if="group.header" class="px-4 mb-3 text-[12px] font-extrabold text-gray-400 uppercase tracking-widest">
                        {{ group.header }}
                    </div>

                    <div class="space-y-1">
                        <template v-for="(item, itemIndex) in group.items" :key="itemIndex">
                            
                            <Link v-if="item.show !== false"
                                  :href="item.route ? route(item.route) : '#'"
                                  class="flex items-center gap-3 px-4 py-2 rounded-xl text-sm font-medium transition-all duration-300 relative overflow-hidden group"
                                  :class="isActive(item.route) 
                                    ? 'text-emerald-800 font-bold bg-emerald-50/50' 
                                    : 'text-gray-500 hover:text-emerald-600 hover:bg-gray-50'">
                                
                                <div v-if="isActive(item.route)" 
                                     class="absolute left-0 top-1/2 -translate-y-1/2 h-6 w-1 bg-emerald-500 rounded-r-full shadow-lg shadow-emerald-200">
                                </div>

                                <i :class="[item.icon, isActive(item.route) ? 'text-emerald-600' : 'text-gray-400 group-hover:text-emerald-500']"
                                   class="relative z-10 text-lg transition-transform duration-300 group-hover:-translate-y-0.5"></i>
                                
                                <span class="relative z-10">{{ item.label }}</span>
                            </Link>
                        </template>
                    </div>
                </div>
            </template>

        </div>

    </div>
</template>