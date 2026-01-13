<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import "leaflet/dist/leaflet.css";
import { LMap, LTileLayer, LMarker, LPopup } from "@vue-leaflet/vue-leaflet";

defineProps({ locations: Array });

const zoom = ref(13);
const center = ref([-3.123321, 102.217312]); // Sesuaikan default center Muara Aman
</script>

<template>
    <Head title="Peta Dakwah" />
    <AppLayout title="Peta Sebaran Dakwah">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Peta Sebaran Dakwah</h1>
            <p class="text-gray-500 text-sm">Visualisasi Geografis AUM dan Kantor Layanan.</p>
        </div>

        <div class="h-[600px] w-full bg-gray-100 rounded-2xl overflow-hidden shadow-sm border border-gray-200 z-0 relative">
            <l-map ref="map" v-model:zoom="zoom" :center="center" :use-global-leaflet="false">
                <l-tile-layer url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"></l-tile-layer>
                
                <l-marker v-for="loc in locations" :key="loc.id" :lat-lng="[loc.latitude, loc.longitude]">
                    <l-popup>
                        <div class="p-1 min-w-[150px]">
                            <h3 class="font-bold text-emerald-700">{{ loc.name }}</h3>
                            <span class="text-xs bg-gray-100 px-2 py-0.5 rounded font-bold text-gray-500">{{ loc.type }}</span>
                            <p class="text-xs mt-2 text-gray-600">{{ loc.address || 'Alamat belum diisi' }}</p>
                        </div>
                    </l-popup>
                </l-marker>
            </l-map>
        </div>
    </AppLayout>
</template>