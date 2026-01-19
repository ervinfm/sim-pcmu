<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import AssetStatusBadge from './Components/AssetStatusBadge.vue';

// PROPS
const props = defineProps({
    assets: Array,      
    stats: Object,
    locations: Array,
    categories: Object,
});

// STATE
const search = ref('');
const category = ref('');
const locationId = ref('');
const condition = ref('');
const currentPage = ref(1);
const itemsPerPage = 12;
const viewMode = ref('grid');

// STATE SELECTION (BATCH PRINT)
const selectedIds = ref([]);
const isSelectAll = ref(false);

// FILTERING
const filteredAssets = computed(() => {
    return props.assets.filter(asset => {
        const searchTerm = search.value.toLowerCase();
        const matchesSearch = asset.name.toLowerCase().includes(searchTerm) || 
                              asset.inventory_code.toLowerCase().includes(searchTerm);
        const matchesCategory = category.value ? asset.category === category.value : true;
        const matchesLocation = locationId.value ? asset.asset_location_id === locationId.value : true;
        const matchesCondition = condition.value ? asset.condition === condition.value : true;

        return matchesSearch && matchesCategory && matchesLocation && matchesCondition;
    });
});

// PAGINATION
const totalPages = computed(() => Math.ceil(filteredAssets.value.length / itemsPerPage));
const paginatedAssets = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage;
    return filteredAssets.value.slice(start, start + itemsPerPage);
});

// WATCHERS
watch([search, category, locationId, condition], () => { currentPage.value = 1; });

// Sync Checkbox "Select All"
watch(selectedIds, (newIds) => {
    if (paginatedAssets.value.length === 0) {
        isSelectAll.value = false;
        return;
    }
    const allOnPageSelected = paginatedAssets.value.every(asset => newIds.includes(asset.id));
    isSelectAll.value = allOnPageSelected;
});

// Reset selection saat pindah halaman
watch(currentPage, () => {
    isSelectAll.value = false;
});


// HELPERS
const nextPage = () => { if (currentPage.value < totalPages.value) currentPage.value++; };
const prevPage = () => { if (currentPage.value > 1) currentPage.value--; };
const goToPage = (p) => currentPage.value = p;
const formatRupiah = (num) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(num);

const getCoverImage = (asset) => {
    if (asset.images?.length > 0) {
        const primary = asset.images.find(img => img.is_primary) || asset.images[0];
        return '/storage/' + primary.image_path;
    }
    return '/images/asset-default.jpg';
};

const goToDetail = (id) => {
    router.get(route('assets.show', id));
};

// DROPDOWN
const openDropdownId = ref(null);
const toggleDropdown = (id) => openDropdownId.value = (openDropdownId.value === id) ? null : id;

// LOGIC BATCH PRINT
const toggleSelectAll = () => {
    if (isSelectAll.value) {
        const pageIds = paginatedAssets.value.map(a => a.id);
        pageIds.forEach(id => {
            if (!selectedIds.value.includes(id)) {
                selectedIds.value.push(id);
            }
        });
    } else {
        const pageIds = paginatedAssets.value.map(a => a.id);
        selectedIds.value = selectedIds.value.filter(id => !pageIds.includes(id));
    }
};

const printBatch = () => {
    if (selectedIds.value.length === 0) return alert('Pilih minimal 1 aset!');
    router.post(route('assets.print-batch'), { 
        ids: selectedIds.value,
        layout: 'A4_STICKER' 
    });
};

</script>

<template>
    <Head title="Manajemen Aset" />

    <AppLayout>
        <div class="space-y-6 relative">
            
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Data Aset</h1>
                    <p class="text-gray-500 text-sm mt-1">
                        Menampilkan {{ filteredAssets.length }} data dari total {{ props.assets.length }} aset.
                    </p>
                </div>
                
                <div class="flex items-center gap-2">
                    
                    <transition 
                        enter-active-class="transform ease-out duration-300 transition"
                        enter-from-class="translate-x-4 opacity-0"
                        enter-to-class="translate-x-0 opacity-100"
                        leave-active-class="transform ease-in duration-200 transition"
                        leave-from-class="translate-x-0 opacity-100"
                        leave-to-class="translate-x-4 opacity-0"
                    >
                        <button 
                            v-if="selectedIds.length > 0"
                            @click="printBatch" 
                            class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2.5 rounded-lg flex items-center gap-2 text-sm font-medium shadow-md transition border border-gray-700 mr-2"
                        >
                            <i class="pi pi-print"></i>
                            <span>Cetak ({{ selectedIds.length }})</span>
                        </button>
                    </transition>

                    <Link 
                        :href="route('assets.loans.index')" 
                        class="bg-white border border-gray-300 text-gray-700 px-4 py-2.5 rounded-lg flex items-center gap-2 text-sm font-medium shadow-sm hover:bg-gray-50 transition"
                        title="Sirkulasi Peminjaman"
                    >
                        <i class="pi pi-sync text-blue-600"></i>
                        <span class="hidden md:inline">Peminjaman</span>
                    </Link>

                    <Link 
                        :href="route('assets.references.index')" 
                        class="bg-white border border-gray-300 text-gray-700 px-4 py-2.5 rounded-lg flex items-center gap-2 text-sm font-medium shadow-sm hover:bg-gray-50 transition"
                        title="Kelola Lokasi & Satuan"
                    >
                        <i class="pi pi-cog text-gray-500"></i>
                        <span class="hidden md:inline">Data Master</span>
                    </Link>

                    <Link :href="route('assets.create')" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg flex items-center gap-2 text-sm font-medium shadow-md transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                        <span class="hidden sm:inline">Tambah Aset</span>
                    </Link>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Aset</p>
                        <p class="text-2xl font-bold text-gray-800 mt-1">{{ stats.total_assets }} <span class="text-sm font-normal text-gray-400">Unit</span></p>
                    </div>
                    <div class="p-3 bg-blue-50 rounded-full text-blue-600">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                    </div>
                </div>
                <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Nilai Aset (IDR)</p>
                        <p class="text-2xl font-bold text-gray-800 mt-1">{{ formatRupiah(stats.total_value) }}</p>
                    </div>
                    <div class="p-3 bg-green-50 rounded-full text-green-600">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                </div>
                <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Perlu Perbaikan</p>
                        <p class="text-2xl font-bold text-red-600 mt-1">{{ stats.maintenance_count }} <span class="text-sm font-normal text-gray-400">Unit</span></p>
                    </div>
                    <div class="p-3 bg-red-50 rounded-full text-red-600">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                    </div>
                </div>
            </div>

            <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex flex-col lg:flex-row gap-4 sticky top-0 z-10">
                <div class="flex flex-col md:flex-row gap-3 w-full lg:w-auto flex-1">
                    <div class="relative w-full md:w-64">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </span>
                        <input v-model="search" type="text" placeholder="Cari nama / kode..." class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm transition shadow-sm">
                    </div>

                    <select v-model="category" class="w-full md:w-40 py-2 border border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 shadow-sm cursor-pointer">
                        <option value="">Semua Kategori</option>
                        <option v-for="(schema, key) in categories" :key="key" :value="key">{{ schema.label }}</option>
                    </select>

                    <select v-model="locationId" class="w-full md:w-40 py-2 border border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 shadow-sm cursor-pointer">
                        <option value="">Semua Lokasi</option>
                        <option v-for="loc in locations" :key="loc.id" :value="loc.id">{{ loc.name }}</option>
                    </select>

                    <select v-model="condition" class="w-full md:w-40 py-2 border border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 shadow-sm cursor-pointer">
                        <option value="">Semua Kondisi</option>
                        <option value="GOOD">Baik</option>
                        <option value="SLIGHTLY_DAMAGED">Rusak Ringan</option>
                        <option value="HEAVILY_DAMAGED">Rusak Berat</option>
                    </select>
                </div>

                <div class="flex items-center gap-2 bg-gray-100 p-1 rounded-lg">
                    <button @click="viewMode = 'grid'" :class="viewMode === 'grid' ? 'bg-white shadow text-blue-600' : 'text-gray-500'" class="p-2 rounded transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    </button>
                    <button @click="viewMode = 'list'" :class="viewMode === 'list' ? 'bg-white shadow text-blue-600' : 'text-gray-500'" class="p-2 rounded transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>
                </div>
            </div>

            <div v-if="filteredAssets.length === 0" class="flex flex-col items-center justify-center py-20 bg-white rounded-xl border border-dashed border-gray-300">
                <div class="p-4 bg-gray-50 rounded-full text-gray-400 mb-4">
                    <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" /></svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900">Tidak ada aset ditemukan</h3>
                <p class="text-gray-500 text-sm mt-1">Coba ubah filter Anda.</p>
            </div>

            <div v-else-if="viewMode === 'grid'" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div v-for="asset in paginatedAssets" 
                     :key="asset.id" 
                     @click="goToDetail(asset.id)"
                     class="group bg-white rounded-xl shadow-sm hover:shadow-lg border border-gray-100 overflow-hidden transition relative cursor-pointer"
                     :class="{'ring-2 ring-blue-500': selectedIds.includes(asset.id)}"
                >
                    <div class="absolute top-3 left-3 z-20" @click.stop>
                        <input type="checkbox" :value="asset.id" v-model="selectedIds" class="w-5 h-5 rounded border-gray-300 text-blue-600 focus:ring-blue-500 shadow-sm cursor-pointer bg-white">
                    </div>

                    <div class="relative h-48 bg-gray-100">
                        <img :src="getCoverImage(asset)" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                        
                        <div class="absolute top-3 right-3">
                            <AssetStatusBadge :status="asset.status" />
                        </div>
                        <div class="absolute bottom-3 left-3">
                            <AssetStatusBadge :status="asset.condition" />
                        </div>
                    </div>
                    
                    <div class="p-4">
                        <h3 class="font-bold text-gray-800 line-clamp-1" :title="asset.name">{{ asset.name }}</h3>
                        <p class="text-xs text-gray-500 font-mono mt-1">{{ asset.inventory_code }}</p>
                        
                        <div class="mt-3 text-sm text-gray-600 space-y-1">
                            <div class="flex items-center"><span class="w-4 mr-2">📍</span>{{ asset.location?.name || '-' }}</div>
                            <div class="flex items-center"><span class="w-4 mr-2">🏷️</span>{{ categories[asset.category]?.label || asset.category }}</div>
                        </div>

                        <div class="pt-3 border-t border-gray-100 flex justify-between items-center mt-auto">
                            <span class="text-sm font-bold text-gray-800">{{ formatRupiah(asset.acquisition_value) }}</span>
                            
                            <div class="relative">
                                <button @click.stop="toggleDropdown(asset.id)" class="p-2 rounded-full hover:bg-gray-100 text-gray-500 transition focus:outline-none">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
                                    </svg>
                                </button>

                                <div v-if="openDropdownId === asset.id" class="absolute bottom-full right-0 mb-2 w-48 bg-white rounded-lg shadow-xl border border-gray-100 z-50 overflow-hidden text-left origin-bottom-right">
                                    <Link :href="route('assets.show', asset.id)" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50">Detail Aset</Link>
                                    <a :href="route('assets.print-label', asset.id)" target="_blank" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50">Cetak QR Code</a>
                                    <div class="border-t border-gray-100 my-1"></div>
                                    <Link :href="route('assets.edit', asset.id)" class="block px-4 py-2 text-sm text-gray-700 hover:bg-yellow-50">Edit Data</Link>
                                    <Link :href="route('assets.destroy', asset.id)" method="delete" as="button" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50" onclick="return confirm('Hapus aset ini?')">Hapus</Link>
                                </div>
                                <div v-if="openDropdownId === asset.id" @click.stop="openDropdownId = null" class="fixed inset-0 z-40"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else class="bg-white rounded-xl shadow-sm border overflow-visible">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left whitespace-nowrap">
                        <thead class="bg-gray-50 text-gray-500 border-b">
                            <tr>
                                <th class="py-3 px-4 w-10 text-center">
                                    <input type="checkbox" v-model="isSelectAll" @change="toggleSelectAll" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer w-4 h-4">
                                </th>
                                <th class="py-3 px-4">Aset</th>
                                <th class="py-3 px-4">Kategori</th>
                                <th class="py-3 px-4">Lokasi</th>
                                <th class="py-3 px-4">Status</th>
                                <th class="py-3 px-4">Kondisi</th>
                                <th class="py-3 px-4">Nilai</th>
                                <th class="py-3 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="asset in paginatedAssets" :key="asset.id" class="hover:bg-gray-50 transition" :class="{'bg-blue-50': selectedIds.includes(asset.id)}">
                                <td class="py-3 px-4 text-center" @click.stop>
                                    <input type="checkbox" :value="asset.id" v-model="selectedIds" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer w-4 h-4">
                                </td>

                                <td class="py-3 px-4 flex items-center gap-3">
                                    <img :src="getCoverImage(asset)" class="w-10 h-10 rounded object-cover border">
                                    <div>
                                        <Link :href="route('assets.show', asset.id)" class="font-bold text-gray-800 hover:text-blue-600">{{ asset.name }}</Link>
                                        <div class="text-xs text-gray-500 font-mono">{{ asset.inventory_code }}</div>
                                    </div>
                                </td>
                                <td class="py-3 px-4">{{ categories[asset.category]?.label || asset.category }}</td>
                                <td class="py-3 px-4">{{ asset.location?.name || '-' }}</td>
                                <td class="py-3 px-4">
                                    <AssetStatusBadge :status="asset.status" />
                                </td>
                                <td class="py-3 px-4">
                                    <AssetStatusBadge :status="asset.condition" />
                                </td>
                                <td class="py-3 px-4">{{ formatRupiah(asset.acquisition_value) }}</td>
                                <td class="py-3 px-4 text-right">
                                    <div class="relative inline-block text-left">
                                        <button @click="toggleDropdown(asset.id)" class="p-2 rounded-full hover:bg-gray-200 text-gray-500 transition">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
                                            </svg>
                                        </button>
                                        <div v-if="openDropdownId === asset.id" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl border border-gray-100 z-50 overflow-hidden text-left origin-top-right">
                                            <Link :href="route('assets.show', asset.id)" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50">Detail Aset</Link>
                                            <a :href="route('assets.print-label', asset.id)" target="_blank" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50">Cetak QR Code</a>
                                            <div class="border-t border-gray-100 my-1"></div>
                                            <Link :href="route('assets.edit', asset.id)" class="block px-4 py-2 text-sm text-gray-700 hover:bg-yellow-50">Edit Data</Link>
                                            <Link :href="route('assets.destroy', asset.id)" method="delete" as="button" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50" onclick="return confirm('Hapus aset ini?')">Hapus</Link>
                                        </div>
                                        <div v-if="openDropdownId === asset.id" @click="openDropdownId = null" class="fixed inset-0 z-40"></div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div v-if="totalPages > 1" class="flex flex-col md:flex-row justify-between items-center mt-6 pt-4 border-t border-gray-200 gap-4">
                <div class="text-sm text-gray-500">Hal {{ currentPage }} dari {{ totalPages }}</div>
                <div class="flex gap-1">
                    <button @click="prevPage" :disabled="currentPage === 1" class="px-3 py-1 border rounded hover:bg-gray-50 disabled:opacity-50 text-sm">Prev</button>
                    <button v-for="page in totalPages" :key="page" @click="goToPage(page)" v-show="page === 1 || page === totalPages || (page >= currentPage - 1 && page <= currentPage + 1)" class="px-3 py-1 border rounded text-sm transition" :class="currentPage === page ? 'bg-blue-600 text-white' : 'hover:bg-gray-50'">{{ page }}</button>
                    <button @click="nextPage" :disabled="currentPage === totalPages" class="px-3 py-1 border rounded hover:bg-gray-50 disabled:opacity-50 text-sm">Next</button>
                </div>
            </div>

        </div>
    </AppLayout>
</template>