<script setup>
import { ref, computed } from 'vue';
import { Link, useForm, usePage, router } from '@inertiajs/vue3';
import Menu from 'primevue/menu';
import Button from 'primevue/button';
import Avatar from 'primevue/avatar';
import OverlayPanel from 'primevue/overlaypanel';

// 1. Ambil Data User Login dari Shared Props Inertia
const page = usePage();
const user = computed(() => page.props.auth.user);

// 2. Helper Label Role
const userRoleLabel = computed(() => {
    switch (user.value?.role) {
        case 'super_admin': return 'Administrator';
        case 'admin_prm': return 'Admin Ranting';
        case 'admin_aum': return 'Admin AUM';
        default: return 'Anggota';
    }
});

const items = ref([
    {
        label: 'User Options',
        items: [
            { 
                label: 'Profil Saya', 
                icon: 'pi pi-id-card', 
                // Mengarah ke Halaman Profil Member
                command: () => router.visit(route('profile.me')) 
            },
            { 
                label: 'Account Settings', 
                icon: 'pi pi-cog', 
                // Mengarah ke Halaman Edit Email/Password
                command: () => router.visit(route('profile.account')) 
            },
            { 
                label: 'Pesan Masuk', 
                icon: 'pi pi-envelope', 
                badge: '3', // Nanti bisa didinamiskan dari props
                // Mengarah ke Halaman Inbox
                command: () => router.visit(route('profile.messages')) 
            },
            { 
                label: 'Log Aktivitas', 
                icon: 'pi pi-history', 
                // Mengarah ke Halaman Log
                command: () => router.visit(route('profile.logs')) 
            }
        ]
    },
    {
        separator: true
    },
    {
        label: 'Keluar Sistem',
        icon: 'pi pi-sign-out',
        class: 'text-red-600 font-bold hover:bg-red-50',
        command: () => { 
            const form = useForm({});
            form.post(route('logout'));
        }
    }
]);

// Dropdown Profil
const menu = ref();

const toggleProfile = (event) => {
    menu.value.toggle(event);
};

defineEmits(['toggleSidebar']);

// LOGIKA HEADER DINAMIS (Judul & Ikon berubah sesuai Route)
const pageHeader = computed(() => {
    // 1. Dashboard
    if (route().current('dashboard')) {
        return { 
            overline: 'OVERVIEW', 
            title: 'Dashboard Utama', 
            icon: 'pi pi-home',
            color: 'text-blue-600',
            bg: 'bg-blue-50'
        };
    }
    
    // 2. Modul Anggota
    if (route().current('members.*')) {
        return { 
            overline: 'KEANGGOTAAN', 
            title: 'Data Warga & Kader', 
            icon: 'pi pi-users',
            color: 'text-emerald-600',
            bg: 'bg-emerald-50'
        };
    }

    // 3. Modul Keuangan
    if (route().current('transactions.*')) {
        return { 
            overline: 'FINANSIAL', 
            title: 'Arus Kas & Transaksi', 
            icon: 'pi pi-wallet',
            color: 'text-orange-600',
            bg: 'bg-orange-50'
        };
    }

    // 4. Modul Aset
    if (route().current('assets.*')) {
        return { 
            overline: 'INVENTARIS', 
            title: 'Aset & Barang', 
            icon: 'pi pi-box',
            color: 'text-cyan-600',
            bg: 'bg-cyan-50'
        };
    }

    // 5. Administrasi (User & Organisasi)
    if (route().current('users.*') || route().current('organizations.*')) {
        return { 
            overline: 'ADMINISTRASI', 
            title: 'Pengaturan Sistem', 
            icon: 'pi pi-cog',
            color: 'text-purple-600',
            bg: 'bg-purple-50'
        };
    }

    // 6. Web Publik (Post & Setting)
    if (route().current('posts.*') || route().current('settings.*')) {
        return { 
            overline: 'WEB PUBLIK', 
            title: 'Konten Website', 
            icon: 'pi pi-globe',
            color: 'text-pink-600',
            bg: 'bg-pink-50'
        };
    }

    // Default
    return { 
        overline: 'SISTEM INFORMASI', 
        title: 'Manajemen Persyarikatan', 
        icon: 'pi pi-th-large',
        color: 'text-gray-600',
        bg: 'bg-gray-50'
    };
});

const op = ref(); // Referensi ke OverlayPanel

const toggleNotifications = (event) => {
    op.value.toggle(event);
};

// 1. Data Dummy Statistik (Segment 1)
const notifStats = ref([
    { label: 'Anggota', count: 3, icon: 'pi pi-users', color: 'text-blue-600', bg: 'bg-blue-50' },
    { label: 'Aset', count: 1, icon: 'pi pi-box', color: 'text-orange-600', bg: 'bg-orange-50' },
    { label: 'Keuangan', count: 5, icon: 'pi pi-wallet', color: 'text-emerald-600', bg: 'bg-emerald-50' },
    { label: 'Lainnya', count: 2, icon: 'pi pi-info-circle', color: 'text-gray-600', bg: 'bg-gray-50' },
]);
const recentNotifs = ref([
    { title: 'Iuran Anggota Masuk', desc: 'Budi Santoso membayar iuran bulan ini', time: '5 menit lalu', icon: 'pi pi-wallet', color: 'text-emerald-500' },
    { title: 'Aset Perlu Perbaikan', desc: 'Laporan kerusakan AC Ruang Rapat', time: '1 jam lalu', icon: 'pi pi-exclamation-triangle', color: 'text-orange-500' },
    { title: 'Anggota Baru', desc: 'Registrasi baru: Siti Aminah', time: '3 jam lalu', icon: 'pi pi-user-plus', color: 'text-blue-500' },
    { title: 'Update Sistem', desc: 'Maintenance server dijadwalkan besok', time: '1 hari lalu', icon: 'pi pi-cog', color: 'text-gray-500' },
]);

const totalNotif = computed(() => notifStats.value.reduce((acc, item) => acc + item.count, 0));

</script>

<template>
    <header class="h-16 flex items-center justify-between bg-white border-b border-gray-100 shadow-sm px-4 lg:px-8">
        
        <div class="flex items-center gap-4">
            <button @click="$emit('toggleSidebar')" 
                    class="lg:hidden p-2 text-gray-500 hover:text-emerald-600 hover:bg-emerald-50 rounded-xl transition duration-300">
                <i class="pi pi-bars text-xl"></i>
            </button>
            
            <div class="hidden md:flex items-center gap-3">
                
                <div class="w-10 h-10 rounded-xl flex items-center justify-center border border-gray-100 shadow-sm transition-colors duration-300"
                     :class="pageHeader.bg">
                    <i :class="[pageHeader.icon, pageHeader.color]" class="text-lg"></i>
                </div>

                <div class="flex flex-col">
                    <span class="text-[10px] font-extrabold uppercase tracking-widest text-gray-400">
                        {{ pageHeader.overline }}
                    </span>
                    <span class="text-sm font-bold text-gray-800 leading-tight">
                        {{ pageHeader.title }}
                    </span>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2 md:gap-4">
            
            <div class="relative group">
                <Button icon="pi pi-bell" text rounded severity="secondary" 
                        @click="toggleNotifications"
                        class="!w-10 !h-10 !text-gray-500 group-hover:!text-emerald-600 group-hover:!bg-emerald-50 transition-all duration-300" />
                
                <span v-if="totalNotif > 0" class="absolute top-2 right-2 flex h-2.5 w-2.5 pointer-events-none">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-red-500 border-2 border-white"></span>
                </span>

                <OverlayPanel ref="op" class="!p-0 !rounded-xl !border-0 !shadow-xl w-80 sm:w-96">
                    <div class="flex flex-col bg-white rounded-xl overflow-hidden">
                        
                        <div class="px-4 py-3 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                            <span class="font-bold text-gray-800 text-sm">Notifikasi</span>
                            <span class="text-xs text-emerald-600 font-medium cursor-pointer hover:underline">Tandai dibaca</span>
                        </div>

                        <div class="p-3 grid grid-cols-2 gap-2 border-b border-gray-100">
                            <div v-for="(stat, index) in notifStats" :key="index" 
                                 class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-50 transition cursor-pointer border border-transparent hover:border-gray-100">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0" :class="stat.bg">
                                    <i :class="[stat.icon, stat.color]" class="text-xs"></i>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-[10px] text-gray-400 uppercase font-bold">{{ stat.label }}</span>
                                    <span class="text-sm font-bold text-gray-800 leading-none">{{ stat.count }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="max-h-64 overflow-y-auto custom-scrollbar">
                            <div v-if="recentNotifs.length === 0" class="p-6 text-center text-gray-400 text-sm">
                                Tidak ada notifikasi baru.
                            </div>

                            <ul v-else class="divide-y divide-gray-50">
                                <li v-for="(notif, i) in recentNotifs" :key="i" 
                                    class="p-3 hover:bg-emerald-50/30 transition cursor-pointer flex gap-3 items-start group">
                                    <div class="mt-1">
                                        <i :class="[notif.icon, notif.color]" class="text-sm"></i>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm font-semibold text-gray-700 group-hover:text-emerald-700 transition">
                                            {{ notif.title }}
                                        </p>
                                        <p class="text-xs text-gray-500 line-clamp-1 mb-1">
                                            {{ notif.desc }}
                                        </p>
                                        <p class="text-[10px] text-gray-400">
                                            {{ notif.time }}
                                        </p>
                                    </div>
                                    <div class="mt-2 w-1.5 h-1.5 rounded-full bg-emerald-500"></div>
                                </li>
                            </ul>
                        </div>

                        <div class="p-2 border-t border-gray-100 text-center bg-gray-50/50">
                            <button class="text-xs font-bold text-emerald-600 hover:text-emerald-700 transition py-1 w-full rounded hover:bg-emerald-50">
                                Lihat Semua Notifikasi
                            </button>
                        </div>

                    </div>
                </OverlayPanel>
            </div>

            <div class="h-8 w-px bg-gray-200 mx-1 hidden md:block"></div>

            <div class="relative">
                <button @click="toggleProfile" 
                        class="flex items-center gap-3 focus:outline-none p-1 pr-3 rounded-full transition-all duration-300 hover:bg-gray-50 border border-transparent hover:border-gray-200 group">
                    
                    <Avatar v-if="user.photo" :image="'/storage/' + user.photo" class="!border-2 !border-white shadow-sm object-cover group-hover:border-emerald-100 transition" shape="circle" />
                    <Avatar v-else icon="pi pi-user" class="!bg-gradient-to-br !from-emerald-100 !to-blue-50 !text-emerald-700 !border-2 !border-white shadow-sm" shape="circle" />
                    
                    <div class="hidden md:flex flex-col items-start text-left">
                        <span class="text-sm font-bold text-gray-700 leading-none max-w-[100px] truncate">{{ user.name }}</span>
                        <span class="text-[10px] text-gray-500 uppercase tracking-wider mt-0.5 font-semibold">{{ userRoleLabel }}</span>
                    </div>
                    <i class="pi pi-chevron-down text-[10px] text-gray-400 hidden md:block group-hover:text-gray-600 transition"></i>
                </button>
                
                <Menu ref="menu" :model="items" :popup="true" class="!rounded-xl !border-0 !shadow-xl !mt-2 w-72">
                    
                    <template #start>
                        <div class="flex flex-col items-center pt-6 pb-4 px-4 bg-gray-50/50 border-b border-gray-100 mb-1">
                            
                            <div class="relative mb-3">
                                <Avatar v-if="user.photo" :image="'/storage/' + user.photo" 
                                        class="!w-20 !h-20 !border-4 !border-white shadow-md object-cover" shape="circle" />
                                <Avatar v-else icon="pi pi-user" 
                                        class="!w-20 !h-20 !bg-gradient-to-br !from-emerald-100 !to-blue-50 !text-emerald-600 !border-4 !border-white shadow-md !text-3xl" shape="circle" />
                                
                                <span class="absolute bottom-1 right-1 w-4 h-4 bg-green-500 border-2 border-white rounded-full" title="Online"></span>
                            </div>

                            <div class="text-center w-full">
                                <h4 class="font-bold text-gray-800 text-base truncate">{{ user.name }}</h4>
                                <p class="text-xs text-gray-500 mb-2 truncate">{{ user.email }}</p>
                                
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider bg-emerald-100 text-emerald-700 border border-emerald-200">
                                    {{ userRoleLabel }}
                                </span>
                            </div>
                        </div>
                    </template>

                    <template #item="{ item, props }">
                        <a v-bind="props.action" class="flex items-center gap-2 px-4 py-2">
                            <span :class="[item.icon, 'text-gray-500']"></span>
                            <span class="flex-1 text-sm font-medium text-gray-700">{{ item.label }}</span>
                            
                            <span v-if="item.badge" class="bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full min-w-[1.25rem] text-center">
                                {{ item.badge }}
                            </span>
                        </a>
                    </template>

                </Menu>
            </div>

        </div>
    </header>
</template>