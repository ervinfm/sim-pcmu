<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
const page = usePage();
const settings = page.props.site_settings;

// State untuk menu mobile (Hamburger)
const showingNavigationDropdown = ref(false);
</script>

<template>
    <div class="min-h-screen bg-gray-50 flex flex-col font-sans">
        <nav class="bg-white border-b border-gray-100 sticky top-0 z-50 shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-20">
                    <div class="flex">
                        <div class="shrink-0 flex items-center gap-3">
                            <Link :href="route('public.home')" class="flex items-center gap-2">
                                <img src="/images/logomu.png" 
                                     class="block h-12 w-auto" 
                                     alt="Logo Muhammadiyah" />
                                <div class="flex flex-col">
                                    <span class="font-bold text-lg text-emerald-800 leading-tight">PCM MUARA AMAN</span>
                                    <span class="text-xs text-gray-500 font-medium tracking-wider">KABUPATEN LEBONG</span>
                                </div>
                            </Link>
                        </div>
                    </div>

                    <div class="hidden sm:flex sm:items-center sm:ms-6 space-x-8">
                        <Link :href="route('public.home')" 
                              class="text-gray-600 hover:text-emerald-600 font-medium transition"
                              :class="{ 'text-emerald-700 font-bold': $page.component === 'Public/Home' }">
                            Beranda
                        </Link>
                        <Link :href="route('public.profile')" 
                              class="text-gray-600 hover:text-emerald-600 font-medium transition">
                            Profil & Sejarah
                        </Link>
                         <Link :href="route('public.structure')" 
                              class="text-gray-600 hover:text-emerald-600 font-medium transition">
                            Struktur & AUM
                        </Link>
                        <Link :href="route('public.news.index')" 
                              class="text-gray-600 hover:text-emerald-600 font-medium transition">
                            Berita
                        </Link>
                        
                        <Link :href="route('login')" 
                              class="px-5 py-2 bg-emerald-600 text-white rounded-full font-semibold hover:bg-emerald-700 transition shadow-lg shadow-emerald-200">
                            Login
                        </Link>
                    </div>

                    <div class="-me-2 flex items-center sm:hidden">
                        <button @click="showingNavigationDropdown = !showingNavigationDropdown"
                                class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none transition duration-150 ease-in-out">
                            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                <path :class="{ 'hidden': showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                <path :class="{ 'hidden': !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <div :class="{ 'block': showingNavigationDropdown, 'hidden': !showingNavigationDropdown }" class="sm:hidden bg-white border-t">
                <div class="pt-2 pb-3 space-y-1">
                    <Link :href="route('public.home')" class="block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300 transition duration-150 ease-in-out">
                        Beranda
                    </Link>
                    <Link :href="route('login')" class="block w-full ps-3 pe-4 py-2 text-emerald-600 font-bold">
                        Login System
                    </Link>
                </div>
            </div>
        </nav>

        <main class="flex-grow">
            <slot />
        </main>

        <footer class="bg-gray-900 text-white pt-12 pb-8 mt-auto">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
                    <div>
                        <h3 class="text-xl font-bold mb-4 text-emerald-400">PCM MUARA AMAN</h3>
                        <p class="text-gray-400 text-sm leading-relaxed">
                            Gerakan Islam Dakwah Amar Ma'ruf Nahi Mungkar dan Tajdid, bersumber pada Al-Qur'an dan As-Sunnah.
                        </p>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold mb-4">Hubungi Kami</h3>
                        <ul class="text-gray-400 text-sm space-y-2">
                            <li>📍 Gedung Dakwah Muhammadiyah Lt. 2</li>
                            <li>Jl. Kampung Jawa No. 123, Pasar Muara Aman</li>
                            <li>✉️ pcmmuaraaman@gmail.com</li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold mb-4">Tautan Cepat</h3>
                        <div class="flex flex-col space-y-2 text-sm text-gray-400">
                             <a href="https://muhammadiyah.or.id/" target="_blank" class="hover:text-white">Pimpinan Pusat</a>
                             <a href="#" class="hover:text-white">Pimpinan Wilayah Bengkulu</a>
                             <a href="#" class="hover:text-white">Pimpinan Daerah Lebong</a>
                        </div>
                    </div>
                </div>
                <div class="border-t border-gray-800 pt-8 text-center text-xs text-gray-500">
                    &copy; 2026 Pimpinan Cabang Muhammadiyah Muara Aman. All rights reserved.
                </div>
            </div>
        </footer>
    </div>
</template>