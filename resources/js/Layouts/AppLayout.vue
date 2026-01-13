<script setup>
import { ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import AppSidebar from './Partials/AppSidebar.vue';
import AppTopbar from './Partials/AppTopbar.vue';
import Toast from 'primevue/toast';
const page = usePage();
const settings = page.props.site_settings;

// State Sidebar Mobile
const isSidebarOpen = ref(false);
</script>

<template>
    <div class="min-h-screen bg-[#f8fafc] font-sans text-gray-900 flex relative overflow-hidden">
        
        <div class="fixed inset-0 overflow-hidden pointer-events-none z-0">
            <div class="absolute top-[-10%] left-[-10%] w-[50%] h-[50%] rounded-full bg-emerald-400/5 blur-[120px]"></div>
            <div class="absolute bottom-[-10%] right-[-10%] w-[50%] h-[50%] rounded-full bg-blue-400/5 blur-[120px]"></div>
        </div>

        <Toast />

        <aside :class="[
            'fixed inset-y-0 left-0 z-50 w-72 transform transition-all duration-500 cubic-bezier(0.34, 1.56, 0.64, 1)',
            'bg-white/80 supports-[backdrop-filter]:bg-white/60 backdrop-blur-xl border-r border-white/50 shadow-sm', // Glassmorphism
            'lg:static lg:translate-x-0', 
            isSidebarOpen ? 'translate-x-0' : '-translate-x-full'
        ]">
            <AppSidebar />
        </aside>

        <div v-if="isSidebarOpen" 
             @click="isSidebarOpen = false"
             class="fixed inset-0 bg-gray-900/20 backdrop-blur-sm z-40 lg:hidden transition-opacity">
        </div>

        <div class="flex-1 flex flex-col min-w-0 relative z-10 h-screen overflow-hidden">
            
            <AppTopbar @toggleSidebar="isSidebarOpen = !isSidebarOpen" />

            <main class="flex-1 overflow-y-auto flex flex-col custom-scrollbar">
                
                <div class="p-4 lg:p-6 flex-1">
                    <div class="max-w-[1600px] mx-auto animate-fade-in-up">
                        <slot />
                    </div>
                </div>

                <footer class="mt-auto py-3 px-6 lg:px-8 border-t border-gray-200/60 bg-white/40 backdrop-blur-sm">
                    <div class="max-w-[1600px] mx-auto flex flex-col md:flex-row justify-between items-center gap-4 text-xs font-medium text-gray-500">
                        
                        <div class="flex items-center gap-2">
                            <span>&copy; 2026 Sistem Informasi Manajemen.</span>
                            <span class="hidden md:inline text-gray-300">|</span>
                            <span class="hidden md:inline text-emerald-600 font-bold">PCM Muara Aman</span>
                        </div>

                        <div class="flex items-center gap-6">
                            <a href="#" class="hover:text-emerald-600 transition-colors">Bantuan</a>
                            <a href="#" class="hover:text-emerald-600 transition-colors">Kebijakan Privasi</a>
                            <div class="flex items-center gap-1.5 px-2 py-1 rounded-md bg-gray-100/80 border border-gray-200/50">
                                <i class="pi pi-code text-[10px]"></i>
                                <span>v1.0.0</span>
                            </div>
                        </div>

                    </div>
                </footer>

            </main>
        </div>

    </div>
</template>

<style scoped>
/* Animasi Masuk Halus */
.animate-fade-in-up {
    animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Scrollbar Cantik */
.custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(16, 185, 129, 0.2); border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(16, 185, 129, 0.4); }
</style>