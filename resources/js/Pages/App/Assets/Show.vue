<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

// --- LOCAL COMPONENTS ---
import AssetStatusBadge from './Components/AssetStatusBadge.vue';

// --- PRIMEVUE IMPORTS ---
import TabView from 'primevue/tabview';
import TabPanel from 'primevue/tabpanel';
import Image from 'primevue/image';
import Tag from 'primevue/tag';
import Timeline from 'primevue/timeline';
import Button from 'primevue/button'; // Optional, jika ingin pakai button primevue

const props = defineProps({
    asset: Object,
    qr_code_url: String,
});

// --- HELPERS ---
const formatRupiah = (num) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(num);
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
};

const formatLabel = (key) => {
    return key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
};

const coverImage = computed(() => {
    if (!props.asset.images || props.asset.images.length === 0) {
        return '/images/asset-default.jpg';
    }
    const primary = props.asset.images.find(img => img.is_primary);
    return primary ? `/storage/${primary.image_path}` : `/storage/${props.asset.images[0].image_path}`;
});

const galleryImages = computed(() => {
    return props.asset.images || []; 
});
</script>

<template>
    <Head :title="`Detail Aset - ${asset.name}`" />

    <AppLayout>
        <div class="max-w-7xl mx-auto space-y-8 pb-10">
            
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 border-b border-gray-200 pb-6">
                <div>
                    <nav class="flex items-center gap-2 text-sm text-gray-500 mb-2">
                        <Link :href="route('dashboard')" class="hover:text-blue-600 transition">Dashboard</Link>
                        <i class="pi pi-angle-right text-xs"></i>
                        <Link :href="route('assets.index')" class="hover:text-blue-600 transition">Aset</Link>
                        <i class="pi pi-angle-right text-xs"></i>
                        <span class="text-gray-800 font-medium">Detail</span>
                    </nav>
                    <div class="flex items-center gap-3">
                        <h1 class="text-3xl font-bold text-gray-900 tracking-tight">{{ asset.name }}</h1>
                        <AssetStatusBadge :status="asset.status" />
                    </div>
                    <p class="text-gray-500 mt-1 flex items-center gap-2 font-mono text-sm">
                        <i class="pi pi-hashtag text-gray-400"></i> {{ asset.inventory_code }}
                    </p>
                </div>
                
                <div class="flex gap-3">
                    <Link :href="route('assets.index')" class="px-5 py-2.5 bg-white border border-gray-300 text-gray-700 rounded-xl text-sm font-semibold hover:bg-gray-50 hover:text-gray-900 transition shadow-sm flex items-center gap-2">
                        <i class="pi pi-arrow-left"></i> Kembali
                    </Link>
                    <Link :href="route('assets.edit', asset.id)" class="px-5 py-2.5 bg-gray-900 text-white rounded-xl text-sm font-semibold hover:bg-gray-800 transition shadow-lg shadow-gray-200 flex items-center gap-2">
                        <i class="pi pi-pencil"></i> Edit Aset
                    </Link>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                
                <div class="lg:col-span-4 space-y-6">
                    
                    <div class="bg-white rounded-2xl shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07),0_10px_20px_-2px_rgba(0,0,0,0.04)] border border-gray-100 overflow-hidden relative group">
                        <div class="relative aspect-[4/3] bg-gray-100 overflow-hidden border-b border-gray-100">
                            <Image :src="coverImage" alt="Cover" preview imageClass="w-full h-full object-cover transition duration-700 group-hover:scale-105" />
                            <div class="absolute bottom-3 left-3">
                                <Tag :value="asset.condition" severity="contrast" class="uppercase text-[10px] tracking-wider shadow-sm font-bold backdrop-blur-md bg-black/70 border border-white/20" />
                            </div>
                        </div>

                        <div class="p-5">
                            <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl p-5 text-white shadow-lg relative overflow-hidden">
                                <div class="absolute top-0 right-0 -mt-2 -mr-2 w-16 h-16 bg-white opacity-5 rounded-full blur-xl"></div>
                                <p class="text-xs text-slate-400 font-medium uppercase tracking-wider mb-1">Nilai Aset Saat Ini</p>
                                <p class="text-2xl font-bold tracking-tight">{{ formatRupiah(asset.acquisition_value) }}</p>
                                <div class="mt-4 pt-4 border-t border-slate-700/50 flex justify-between items-center text-xs text-slate-400">
                                    <span>Tgl Perolehan</span>
                                    <span class="text-slate-200 font-mono">{{ formatDate(asset.acquisition_date) }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-px bg-gray-100 border-t border-gray-100">
                            <a :href="qr_code_url" target="_blank" class="bg-white p-4 flex flex-col items-center justify-center hover:bg-blue-50 transition group/btn cursor-pointer">
                                <i class="pi pi-qrcode text-2xl text-gray-400 mb-2 group-hover/btn:text-blue-600 transition"></i>
                                <span class="text-xs font-semibold text-gray-600 group-hover/btn:text-blue-700">Cetak QR</span>
                            </a>
                            <div class="bg-white p-4 flex flex-col items-center justify-center hover:bg-blue-50 transition group/btn cursor-help" :title="asset.location?.name">
                                <i class="pi pi-map-marker text-2xl text-gray-400 mb-2 group-hover/btn:text-red-500 transition"></i>
                                <span class="text-xs font-semibold text-gray-600 group-hover/btn:text-red-600 text-center line-clamp-1">
                                    {{ asset.location?.name || 'Tanpa Lokasi' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <h3 class="text-sm font-bold text-gray-900 mb-5 flex items-center gap-2 uppercase tracking-wider">
                            <i class="pi pi-box text-blue-500"></i> Metadata
                        </h3>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between group">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-gray-50 flex items-center justify-center text-gray-400 group-hover:bg-blue-50 group-hover:text-blue-500 transition">
                                        <i class="pi pi-tag text-xs"></i>
                                    </div>
                                    <span class="text-sm text-gray-500 font-medium">Kategori</span>
                                </div>
                                <span class="text-sm font-semibold text-gray-800">{{ asset.category }}</span>
                            </div>
                            
                            <div class="flex items-center justify-between group">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-gray-50 flex items-center justify-center text-gray-400 group-hover:bg-green-50 group-hover:text-green-500 transition">
                                        <i class="pi pi-wallet text-xs"></i>
                                    </div>
                                    <span class="text-sm text-gray-500 font-medium">Sumber</span>
                                </div>
                                <span class="text-sm font-semibold text-gray-800">{{ asset.source_of_acquisition }}</span>
                            </div>

                            <div class="flex items-center justify-between group">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-gray-50 flex items-center justify-center text-gray-400 group-hover:bg-purple-50 group-hover:text-purple-500 transition">
                                        <i class="pi pi-user text-xs"></i>
                                    </div>
                                    <span class="text-sm text-gray-500 font-medium">PIC / User</span>
                                </div>
                                <span class="text-sm font-semibold text-gray-800">{{ asset.user?.name || '-' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-8">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden min-h-[600px] flex flex-col">
                        
                        <TabView class="flex-1 flex flex-col">
                            
                            <TabPanel>
                                <template #header>
                                    <div class="flex items-center gap-2">
                                        <i class="pi pi-list"></i>
                                        <span>Spesifikasi</span>
                                    </div>
                                </template>
                                
                                <div class="p-6 md:p-8">
                                    <div v-if="asset.specifications && Object.keys(asset.specifications).length > 0">
                                        <h4 class="text-gray-900 font-bold mb-4 text-lg">Detail Teknis</h4>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div v-for="(value, key) in asset.specifications" :key="key" 
                                                class="flex flex-col p-4 rounded-xl border border-gray-100 bg-gray-50/50 hover:bg-white hover:shadow-md hover:border-blue-100 transition duration-200 group">
                                                <span class="text-[11px] uppercase tracking-wider font-semibold text-gray-400 mb-1 group-hover:text-blue-500 transition">
                                                    {{ formatLabel(key) }}
                                                </span>
                                                <span class="text-gray-800 font-medium break-words">
                                                    {{ value || '-' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div v-else class="flex flex-col items-center justify-center py-12 text-center bg-gray-50 rounded-xl border-2 border-dashed border-gray-200">
                                        <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mb-3 text-gray-400">
                                            <i class="pi pi-sliders-h text-xl"></i>
                                        </div>
                                        <p class="text-gray-500 font-medium">Belum ada spesifikasi teknis.</p>
                                    </div>

                                    <div class="mt-8 pt-8 border-t border-gray-100">
                                        <h4 class="text-gray-900 font-bold mb-3 flex items-center gap-2">
                                            <i class="pi pi-align-left text-blue-500"></i> Deskripsi & Catatan
                                        </h4>
                                        <div class="bg-blue-50/50 rounded-xl p-5 text-gray-700 text-sm leading-relaxed border border-blue-100/50">
                                            {{ asset.description || 'Tidak ada deskripsi tambahan untuk aset ini.' }}
                                        </div>
                                    </div>
                                </div>
                            </TabPanel>

                            <TabPanel>
                                <template #header>
                                    <div class="flex items-center gap-2">
                                        <i class="pi pi-images"></i>
                                        <span>Galeri Foto</span>
                                        <span class="bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full text-xs font-bold">{{ galleryImages.length }}</span>
                                    </div>
                                </template>

                                <div class="p-6 md:p-8">
                                    <div v-if="galleryImages.length > 0" class="grid grid-cols-2 md:grid-cols-3 gap-4">
                                        <div v-for="img in galleryImages" :key="img.id" class="rounded-xl overflow-hidden shadow-sm border border-gray-100 relative group aspect-[4/3] bg-gray-100 cursor-pointer">
                                            <Image :src="`/storage/${img.image_path}`" alt="Asset Image" preview imageClass="w-full h-full object-cover transition duration-500 group-hover:scale-110" />
                                            
                                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition duration-300 pointer-events-none"></div>
                                            
                                            <div v-if="img.is_primary" class="absolute top-2 left-2 bg-blue-600 text-white text-[10px] uppercase font-bold px-2 py-1 rounded shadow-sm z-10">
                                                Cover Utama
                                            </div>
                                        </div>
                                    </div>
                                    <div v-else class="flex flex-col items-center justify-center py-16 text-center bg-gray-50 rounded-xl border-2 border-dashed border-gray-200">
                                        <div class="w-16 h-16 bg-white rounded-full shadow-sm flex items-center justify-center mb-4 text-gray-300">
                                            <i class="pi pi-image text-3xl"></i>
                                        </div>
                                        <h3 class="text-gray-900 font-medium">Galeri Kosong</h3>
                                        <p class="text-gray-500 text-sm mt-1">Belum ada foto tambahan yang diunggah.</p>
                                    </div>
                                </div>
                            </TabPanel>

                            <TabPanel>
                                <template #header>
                                    <div class="flex items-center gap-2">
                                        <i class="pi pi-folder"></i>
                                        <span>Dokumen</span>
                                    </div>
                                </template>

                                <div class="p-6 md:p-8">
                                    <div v-if="asset.documents && asset.documents.length > 0" class="grid grid-cols-1 gap-3">
                                        <div v-for="doc in asset.documents" :key="doc.id" class="flex items-center justify-between p-4 bg-white border border-gray-200 rounded-xl hover:shadow-md hover:border-blue-300 transition group">
                                            <div class="flex items-center gap-4">
                                                <div class="w-12 h-12 bg-red-50 text-red-500 rounded-lg flex items-center justify-center group-hover:bg-red-100 transition shrink-0">
                                                    <i class="pi pi-file-pdf text-xl"></i> 
                                                </div>
                                                <div>
                                                    <h4 class="font-bold text-gray-800 text-sm group-hover:text-blue-600 transition mb-1">{{ doc.name }}</h4>
                                                    <div class="flex items-center gap-2 text-xs text-gray-500">
                                                        <span class="bg-gray-100 px-1.5 py-0.5 rounded text-gray-600 font-mono">{{ doc.document_number || 'N/A' }}</span>
                                                        <span v-if="doc.expiry_date" class="text-red-500 flex items-center gap-1">
                                                            <i class="pi pi-calendar-times"></i> Exp: {{ formatDate(doc.expiry_date) }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <a :href="`/storage/${doc.file_path}`" target="_blank" class="w-10 h-10 flex items-center justify-center text-gray-400 hover:text-white hover:bg-blue-600 rounded-full transition border border-gray-100 hover:border-blue-600" title="Download">
                                                <i class="pi pi-download"></i>
                                            </a>
                                        </div>
                                    </div>
                                    <div v-else class="flex flex-col items-center justify-center py-16 text-center bg-gray-50 rounded-xl border-2 border-dashed border-gray-200">
                                        <div class="w-16 h-16 bg-white rounded-full shadow-sm flex items-center justify-center mb-4 text-gray-300">
                                            <i class="pi pi-folder-open text-3xl"></i>
                                        </div>
                                        <h3 class="text-gray-900 font-medium">Tidak Ada Dokumen</h3>
                                        <p class="text-gray-500 text-sm mt-1">Sertifikat, faktur, atau garansi belum diunggah.</p>
                                    </div>
                                </div>
                            </TabPanel>

                            <TabPanel>
                                <template #header>
                                    <div class="flex items-center gap-2">
                                        <i class="pi pi-history"></i>
                                        <span>Riwayat</span>
                                    </div>
                                </template>

                                <div class="p-6 md:p-8">
                                    <div v-if="asset.active_loan" class="mb-8 relative overflow-hidden bg-gradient-to-r from-blue-600 to-blue-500 rounded-2xl p-6 text-white shadow-lg">
                                        <div class="absolute right-0 top-0 opacity-10 transform translate-x-10 -translate-y-10">
                                            <i class="pi pi-clock" style="font-size: 8rem;"></i>
                                        </div>
                                        <div class="relative z-10 flex items-start gap-4">
                                            <div class="p-3 bg-white/20 backdrop-blur-sm rounded-xl">
                                                <i class="pi pi-sync text-2xl animate-spin-slow"></i>
                                            </div>
                                            <div>
                                                <h4 class="font-bold text-lg mb-1">Sedang Dipinjam</h4>
                                                <p class="text-blue-50 text-sm mb-3">
                                                    Aset ini sedang berada di tangan <strong>{{ asset.active_loan.borrower_name || asset.active_loan.user?.name }}</strong>.
                                                </p>
                                                <div class="inline-flex items-center gap-2 bg-black/20 px-3 py-1.5 rounded-lg text-xs font-mono">
                                                    <i class="pi pi-calendar"></i>
                                                    Rencana Kembali: {{ formatDate(asset.active_loan.return_date_plan) }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div v-if="asset.loans && asset.loans.length > 0">
                                        <Timeline :value="asset.loans" align="left" class="custom-timeline">
                                            <template #content="slotProps">
                                                <div class="mb-8 ml-4">
                                                    <div class="bg-white border border-gray-200 rounded-xl p-4 hover:shadow-md transition duration-200 hover:border-blue-300 relative">
                                                        <div class="absolute left-0 top-4 -ml-1.5 w-3 h-3 bg-white border-l border-b border-gray-200 transform rotate-45"></div>

                                                        <div class="flex justify-between items-start mb-2">
                                                            <div>
                                                                <span class="text-xs font-bold px-2 py-1 rounded uppercase tracking-wide" 
                                                                    :class="slotProps.item.status === 'COMPLETED' ? 'bg-green-100 text-green-700' : (slotProps.item.status === 'BORROWED' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600')">
                                                                    {{ slotProps.item.status }}
                                                                </span>
                                                                <span class="text-xs text-gray-400 ml-2 font-mono">{{ formatDate(slotProps.item.loan_date) }}</span>
                                                            </div>
                                                        </div>
                                                        
                                                        <h5 class="text-gray-900 font-bold text-sm mb-1">
                                                            {{ slotProps.item.borrower_name || slotProps.item.user?.name }}
                                                        </h5>
                                                        <p class="text-gray-600 text-sm italic mb-2">
                                                            "{{ slotProps.item.description || 'Tidak ada keterangan' }}"
                                                        </p>

                                                        <div v-if="slotProps.item.return_date_actual" class="mt-3 pt-3 border-t border-gray-100 flex items-center gap-2 text-xs text-green-700 font-medium">
                                                            <i class="pi pi-check-circle"></i>
                                                            Dikembalikan: {{ formatDate(slotProps.item.return_date_actual) }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>
                                            
                                            <template #marker="slotProps">
                                                <span class="flex w-8 h-8 items-center justify-center rounded-full ring-4 ring-white shadow-sm z-10"
                                                    :class="slotProps.item.status === 'COMPLETED' ? 'bg-green-500 text-white' : 'bg-blue-500 text-white'">
                                                    <i class="pi text-xs" :class="slotProps.item.status === 'COMPLETED' ? 'pi-check' : 'pi-arrow-right-arrow-left'"></i>
                                                </span>
                                            </template>
                                        </Timeline>
                                    </div>
                                    <div v-else class="flex flex-col items-center justify-center py-16 text-center text-gray-400">
                                        <i class="pi pi-history text-4xl mb-3 opacity-20"></i>
                                        <p>Belum ada riwayat peminjaman.</p>
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
/* Modern Tabs Styling */
:deep(.p-tabview-nav) {
    background-color: #ffffff;
    border-bottom: 1px solid #f3f4f6;
    padding: 0 1rem;
}

:deep(.p-tabview-nav-link) {
    background: transparent !important;
    border: none !important;
    color: #64748b !important; /* slate-500 */
    font-weight: 600;
    font-size: 0.95rem;
    padding: 1.25rem 1.5rem !important;
    transition: all 0.2s ease;
    border-bottom: 2px solid transparent !important;
}

:deep(.p-tabview-nav-link:hover) {
    color: #1e293b !important; /* slate-800 */
    background-color: #f8fafc !important; /* slate-50 */
}

:deep(.p-highlight .p-tabview-nav-link) {
    color: #2563eb !important; /* blue-600 */
    border-bottom-color: #2563eb !important;
}

:deep(.p-tabview-panels) {
    padding: 0;
    flex: 1;
}

/* Timeline Connector Styling */
:deep(.p-timeline-event-connector) {
    background-color: #e2e8f0; /* slate-200 */
    width: 2px;
}

:deep(.p-timeline-event-opposite) {
    flex: 0;
    padding: 0;
}

/* Image Preview */
:deep(.p-image-preview-indicator) {
    background-color: rgba(15, 23, 42, 0.6); /* slate-900/60 */
    backdrop-filter: blur(4px);
    color: white;
}

.animate-spin-slow {
    animation: spin 3s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>