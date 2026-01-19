<script setup>
import { Head, Link } from '@inertiajs/vue3';
import WebLayout from '@/Layouts/WebLayout.vue';// Misal: import PublicLayout from '@/Layouts/PublicLayout.vue'; 

const props = defineProps({
    asset: Object,
});

const coverImage = props.asset.images.find(img => img.is_primary)?.image_path 
    ? `/storage/${props.asset.images.find(img => img.is_primary).image_path}`
    : '/images/asset-default.jpg';
</script>

<template>
    <Head :title="asset.name" />
    <WebLayout>
        <div class="min-h-screen bg-gray-50 flex flex-col items-center pt-6 sm:pt-0">
            
            <div class="w-full max-w-md bg-white shadow-lg overflow-hidden md:rounded-2xl mt-4">
                <div class="relative h-64 bg-gray-200">
                    <img :src="coverImage" class="w-full h-full object-cover">
                    <div class="absolute top-0 left-0 w-full h-full bg-gradient-to-t from-black/70 to-transparent"></div>
                    
                    <div class="absolute bottom-4 left-4 text-white">
                        <span class="bg-blue-600 text-xs font-bold px-2 py-1 rounded mb-1 inline-block">
                            {{ asset.category }}
                        </span>
                        <h1 class="text-2xl font-bold leading-tight">{{ asset.name }}</h1>
                        <p class="text-sm opacity-90 font-mono">{{ asset.inventory_code }}</p>
                    </div>
                </div>
    
                <div class="flex border-b border-gray-100">
                    <div class="flex-1 p-4 text-center border-r border-gray-100">
                        <p class="text-xs text-gray-500 uppercase">Kondisi</p>
                        <p class="font-bold text-gray-800">{{ asset.condition }}</p>
                    </div>
                    <div class="flex-1 p-4 text-center">
                        <p class="text-xs text-gray-500 uppercase">Status</p>
                        <span v-if="asset.active_loan" class="text-orange-600 font-bold text-sm">Dipinjam</span>
                        <span v-else class="text-green-600 font-bold text-sm">Tersedia</span>
                    </div>
                </div>
    
                <div class="p-6 space-y-4">
                    <div>
                        <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                            <i class="pi pi-map-marker text-red-500"></i> Lokasi Saat Ini
                        </h3>
                        <p class="text-gray-600 mt-1">{{ asset.location?.name || 'Tidak diketahui' }}</p>
                    </div>
    
                    <div v-if="asset.specifications">
                        <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2 mb-2">
                            <i class="pi pi-list text-blue-500"></i> Spesifikasi
                        </h3>
                        <div class="bg-gray-50 rounded-lg p-3 text-sm space-y-2 border border-gray-100">
                            <div v-for="(val, key) in asset.specifications" :key="key" class="flex justify-between">
                                <span class="text-gray-500 capitalize">{{ key.replace(/_/g, ' ') }}</span>
                                <span class="font-medium text-gray-800 text-right">{{ val }}</span>
                            </div>
                        </div>
                    </div>
    
                    <div v-if="asset.description" class="pt-2">
                        <h3 class="text-sm font-bold text-gray-900 mb-1">Deskripsi</h3>
                        <p class="text-gray-600 text-sm leading-relaxed">{{ asset.description }}</p>
                    </div>
                </div>
    
                <div class="p-4 bg-gray-50 border-t border-gray-100 text-center">
                    <p class="text-xs text-gray-400 mb-3">Data ini milik Sistem Inventaris Muhammadiyah</p>
                    
                    <Link :href="route('login')" class="text-blue-600 text-sm font-medium hover:underline">
                        Login Pengelola
                    </Link>
                </div>
            </div>
        </div>
    </WebLayout>
</template>