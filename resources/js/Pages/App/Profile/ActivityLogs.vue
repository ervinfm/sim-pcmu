<script setup>
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Timeline from 'primevue/timeline';
import Card from 'primevue/card';
import Badge from 'primevue/badge';
import Tag from 'primevue/tag';

const props = defineProps({ logs: Array, user: Object });

// Helper untuk Styling Log
const getLogStyle = (action) => {
    switch (action) {
        case 'LOGIN':
            return { icon: 'pi pi-sign-in', color: 'text-green-500', bg: 'bg-green-100', border: 'border-green-200' };
        case 'LOGOUT':
            return { icon: 'pi pi-power-off', color: 'text-gray-500', bg: 'bg-gray-100', border: 'border-gray-200' };
        case 'UPDATE_PASSWORD':
            return { icon: 'pi pi-lock', color: 'text-red-500', bg: 'bg-red-100', border: 'border-red-200' };
        case 'UPDATE_ACCOUNT':
            return { icon: 'pi pi-user-edit', color: 'text-blue-500', bg: 'bg-blue-100', border: 'border-blue-200' };
        default:
            return { icon: 'pi pi-info-circle', color: 'text-emerald-500', bg: 'bg-emerald-100', border: 'border-emerald-200' };
    }
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('id-ID', {
        day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit'
    });
};
</script>

<template>
    <Head title="Riwayat Aktivitas" />
    <AppLayout>
        <div class="max-w-6xl mx-auto">
            
            <div class="mb-8">
                <h1 class="text-3xl font-black text-gray-800 tracking-tight">Security Log</h1>
                <p class="text-gray-500 mt-1">Jejak audit keamanan dan aktivitas akun Anda.</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mt-6">
                
                <div class="lg:col-span-4 space-y-6">
                    <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-3xl p-6 text-white shadow-xl overflow-hidden relative">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-white/5 rounded-full blur-3xl -mr-10 -mt-10"></div>
                        
                        <div class="relative z-10">
                            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4">Device Terkini</h3>
                            <div class="flex items-center gap-4 mb-6">
                                <div class="w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
                                    <i class="pi pi-desktop text-2xl text-emerald-400"></i>
                                </div>
                                <div>
                                    <p class="font-bold text-lg">IP Address Saat Ini</p>
                                    <p class="text-sm text-slate-400 font-mono">192.168.1.1 (Local)</p>
                                </div>
                            </div>
                            
                            <div class="border-t border-white/10 pt-4 mt-4">
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-slate-400">Status Akun</span>
                                    <Tag value="SECURE" severity="success" class="!bg-emerald-500/20 !text-emerald-300 border border-emerald-500/50" />
                                </div>
                                <div class="flex justify-between items-center text-sm mt-3">
                                    <span class="text-slate-400">Total Aktivitas</span>
                                    <span class="font-bold font-mono">{{ logs.length }} Records</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-blue-50 rounded-2xl p-5 border border-blue-100">
                        <div class="flex gap-3">
                            <i class="pi pi-shield text-blue-600 text-xl mt-1"></i>
                            <div>
                                <h4 class="font-bold text-blue-800 text-sm">Tips Keamanan</h4>
                                <p class="text-xs text-blue-600 mt-1 leading-relaxed">
                                    Jika Anda melihat aktivitas mencurigakan atau login dari perangkat yang tidak dikenal, segera ganti password Anda.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-8">
                    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-8 min-h-[500px]">
                        
                        <h3 class="font-bold text-gray-800 mb-6 flex items-center gap-2">
                            <i class="pi pi-history"></i> Aktivitas Terakhir
                        </h3>

                        <Timeline :value="logs" align="left" class="customized-timeline">
                            <template #marker="slotProps">
                                <span class="flex w-9 h-9 items-center justify-center rounded-full shadow-sm ring-4 ring-white" 
                                      :class="[getLogStyle(slotProps.item.action).bg, getLogStyle(slotProps.item.action).color]">
                                    <i :class="getLogStyle(slotProps.item.action).icon" class="text-sm"></i>
                                </span>
                            </template>
                            
                            <template #content="slotProps">
                                <div class="mb-8 ml-2 group">
                                    <div class="bg-white border border-gray-100 rounded-xl p-4 shadow-sm hover:shadow-md hover:border-emerald-100 transition-all duration-300 relative">
                                        
                                        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-2">
                                            <span class="text-xs font-bold px-2 py-1 rounded bg-gray-50 border border-gray-200 uppercase tracking-wider text-gray-600 mb-2 sm:mb-0">
                                                {{ slotProps.item.action.replace('_', ' ') }}
                                            </span>
                                            <span class="text-xs text-gray-400 flex items-center gap-1">
                                                <i class="pi pi-clock"></i> {{ formatDate(slotProps.item.created_at) }}
                                            </span>
                                        </div>

                                        <p class="text-sm text-gray-700 font-medium leading-relaxed">
                                            {{ slotProps.item.description }}
                                        </p>

                                        <div class="mt-3 pt-3 border-t border-gray-50 flex items-center gap-4 text-xs text-gray-400">
                                            <span class="flex items-center gap-1 bg-gray-50 px-2 py-1 rounded">
                                                <i class="pi pi-globe text-emerald-500"></i> {{ slotProps.item.ip_address || 'Unknown IP' }}
                                            </span>
                                            <span class="flex items-center gap-1 bg-gray-50 px-2 py-1 rounded">
                                                <i class="pi pi-desktop text-purple-500"></i> {{ slotProps.item.device_info || 'Unknown Device' }}
                                            </span>
                                        </div>

                                    </div>
                                </div>
                            </template>
                        </Timeline>

                        <div v-if="logs.length === 0" class="text-center py-16">
                            <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="pi pi-file text-3xl text-gray-300"></i>
                            </div>
                            <p class="text-gray-500 font-medium">Belum ada riwayat aktivitas yang tercatat.</p>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Custom Timeline Connector Line */
:deep(.p-timeline-event-connector) {
    background-color: #f1f5f9; /* Slate 100 */
    width: 2px;
}

/* Hover Effect pada Connector */
:deep(.p-timeline-event:hover .p-timeline-event-connector) {
    background-color: #10b981; /* Emerald 500 */
    transition: background-color 0.3s;
}
</style>