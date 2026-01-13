<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import "leaflet/dist/leaflet.css";
import { LMap, LTileLayer, LMarker, LPopup } from "@vue-leaflet/vue-leaflet";

// Components
import TabView from 'primevue/tabview';
import TabPanel from 'primevue/tabpanel';
import Tag from 'primevue/tag';
import Button from 'primevue/button';
import Avatar from 'primevue/avatar';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Menu from 'primevue/menu';

const props = defineProps({ organization: Object, stats: Object, members: Object });
const zoom = ref(15);
const center = ref([props.organization.latitude || -3.123321, props.organization.longitude || 102.217312]);

const formatDate = (d) => d ? new Date(d).toLocaleDateString('id-ID', {day: 'numeric', month: 'long', year: 'numeric'}) : '-';

// --- LOGIC ACTION MENU ---
const actionMenu = ref();
const toggleActionMenu = (event) => {
    actionMenu.value.toggle(event);
};

const getActionItems = () => {
    const items = [
        { label: 'Edit Profil Utama', icon: 'pi pi-file-edit', command: () => router.visit(route('organizations.edit', props.organization.id)) },
        { label: 'Kelola Struktur', icon: 'pi pi-users', command: () => router.visit(route('organizations.structure.edit', props.organization.id)) }
    ];
    if (props.organization.category === 'STRUKTURAL') {
        items.push({ separator: true });
        items.push({ label: 'Kelola Wilayah', icon: 'pi pi-map', command: () => router.visit(route('organizations.territory.edit', props.organization.id)) });
    }
    return items;
};

// --- LOGIC LABEL & HEADER DINAMIS ---
const labels = computed(() => {
    switch (props.organization.category) {
        case 'STRUKTURAL': 
            return { 
                statsTitle: 'Kekuatan Basis', 
                statsSubtitle: 'Termasuk jejaring unit binaan', 
                memberTab: 'Data Anggota',
                extraTab: 'Wilayah & Unit Binaan',
                icon: 'pi-sitemap'
            };
        case 'AUM': 
            return { 
                statsTitle: 'Aset SDM', 
                statsSubtitle: 'Guru, Karyawan & Staf', 
                memberTab: 'Data Pegawai',
                extraTab: 'Profil Pelayanan',
                icon: 'pi-building'
            };
        case 'ORTOM': 
            return { 
                statsTitle: 'Basis Kader', 
                statsSubtitle: 'Kader Aktif Terdaftar', 
                memberTab: 'Data Kader',
                extraTab: 'Profil Gerakan',
                icon: 'pi-users'
            };
        default: return { statsTitle: 'Statistik', statsSubtitle: '-', memberTab: 'Anggota', extraTab: 'Info', icon: 'pi-info' };
    }
});
</script>

<template>
    <Head :title="organization.name" />
    <AppLayout>
        <div class="min-h-screen bg-slate-50/50 -m-6 p-6">
            <div class="max-w-7xl mx-auto space-y-6">
                
                <div class="flex items-center gap-2">
                    <Link :href="route('organizations.index')" class="group flex items-center gap-2 text-slate-500 hover:text-emerald-600 transition-colors font-bold text-sm">
                        <div class="w-8 h-8 rounded-full bg-white border border-gray-200 flex items-center justify-center shadow-sm group-hover:border-emerald-200 group-hover:bg-emerald-50 transition-all">
                            <i class="pi pi-arrow-left text-xs"></i>
                        </div>
                        <span>Kembali ke Direktori</span>
                    </Link>
                </div>

                <div class="bg-white rounded-3xl overflow-hidden border border-gray-200 shadow-sm relative">
                    <div class="h-36 bg-gradient-to-r from-slate-800 to-slate-900 relative">
                        <div class="absolute inset-0 opacity-10 bg-[url('https://upload.wikimedia.org/wikipedia/commons/e/ec/World_map_blank_without_borders.svg')] bg-cover bg-center"></div>
                    </div>
                    <div class="px-8 pb-8 flex flex-col md:flex-row items-start gap-6 -mt-16 relative z-10">
                        <div class="w-32 h-32 bg-white rounded-2xl p-2 shadow-2xl border-4 border-white flex items-center justify-center shrink-0">
                            <img v-if="organization.logo_path" :src="'/storage/' + organization.logo_path" class="w-full h-full object-contain rounded-xl">
                            <span v-else class="text-4xl font-bold text-gray-300">{{ organization.name.charAt(0) }}</span>
                        </div>
                        <div class="flex-1 pt-16 mt-2 md:pt-16 w-full">
                            <div class="flex flex-col md:flex-row justify-between items-start gap-4">
                                <div>
                                    <div class="flex items-center gap-3 mb-2">
                                        <Tag :value="organization.type" severity="success" class="!px-3 uppercase text-xs font-bold" rounded />
                                        <span v-if="organization.code" class="text-xs font-mono text-gray-500 bg-gray-100 px-2 py-0.5 rounded border">{{ organization.code }}</span>
                                    </div>
                                    <h1 class="text-3xl font-black text-slate-800 tracking-tight leading-none mb-2">{{ organization.name }}</h1>
                                    <div class="flex items-center gap-2 text-sm text-gray-500">
                                        <i class="pi pi-map-marker text-red-500"></i>
                                        <span>{{ organization.address || 'Alamat belum diisi' }}</span>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <Button label="Kelola Unit" icon="pi pi-cog" iconPos="right" 
                                            class="!bg-gray-900 !border-gray-900 hover:!bg-gray-800 !px-4 !py-2 shadow-lg !text-sm font-bold"
                                            @click="toggleActionMenu" aria-haspopup="true" aria-controls="action_menu" />
                                    <Menu ref="actionMenu" id="action_menu" :model="getActionItems()" :popup="true" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                    
                    <div class="lg:col-span-4 space-y-6">
                        
                        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden p-1">
                            <div class="h-56 w-full rounded-xl overflow-hidden relative z-0">
                                <l-map ref="map" v-model:zoom="zoom" :center="center" :use-global-leaflet="false">
                                    <l-tile-layer url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"></l-tile-layer>
                                    <l-marker :lat-lng="center"><l-popup>{{ organization.name }}</l-popup></l-marker>
                                </l-map>
                            </div>
                            <div class="p-3 text-center">
                                <span class="text-xs font-bold text-gray-500 block mb-1">
                                    {{ organization.category === 'STRUKTURAL' ? 'Pusat Dakwah Wilayah' : 'Lokasi Fisik Bangunan' }}
                                </span>
                                <a :href="`https://maps.google.com/?q=${organization.latitude},${organization.longitude}`" target="_blank" class="text-xs font-bold text-blue-600 hover:underline inline-flex items-center gap-1">
                                    Buka Google Maps <i class="pi pi-external-link text-[10px]"></i>
                                </a>
                            </div>
                        </div>

                        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                            <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                                <i class="pi text-emerald-500" :class="labels.icon"></i> Statistik Data
                            </h3>
                            <div class="space-y-4">
                                <div class="bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl p-4 text-white shadow-lg shadow-emerald-200">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <p class="text-emerald-100 text-xs font-bold uppercase tracking-wider">{{ labels.statsTitle }}</p>
                                            <h2 class="text-3xl font-black mt-1">
                                                {{ organization.category === 'STRUKTURAL' ? stats.network_count : stats.direct_count }}
                                            </h2>
                                        </div>
                                        <i class="pi pi-users text-3xl opacity-50"></i>
                                    </div>
                                    <p class="text-[10px] mt-2 text-emerald-100/80 bg-black/10 inline-block px-2 py-1 rounded">
                                        {{ labels.statsSubtitle }}
                                    </p>
                                </div>

                                <div v-if="organization.category === 'STRUKTURAL'" class="grid grid-cols-2 gap-3">
                                    <div class="bg-gray-50 p-3 rounded-xl border border-gray-100 text-center">
                                        <span class="block text-2xl font-bold text-gray-800">{{ stats.children_count }}</span>
                                        <span class="text-xs text-gray-500">Unit Binaan</span>
                                    </div>
                                    <div class="bg-gray-50 p-3 rounded-xl border border-gray-100 text-center">
                                        <span class="block text-2xl font-bold text-gray-800">{{ stats.territory_count }}</span>
                                        <span class="text-xs text-gray-500">Wilayah</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 text-sm">
                            <h3 class="font-bold text-gray-800 mb-4">Informasi Legalitas</h3>
                            <ul class="space-y-3">
                                <li class="flex flex-col border-b border-gray-50 pb-2">
                                    <span class="text-xs text-gray-400">Nomor SK</span>
                                    <span class="font-medium text-gray-700">{{ organization.sk_number || '-' }}</span>
                                </li>
                                <li class="flex flex-col border-b border-gray-50 pb-2">
                                    <span class="text-xs text-gray-400">Tanggal Berdiri</span>
                                    <span class="font-medium text-gray-700">{{ formatDate(organization.establishment_date) }}</span>
                                </li>
                                <li class="flex flex-col">
                                    <span class="text-xs text-gray-400">Kontak Resmi</span>
                                    <span class="font-medium text-emerald-600 truncate">{{ organization.email || organization.phone || '-' }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="lg:col-span-8 bg-white rounded-2xl border border-gray-200 shadow-sm min-h-[600px] flex flex-col">
                        <TabView> 
                            
                            <TabPanel header="Struktur Pengurus">
                                <div class="p-6">
                                    <div v-if="organization.structures.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div v-for="struct in organization.structures" :key="struct.id" class="flex items-center gap-4 p-4 rounded-xl border border-gray-100 bg-gray-50 hover:bg-white hover:shadow-md transition">
                                            <Avatar :image="struct.member?.photo_path ? '/storage/' + struct.member.photo_path : null" 
                                                    :icon="!struct.member?.photo_path ? 'pi pi-user' : null" 
                                                    size="large" shape="circle" class="bg-white shadow-sm" />
                                            <div>
                                                <span class="text-xs font-bold text-emerald-600 uppercase bg-emerald-50 px-2 py-0.5 rounded border border-emerald-100">{{ struct.position_name }}</span>
                                                <h4 class="font-bold text-gray-800 mt-1">{{ struct.member?.full_name || 'Nama Tidak Ditemukan' }}</h4>
                                                <p class="text-xs text-gray-400">NBM: {{ struct.member?.nbm || '-' }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-else class="flex flex-col items-center justify-center py-12 text-gray-400 border-2 border-dashed border-gray-100 rounded-xl bg-gray-50/50">
                                        <i class="pi pi-users text-4xl mb-2 text-gray-300"></i>
                                        <p>Data struktur pengurus belum diinput.</p>
                                    </div>
                                </div>
                            </TabPanel>

                            <TabPanel :header="labels.memberTab">
                                <div class="p-6">
                                    <DataTable :value="members.data" :rows="10" stripedRows size="small" class="text-sm">
                                        <template #empty><div class="text-center p-4 text-gray-500">Tidak ada data personel terdaftar.</div></template>
                                        <Column field="full_name" header="Nama">
                                            <template #body="{data}">
                                                <div class="flex items-center gap-2">
                                                    <Avatar :label="data.full_name[0]" shape="circle" size="small" class="bg-blue-50 text-blue-600 font-bold shrink-0" />
                                                    <div>
                                                        <div class="font-bold text-gray-700">{{ data.full_name }}</div>
                                                        <div class="text-[10px] text-gray-400">{{ data.nbm || 'Non-NBM' }}</div>
                                                    </div>
                                                </div>
                                            </template>
                                        </Column>
                                        <Column header="Unit Asal" style="width: 25%">
                                            <template #body="{data}">
                                                <span class="text-xs font-medium text-gray-600 bg-gray-100 px-2 py-1 rounded border border-gray-200 whitespace-nowrap">
                                                    {{ data.organization_unit?.name || '-' }}
                                                </span>
                                            </template>
                                        </Column>
                                        <Column header="Status" style="width: 15%">
                                            <template #body="{data}">
                                                <Tag :value="data.status" :severity="data.status==='ACTIVE'?'success':'secondary'" class="!text-[10px] !px-2" rounded />
                                            </template>
                                        </Column>
                                    </DataTable>
                                    <div class="flex justify-center mt-6" v-if="members.links.length > 3">
                                        <div class="flex gap-1 bg-white p-1 rounded-lg border border-gray-200 shadow-sm">
                                            <Link v-for="(link, i) in members.links" :key="i" :href="link.url||'#'" 
                                                  class="px-3 py-1.5 text-xs font-medium rounded transition-colors" 
                                                  :class="link.active ? 'bg-gray-900 text-white shadow' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900'" 
                                                  v-html="link.label.replace('«', '').replace('»', '')" />
                                        </div>
                                    </div>
                                </div>
                            </TabPanel>

                            <TabPanel :header="labels.extraTab">
                                <div class="p-6">
                                    
                                    <div v-if="organization.category === 'STRUKTURAL'" class="space-y-6">
                                        <div v-if="organization.children.length > 0">
                                            <h4 class="text-sm font-bold text-gray-700 mb-3">Unit Organisasi Binaan</h4>
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                                <div v-for="child in organization.children" :key="child.id" 
                                                     class="p-4 border rounded-xl hover:bg-emerald-50/30 hover:border-emerald-200 transition cursor-pointer group bg-white"
                                                     @click="router.visit(route('organizations.show', child.id))">
                                                    <div class="flex justify-between items-center mb-2">
                                                        <Tag :value="child.type" severity="warning" class="!text-[10px]" />
                                                        <i class="pi pi-arrow-right text-gray-300 group-hover:text-emerald-500"></i>
                                                    </div>
                                                    <h4 class="font-bold text-gray-800">{{ child.name }}</h4>
                                                    <span class="text-xs text-gray-500 mt-1 block">{{ child.members_count }} Anggota</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div v-if="organization.territories.length > 0">
                                            <h4 class="text-sm font-bold text-gray-700 mb-3">Cakupan Wilayah Administratif</h4>
                                            <div class="flex flex-wrap gap-2">
                                                <div v-for="geo in organization.territories" :key="geo.id" 
                                                     class="bg-white text-gray-700 px-3 py-2 rounded-lg border border-gray-200 text-sm font-medium flex items-center gap-2 shadow-sm">
                                                    <div class="w-6 h-6 rounded-full bg-orange-100 flex items-center justify-center text-orange-600">
                                                        <i class="pi pi-map-marker text-xs"></i>
                                                    </div>
                                                    {{ geo.name }}
                                                </div>
                                            </div>
                                        </div>
                                        <div v-if="organization.children.length === 0 && organization.territories.length === 0" class="text-center py-12 text-gray-400 bg-gray-50 rounded-xl border border-dashed">
                                            Data wilayah/unit belum tersedia.
                                        </div>
                                    </div>

                                    <div v-else class="space-y-4">
                                        <div v-if="organization.description" class="prose prose-sm max-w-none text-gray-600 leading-relaxed">
                                            {{ organization.description }}
                                        </div>
                                        <div v-else class="text-center py-12 text-gray-400 bg-gray-50 rounded-xl border border-dashed border-gray-200">
                                            <i class="pi pi-file-edit text-3xl mb-2 text-gray-300"></i>
                                            <p>Belum ada deskripsi profil untuk unit ini.</p>
                                            <Button label="Tambahkan Deskripsi" text size="small" class="mt-2" @click="router.visit(route('organizations.edit', organization.id))" />
                                        </div>
                                    </div>

                                </div>
                            </TabPanel>

                        </TabView>
                    </div>

                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
:deep(.p-tabview-nav) { border-color: #f1f5f9 !important; }
:deep(.p-tabview-nav li .p-tabview-nav-link) { color: #64748b; font-weight: 600; padding: 1.25rem 1.5rem; }
:deep(.p-tabview-nav li.p-highlight .p-tabview-nav-link) { color: #059669; border-color: #059669; }
</style>