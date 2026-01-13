<script setup>
import { Head } from '@inertiajs/vue3';
import WebLayout from '@/Layouts/WebLayout.vue';
import { computed } from 'vue';
import Card from 'primevue/card';
import Tag from 'primevue/tag';
import Button from 'primevue/button';

const props = defineProps({
    structure: Object // Data PCM beserta children (PRM & AUM)
});

// 1. Memisahkan Data PRM (Pimpinan Ranting)
const prms = computed(() => {
    return props.structure?.children?.filter(unit => unit.type === 'PRM') || [];
});

// 2. Memisahkan Data AUM (Masjid, Sekolah, Klinik, dll)
const aums = computed(() => {
    return props.structure?.children?.filter(unit => unit.type !== 'PRM') || [];
});

// Helper: Ikon berdasarkan tipe unit
const getIcon = (type) => {
    switch (type) {
        case 'MASJID': return 'pi pi-moon';     // Ikon Bulan Bintang
        case 'SEKOLAH': return 'pi pi-book';    // Ikon Buku
        case 'KLINIK': return 'pi pi-heart';    // Ikon Kesehatan
        case 'PANTI': return 'pi pi-home';      // Ikon Rumah
        default: return 'pi pi-building';
    }
};

// Helper: Warna Tag berdasarkan tipe
const getSeverity = (type) => {
    switch (type) {
        case 'MASJID': return 'success'; // Hijau
        case 'SEKOLAH': return 'info';    // Biru
        case 'KLINIK': return 'danger';   // Merah
        default: return 'warning';
    }
};

// Helper: Link Google Maps
const openMap = (lat, lng) => {
    if(lat && lng) {
        window.open(`https://www.google.com/maps/search/?api=1&query=${lat},${lng}`, '_blank');
    }
};
</script>

<template>
    <Head title="Struktur & AUM" />

    <WebLayout>
        <div class="bg-emerald-50 py-12 border-b border-emerald-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h1 class="text-3xl md:text-4xl font-bold text-emerald-900 mb-2">Struktur & Amal Usaha</h1>
                <p class="text-gray-600">Jaringan Pimpinan Ranting dan Unit Layanan Muhammadiyah Muara Aman</p>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-16">
            
            <div class="text-center">
                <div class="inline-block p-6 bg-white rounded-2xl shadow-lg border-t-4 border-emerald-600 relative">
                    <div class="absolute -bottom-16 left-1/2 -translate-x-1/2 w-1 h-16 bg-gray-300 hidden md:block"></div>
                    
                    <h2 class="text-2xl font-bold text-gray-800">PCM MUARA AMAN</h2>
                    <p class="text-emerald-600 font-semibold mb-2">Pimpinan Tingkat Cabang</p>
                    <div class="text-sm text-gray-500">
                        <i class="pi pi-map-marker"></i> {{ structure?.address || 'Alamat Kantor' }}
                    </div>
                </div>
            </div>

            <div>
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600">
                        <i class="pi pi-sitemap text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold text-gray-800">Pimpinan Ranting (PRM)</h3>
                        <p class="text-gray-500 text-sm">Terdiri dari {{ prms.length }} Ranting yang membawahi beberapa desa.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 relative z-10">
                    <div v-for="prm in prms" :key="prm.id" class="group">
                        <Card class="h-full border border-gray-100 hover:shadow-lg transition duration-300 hover:border-emerald-200">
                            <template #header>
                                <div class="bg-gray-50 p-4 border-b border-gray-100 flex justify-between items-center">
                                    <h4 class="font-bold text-gray-800 group-hover:text-emerald-700 transition">
                                        {{ prm.name }}
                                    </h4>
                                    <span class="text-xs font-mono bg-white border px-2 py-1 rounded text-gray-500">
                                        Ranting
                                    </span>
                                </div>
                            </template>
                            <template #content>
                                <div class="text-sm text-gray-600 mb-2 font-semibold">Wilayah Desa / Kelurahan:</div>
                                <ul class="space-y-2">
                                    <li v-for="village in prm.villages" :key="village.id" class="flex items-start gap-2 text-sm text-gray-500">
                                        <i class="pi pi-check-circle text-emerald-500 mt-0.5"></i>
                                        <span>{{ village.name }}</span>
                                    </li>
                                    <li v-if="prm.villages?.length === 0" class="text-xs text-gray-400 italic">
                                        Belum ada data desa.
                                    </li>
                                </ul>
                            </template>
                        </Card>
                    </div>
                </div>
            </div>

            <div>
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600">
                        <i class="pi pi-building text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold text-gray-800">Amal Usaha (AUM)</h3>
                        <p class="text-gray-500 text-sm">Unit layanan pendidikan, sosial, dan keagamaan.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div v-for="aum in aums" :key="aum.id" 
                         class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-start gap-4 hover:shadow-md transition">
                        
                        <div class="w-12 h-12 rounded-lg bg-gray-50 flex items-center justify-center shrink-0">
                            <i :class="getIcon(aum.type)" class="text-2xl text-gray-600"></i>
                        </div>
                        
                        <div class="flex-grow">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="font-bold text-gray-900">{{ aum.name }}</h4>
                                    <p class="text-sm text-gray-500 line-clamp-1">{{ aum.address || 'Alamat belum diinput' }}</p>
                                </div>
                                <Tag :value="aum.type" :severity="getSeverity(aum.type)" class="text-[10px]" />
                            </div>
                            
                            <div class="mt-3 flex gap-2">
                                <Button v-if="aum.latitude && aum.longitude" 
                                        label="Peta Lokasi" icon="pi pi-map" size="small" outlined 
                                        @click="openMap(aum.latitude, aum.longitude)" />
                                
                                <Button v-if="aum.facebook" 
                                        icon="pi pi-facebook" size="small" severity="secondary" text rounded 
                                        @click="window.open(`https://facebook.com/${aum.facebook}`, '_blank')" />
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="aums.length === 0" class="text-center py-8 bg-gray-50 rounded-lg border border-dashed text-gray-400">
                    Belum ada data Amal Usaha.
                </div>
            </div>

        </div>
    </WebLayout>
</template>