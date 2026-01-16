<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { debounce } from 'lodash';

import DataView from 'primevue/dataview';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import Dropdown from 'primevue/dropdown';
import Tag from 'primevue/tag';
import Menu from 'primevue/menu';
import ConfirmPopup from 'primevue/confirmpopup';
import { useConfirm } from "primevue/useconfirm";

const props = defineProps({ organizations: Object, filters: Object });

const search = ref(props.filters.search);
const sort = ref(props.filters.sort || 'name');
const activeTab = ref(props.filters.tab || 'STRUKTURAL');
const isLoading = ref(false);

const tabs = [
    { id: 'STRUKTURAL', label: 'Pimpinan', icon: 'pi pi-sitemap', activeClass: 'bg-emerald-600 text-white border-emerald-600 shadow-lg shadow-emerald-200', inactiveClass: 'text-emerald-700 bg-white border-emerald-200 hover:bg-emerald-50' },
    { id: 'AUM', label: 'Amal Usaha', icon: 'pi pi-building', activeClass: 'bg-blue-600 text-white border-blue-600 shadow-lg shadow-blue-200', inactiveClass: 'text-blue-700 bg-white border-blue-200 hover:bg-blue-50' },
    { id: 'ORTOM', label: 'Ortom', icon: 'pi pi-users', activeClass: 'bg-orange-500 text-white border-orange-500 shadow-lg shadow-orange-200', inactiveClass: 'text-orange-700 bg-white border-orange-200 hover:bg-orange-50' }
];

const updateParams = () => {
    isLoading.value = true;
    router.get(route('organizations.index'), {
        tab: activeTab.value, search: search.value, sort: sort.value
    }, { preserveState: true, preserveScroll: true, replace: true, onFinish: () => isLoading.value = false });
};

watch(search, debounce(updateParams, 500));
watch(sort, updateParams);
const changeTab = (id) => { activeTab.value = id; updateParams(); };

// Action Logic
const confirm = useConfirm();
const menu = ref();
const selectedId = ref(null);
const selectedItem = ref(null);
const toggleMenu = (event, item) => {
    selectedItem.value = item;      // Set Object Item lengkap untuk Logic Menu
    selectedId.value = item.id;     // Set ID untuk Logic Hapus/Edit
    menu.value.toggle(event);       // Buka Menu
};
// Menu Item Dinamis menggunakan Computed atau Function
const getMenuItems = () => {
    const items = [
        { 
            label: 'Lihat Detail', 
            icon: 'pi pi-eye', 
            command: () => router.visit(route('organizations.show', selectedItem.value.id)) 
        },
        { 
            label: 'Edit Profil', 
            icon: 'pi pi-file-edit', 
            command: () => router.visit(route('organizations.edit', selectedItem.value.id)) 
        },
        { separator: true },
        { 
            label: 'Kelola Struktur', 
            icon: 'pi pi-users', 
            class: 'text-emerald-600 font-medium',
            // Arahkan ke halaman baru
            command: () => router.visit(route('organizations.structure', selectedItem.value.id)) 
        }
    ];

    // Tambah menu Wilayah KHUSUS untuk Struktural (PCM/PRM)
    if (selectedItem.value?.category === 'STRUKTURAL') {
        items.splice(3, 0, { // Insert di posisi yang pas
            label: 'Kelola Wilayah', 
            icon: 'pi pi-map', 
            class: 'text-blue-600 font-medium',
            command: () => router.visit(route('organizations.territory', selectedItem.value.id)) 
        });
    }

    items.push(
        { separator: true },
        { 
            label: 'Hapus Unit', 
            icon: 'pi pi-trash', 
            class: 'text-red-600 font-bold', 
            command: () => confirmDelete() 
        }
    );

    return items;
};

const confirmDelete = () => {
    confirm.require({
        message: 'Hapus data ini? Data tidak bisa dikembalikan.', header: 'Konfirmasi', icon: 'pi pi-exclamation-triangle', acceptClass: 'p-button-danger',
        accept: () => router.delete(route('organizations.destroy', selectedId.value))
    });
};

const getBadge = (type) => {
    const map = { 'PCM': 'success', 'PRM': 'warning', 'SEKOLAH': 'info', 'MASJID': 'success', 'LAZISMU': 'warning' };
    return map[type] || 'secondary';
};
const getBorder = (cat) => cat === 'STRUKTURAL' ? 'hover:border-emerald-400' : (cat === 'AUM' ? 'hover:border-blue-400' : 'hover:border-orange-400');
</script>

<template>
    <Head title="Manajemen Organisasi" />
    <AppLayout>
        <div class="min-h-screen bg-slate-50/50 -m-6 p-6">
            <div class="max-w-7xl mx-auto space-y-6">
                <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                    <div>
                        <h1 class="text-2xl font-black text-gray-800 tracking-tight">Direktori Organisasi</h1>
                        <p class="text-gray-500 text-sm mt-0.5">Database terpusat Cabang, Ranting, AUM, dan Ortom.</p>
                    </div>
                    <Link :href="route('organizations.create')">
                        <Button label="Tambah Unit" icon="pi pi-plus" class="!bg-gray-900 !border-gray-900 hover:!bg-gray-800 !rounded-lg !text-sm font-bold shadow-lg" />
                    </Link>
                </div>

                <div class="flex flex-col xl:flex-row gap-4 justify-between items-start xl:items-center top-20 z-30 bg-slate-50/90 backdrop-blur-sm py-2">
                    <div class="flex flex-wrap gap-2">
                        <button v-for="tab in tabs" :key="tab.id" @click="changeTab(tab.id)" class="flex items-center gap-2 px-4 py-2 rounded-lg font-bold text-sm transition-all border" :class="activeTab === tab.id ? tab.activeClass : tab.inactiveClass">
                            <i :class="tab.icon"></i><span>{{ tab.label }}</span>
                        </button>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-2 w-full xl:w-auto">
                        <IconField iconPosition="left" class="w-full sm:w-72">
                            <InputIcon class="pi pi-search text-gray-400" />
                            <InputText v-model="search" placeholder="Cari unit..." class="w-full !rounded-lg !border-gray-300 focus:!border-emerald-500" />
                            <i v-if="isLoading" class="pi pi-spin pi-spinner absolute right-3 top-3 text-emerald-500 text-xs"></i>
                        </IconField>
                        <Dropdown v-model="sort" :options="[{label:'Nama (A-Z)',value:'name'},{label:'Terbaru',value:'newest'}]" optionLabel="label" optionValue="value" class="w-full sm:w-48 !rounded-lg !border-gray-300" />
                    </div>
                </div>

                <DataView :value="organizations.data" :layout="'grid'" :paginator="false" class="!bg-transparent" :pt="{ content: { class: '!bg-transparent !p-0' } }">
                    <template #grid="slotProps">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 w-full">
                            <div v-for="(item, index) in slotProps.items" :key="index" class="col-span-1 h-full">
                                <div class="group relative bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-xl transition-all duration-300 h-full flex flex-col overflow-hidden cursor-pointer" 
                                     :class="getBorder(item.category)"
                                     @click="router.visit(route('organizations.show', item.id))">
                                    
                                    <div class="p-5 border-b border-gray-50 flex items-start gap-4">
                                        <div class="w-14 h-14 rounded-lg bg-gray-50 border border-gray-100 flex items-center justify-center shrink-0 overflow-hidden">
                                            <img v-if="item.logo_path" :src="'/storage/' + item.logo_path" class="w-full h-full object-contain p-1">
                                            <span v-else class="text-lg font-bold text-gray-400 select-none"><img src="images/logo.png"></span>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2 mb-1">
                                                <Tag :value="item.type" :severity="getBadge(item.type)" class="!text-[9px] !px-1.5 uppercase font-bold !rounded" />
                                            </div>
                                            <h3 class="text-base font-bold text-gray-800 leading-snug line-clamp-2 group-hover:text-emerald-700 transition-colors">{{ item.name }}</h3>
                                        </div>
                                        <button @click.stop="toggleMenu($event, item)" class="text-gray-300 hover:text-gray-600 -mr-2 -mt-2 p-2 relative z-10">
                                            <i class="pi pi-ellipsis-v"></i>
                                        </button>
                                    </div>

                                    <div class="p-5 flex-1 space-y-3">
                                        <div v-if="item.parent" class="flex items-center gap-2 text-xs text-gray-500 bg-gray-50 p-1.5 rounded w-fit">
                                            <i class="pi pi-sitemap text-gray-400"></i> <span class="truncate">Induk: <strong>{{ item.parent.name }}</strong></span>
                                        </div>
                                        <div v-else class="flex items-center gap-2 text-xs text-gray-500 bg-gray-50 p-1.5 rounded w-fit">
                                            <i class="pi pi-sitemap text-gray-400"></i> <span class="truncate">Induk: <strong>PDM Kabupaten Lebong</strong></span>
                                        </div>
                                        <div class="flex items-start gap-2 text-xs text-gray-500">
                                            <i class="pi pi-map-marker mt-0.5 text-red-400"></i> <span class="line-clamp-2">{{ item.address || 'Alamat kosong' }}</span>
                                        </div>
                                        <div v-if="item.email || item.phone" class="flex items-center gap-3 pt-1 border-t border-gray-50 mt-2">
                                            <span v-if="item.email" class="text-xs text-blue-500 flex items-center gap-1"><i class="pi pi-envelope"></i> Email</span>
                                            <span v-if="item.phone" class="text-xs text-emerald-500 flex items-center gap-1"><i class="pi pi-whatsapp"></i> Kontak</span>
                                        </div>
                                    </div>

                                    <div class="px-5 py-3 bg-gray-50/80 border-t border-gray-100 grid grid-cols-2 gap-px">
                                        <div class="text-center border-r border-gray-200 pr-2">
                                            <span class="block text-[10px] text-gray-400 uppercase font-bold tracking-wider">Total Basis</span>
                                            <span class="block text-sm font-black text-gray-700">{{ item.network_members_count }} <span class="text-[9px] font-normal text-gray-400">Org</span></span>
                                        </div>
                                        <div class="text-center pl-2">
                                            <template v-if="activeTab === 'STRUKTURAL'">
                                                <span class="block text-[10px] text-gray-400 uppercase font-bold tracking-wider">Cakupan</span>
                                                <span class="block text-sm font-black text-gray-700">{{ item.network_territories_count }} <span class="text-[9px] font-normal text-gray-400">Wilayah</span></span>
                                            </template>
                                            <template v-else>
                                                <span class="block text-[10px] text-gray-400 uppercase font-bold tracking-wider">Kode Unit</span>
                                                <span class="block text-xs font-bold text-gray-700 truncate">{{ item.code || '-' }}</span>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </DataView>

                <div class="flex justify-center pt-4 pb-8" v-if="organizations.links.length > 3">
                    <div class="flex gap-1">
                        <Link v-for="(link, i) in organizations.links" :key="i" :href="link.url || '#'" 
                              class="w-9 h-9 flex items-center justify-center rounded-md text-sm font-medium transition-all"
                              :class="{'bg-gray-900 text-white shadow-lg': link.active, 'bg-white text-gray-500 border hover:bg-gray-50': !link.active, 'opacity-50 pointer-events-none': !link.url}"
                              v-html="link.label.replace('&laquo;', '').replace('&raquo;', '')" />
                    </div>
                </div>
            </div>
        </div>
        <Menu ref="menu" :model="selectedItem ? getMenuItems() : []" :popup="true" />
        <ConfirmPopup />
    </AppLayout>
</template>