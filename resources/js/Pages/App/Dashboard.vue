<script setup>
import { onMounted, ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Chart from 'primevue/chart';

// --- IMPORT LEAFLET ---
import "leaflet/dist/leaflet.css";
import { LMap, LTileLayer, LMarker, LPopup, LTooltip } from "@vue-leaflet/vue-leaflet";

const props = defineProps({
    stats: Array,
    role: String,
    charts: Object,
    mapLocations: Array
});

// --- STATE ---
const zoom = ref(13);
const center = ref([-3.123321, 102.217312]); 

// --- CHART OPTIONS (Minimalis & Modern) ---
const commonOptions = ref({
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            labels: {
                usePointStyle: true,
                padding: 20,
                font: { family: 'Inter, sans-serif', size: 11 }
            },
            position: 'bottom'
        }
    },
    layout: { padding: 10 }
});

const barOptions = ref({
    ...commonOptions.value,
    scales: {
        y: {
            beginAtZero: true,
            grid: { color: '#f3f4f6', borderDash: [5, 5] },
            ticks: { font: { family: 'Inter', size: 10 } }
        },
        x: {
            grid: { display: false },
            ticks: { font: { family: 'Inter', size: 10 } }
        }
    }
});

// Refs Data Chart
const genderChart = ref(null);
const rantingChart = ref(null);
const villageChart = ref(null);
const eduChart = ref(null);
const aumChart = ref(null);
const trainingChart = ref(null);

// Colors Palette (Modern Pastels)
const getTheme = (colorName) => {
    const themes = {
        blue:    { bg: 'bg-blue-600',    light: 'bg-blue-50',    text: 'text-blue-600',    icon: 'text-blue-100' },
        emerald: { bg: 'bg-emerald-600', light: 'bg-emerald-50', text: 'text-emerald-600', icon: 'text-emerald-100' },
        orange:  { bg: 'bg-orange-500',  light: 'bg-orange-50',  text: 'text-orange-600',  icon: 'text-orange-100' },
        purple:  { bg: 'bg-purple-600',  light: 'bg-purple-50',  text: 'text-purple-600',  icon: 'text-purple-100' },
        cyan:    { bg: 'bg-cyan-600',    light: 'bg-cyan-50',    text: 'text-cyan-600',    icon: 'text-cyan-100' },
        gray:    { bg: 'bg-gray-600',    light: 'bg-gray-50',    text: 'text-gray-600',    icon: 'text-gray-100' },
    };
    return themes[colorName] || themes.gray; // Default ke gray jika warna tidak ditemukan
};

onMounted(() => {
    if (props.charts) {
        // 1. Gender (Donut)
        if (props.charts.gender) {
            genderChart.value = {
                labels: Object.keys(props.charts.gender).map(k => k === 'L' ? 'Laki-laki' : 'Perempuan'),
                datasets: [{
                    data: Object.values(props.charts.gender),
                    backgroundColor: ['#3B82F6', '#EC4899'],
                    hoverOffset: 4
                }]
            };
        }

        // 2. Ranting (Bar)
        if (props.charts.ranting && Object.keys(props.charts.ranting).length > 0) {
            rantingChart.value = {
                labels: Object.keys(props.charts.ranting),
                datasets: [{
                    label: 'Anggota',
                    data: Object.values(props.charts.ranting),
                    backgroundColor: '#10B981',
                    borderRadius: 8,
                    barThickness: 20
                }]
            };
        }

        // 3. Desa (Bar)
        if (props.charts.village) {
            villageChart.value = {
                labels: Object.keys(props.charts.village),
                datasets: [{
                    label: 'Domisili',
                    data: Object.values(props.charts.village),
                    backgroundColor: '#F59E0B',
                    borderRadius: 8,
                    barThickness: 20
                }]
            };
        }

        // 4. Pendidikan (Bar)
        if (props.charts.education) {
            eduChart.value = {
                labels: Object.keys(props.charts.education),
                datasets: [{
                    label: 'Jumlah',
                    data: Object.values(props.charts.education),
                    backgroundColor: '#6366F1',
                    borderRadius: 8,
                    barThickness: 25
                }]
            };
        }

        // 5. AUM & Training (Doughnut)
        if (props.charts.aum) {
            aumChart.value = {
                labels: Object.keys(props.charts.aum),
                datasets: [{
                    data: Object.values(props.charts.aum),
                    backgroundColor: ['#8B5CF6', '#E5E7EB'],
                    borderWidth: 0
                }]
            };
        }
        if (props.charts.training) {
            trainingChart.value = {
                labels: Object.keys(props.charts.training),
                datasets: [{
                    data: Object.values(props.charts.training),
                    backgroundColor: ['#F97316', '#E5E7EB'],
                    borderWidth: 0
                }]
            };
        }
    }
});
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout>
        <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-3xl font-black text-gray-800 tracking-tight">Dashboard Overview</h2>
                <p class="text-gray-500 text-sm mt-1">
                    Visualisasi data real-time <span class="font-bold text-emerald-600">PCM Muara Aman</span>.
                </p>
            </div>
            <div class="flex items-center gap-2 px-4 py-2 bg-white rounded-full shadow-sm border border-gray-100 text-xs font-medium text-gray-500">
                <span class="relative flex h-2 w-2">
                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                  <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                </span>
                Live Data Updated
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
            <div v-for="(stat, index) in stats" :key="index" 
                 class="relative bg-white p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-[0_20px_50px_-12px_rgba(0,0,0,0.1)] hover:-translate-y-1 transition-all duration-300 group overflow-hidden">
                
                <div class="absolute -right-6 -top-6 w-32 h-32 rounded-full opacity-10 blur-3xl transition-all group-hover:scale-150"
                     :class="getTheme(stat.color_name).bg"></div>

                <div class="relative z-10 flex justify-between items-start">
                    <div>
                        <p class="text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-2">{{ stat.label }}</p>
                        <h3 class="text-3xl font-black text-gray-800 tracking-tight leading-none mb-4">{{ stat.value }}</h3>
                        
                        <div class="inline-flex items-center gap-1.5 px-2 py-1 rounded-md bg-gray-50 border border-gray-100 group-hover:bg-white group-hover:shadow-sm transition-colors">
                            <i class="pi pi-arrow-up text-[9px]" :class="getTheme(stat.color_name).text"></i>
                            <span class="text-[10px] font-bold text-gray-600">2.5%</span>
                            <span class="text-[9px] text-gray-400 font-medium">bulan ini</span>
                        </div>
                    </div>

                    <div class="w-14 h-14 rounded-2xl flex items-center justify-center shadow-lg shadow-gray-100 transform group-hover:rotate-6 transition-all duration-300"
                         :class="getTheme(stat.color_name).bg">
                        <i :class="stat.icon" class="text-white text-2xl"></i>
                    </div>
                </div>
                
                <div class="absolute bottom-0 left-0 h-1 w-0 group-hover:w-full transition-all duration-500 ease-out"
                     :class="getTheme(stat.color_name).bg"></div>
            </div>
        </div>

        <div v-if="charts" class="space-y-8">
            
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
                
                <div class="xl:col-span-2 bg-white rounded-3xl border border-gray-100 shadow-sm p-1 relative overflow-hidden group">
                    <div class="absolute top-5 left-6 z-10 bg-white/80 backdrop-blur-md px-4 py-2 rounded-xl shadow-sm border border-white/50">
                        <h3 class="font-bold text-gray-800 flex items-center gap-2">
                            <i class="pi pi-map text-emerald-500"></i> Sebaran Wilayah
                        </h3>
                    </div>
                    
                    <div class="h-[450px] w-full rounded-[1.3rem] overflow-hidden relative z-0">
                        <l-map ref="map" v-model:zoom="zoom" :center="center" :use-global-leaflet="false">
                            <l-tile-layer url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"></l-tile-layer>
                            <l-marker v-for="loc in mapLocations" :key="loc.id" :lat-lng="[loc.lat, loc.lng]">
                                <l-tooltip>{{ loc.name }}</l-tooltip>
                                <l-popup>
                                    <div class="text-center min-w-[120px]">
                                        <strong class="text-emerald-700 block text-sm">{{ loc.name }}</strong>
                                        <span class="text-[10px] bg-gray-100 px-2 py-0.5 rounded text-gray-500 font-bold">{{ loc.type }}</span>
                                        <div class="mt-2 text-xs font-bold">{{ loc.members_count }} Anggota</div>
                                    </div>
                                </l-popup>
                            </l-marker>
                        </l-map>
                    </div>
                </div>

                <div class="space-y-6 flex flex-col">
                    
                    <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm flex-1 flex flex-col justify-center items-center relative">
                        <h4 class="absolute top-6 left-6 text-sm font-bold text-gray-700">Demografi Gender</h4>
                        <div class="w-full max-w-[220px] relative mt-4">
                            <Chart type="doughnut" :data="genderChart" :options="commonOptions" />
                            <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                                <i class="pi pi-users text-2xl text-gray-300"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm flex-1 flex flex-col justify-center items-center relative">
                        <h4 class="absolute top-6 left-6 text-sm font-bold text-gray-700">Status Perkaderan</h4>
                        <div class="w-full max-w-[220px] mt-4">
                            <Chart type="doughnut" :data="trainingChart" :options="commonOptions" />
                        </div>
                    </div>

                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                
                <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="font-bold text-gray-800">Top Wilayah Basis Anggota</h3>
                        <button class="text-xs bg-gray-50 hover:bg-gray-100 px-3 py-1 rounded-lg transition">Export</button>
                    </div>
                    <div class="h-[300px]">
                        <Chart v-if="rantingChart" type="bar" :data="rantingChart" :options="barOptions" class="h-full" />
                        <Chart v-else type="bar" :data="villageChart" :options="barOptions" class="h-full" />
                    </div>
                </div>

                <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="font-bold text-gray-800">Jenjang Pendidikan</h3>
                        <i class="pi pi-book text-gray-300"></i>
                    </div>
                    <div class="h-[300px]">
                        <Chart type="bar" :data="eduChart" :options="barOptions" class="h-full" />
                    </div>
                </div>

            </div>

        </div>

        <div v-else class="bg-gradient-to-br from-white to-gray-50 p-12 rounded-3xl border border-dashed border-gray-300 text-center shadow-inner">
            <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center mx-auto shadow-sm mb-6">
                <i class="pi pi-chart-pie text-4xl text-emerald-200"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-800">Mode Ringkas</h3>
            <p class="text-gray-500 mt-2 max-w-md mx-auto">
                Anda sedang dalam mode tinjauan operasional. Fokus pada pengelolaan kas dan inventaris unit Anda.
            </p>
        </div>

    </AppLayout>
</template>

<style scoped>
/* Leaflet Specifics for Rounded Corners */
.leaflet-container {
    font-family: 'Inter', sans-serif !important;
    background: #f8fafc;
}

/* Custom Chart Tooltip styling could be added here if using external tooltip plugin */
</style>