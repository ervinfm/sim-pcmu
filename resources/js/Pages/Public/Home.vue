<script setup>
import { Head, Link } from '@inertiajs/vue3';
import WebLayout from '@/Layouts/WebLayout.vue';
import Button from 'primevue/button';
import DateTimeWidget from '@/Components/DateTimeWidget.vue';
import Card from 'primevue/card';

// Props diterima dari HomeController
const props = defineProps({
    pcm: Object,
    latestPosts: Array,
    settings: Object, // Berisi slider, sambutan, dll
});

// Helper Function: Menentukan Sumber Gambar
const getKetuaImage = () => {
    // 1. Cek apakah ada data foto di database (diupload admin)
    if (props.settings?.foto_ketua) {
        return '/storage/' + props.settings.foto_ketua;
    }
    
    // 2. Jika tidak ada, pakai gambar bawaan di folder public
    return '/images/ketua_default.jpg';
};

// Helper untuk format tanggal
const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: 'numeric', month: 'long', year: 'numeric'
    });
};
</script>

<template>
    <Head title="Muhammadiyah " />

    <WebLayout>
        <div class="relative bg-emerald-900 text-white overflow-hidden">
            <div class="absolute inset-0 opacity-10">
                <svg class="h-full w-full" viewBox="0 0 100 100" preserveAspectRatio="none">
                    <path d="M0 100 C 20 0 50 0 100 100 Z" fill="white" />
                </svg>
            </div>
            
            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 md:py-32 flex flex-col items-center text-center">
                <div class="animate-fade-in-up">
            
                   <div class="absolute top-6 left-1/2 -translate-x-1/2 z-20">
                        <DateTimeWidget />
                    </div>

                    <h1 class="text-4xl md:text-6xl font-extrabold tracking-tight mb-4 leading-tight">
                        Demi Kemajuan <span class="text-yellow-400">Muara Aman</span>,<br/>
                        Mencerahkan Semesta
                    </h1>
                    <p class="max-w-2xl mx-auto text-lg md:text-xl text-emerald-100 mb-10">
                        Portal Resmi Pimpinan Cabang Muhammadiyah Muara Aman. 
                        Pusat informasi dakwah, keumatan, dan transparansi organisasi.
                    </p>
                    
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <Link :href="route('public.profile')">
                            <Button label="Profil Organisasi" icon="pi pi-arrow-right" iconPos="right" 
                                    class="p-button-warning p-button-lg font-bold !rounded-full !px-8" />
                        </Link>
                        <Link :href="route('public.news.index')">
                            <Button label="Baca Berita" severity="secondary" outlined 
                                    class="!text-white !border-white hover:!bg-white hover:!text-emerald-900 !rounded-full !px-8" />
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white border-b relative z-10 -mt-8 mx-4 md:mx-auto max-w-6xl rounded-xl shadow-lg p-6 grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
            <div class="p-2">
                <div class="text-3xl font-bold text-emerald-600">7</div>
                <div class="text-sm text-gray-500 uppercase font-semibold">Ranting (PRM)</div>
            </div>
            <div class="p-2 border-l">
                <div class="text-3xl font-bold text-emerald-600">3+</div>
                <div class="text-sm text-gray-500 uppercase font-semibold">Amal Usaha</div>
            </div>
            <div class="p-2 border-l">
                <div class="text-3xl font-bold text-emerald-600">1934</div>
                <div class="text-sm text-gray-500 uppercase font-semibold">Tahun Berdiri</div>
            </div>
            <div class="p-2 border-l">
                <div class="text-3xl font-bold text-emerald-600">Active</div>
                <div class="text-sm text-gray-500 uppercase font-semibold">Status Cabang</div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
                
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 sticky top-24">
                        <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                            <i class="pi pi-user text-emerald-600"></i> Sambutan Ketua
                        </h3>
                        <div class="aspect-square bg-gray-200 rounded-xl mb-4 overflow-hidden relative">
                             <img :src="getKetuaImage()" 
                                class="object-cover w-full h-full hover:scale-105 transition duration-500" 
                                alt="Ketua PCM">
                        </div>
                        <blockquote class="text-gray-600 italic text-sm mb-4">
                            "{{ settings?.sambutan_ketua || 'Assalamualaikum Warahmatullahi Wabarakatuh. Selamat datang di website resmi kami. Semoga menjadi wasilah dakwah digital yang mencerahkan.' }}"
                        </blockquote>
                        <div class="font-bold text-gray-900">Ketua PCMU Muara Aman</div>
                        <div class="text-xs text-emerald-600 font-semibold">Periode 2022-2027</div>
                    </div>
                </div>

                <div class="lg:col-span-2">
                    <div class="flex justify-between items-center mb-8">
                        <div>
                            <h2 class="text-3xl font-bold text-gray-900">Kabar Persyarikatan</h2>
                            <p class="text-gray-500">Berita kegiatan dakwah dan sosial terkini.</p>
                        </div>
                        <Link :href="route('public.news.index')" class="text-emerald-600 font-semibold hover:underline">
                            Lihat Semua →
                        </Link>
                    </div>

                    <div class="grid gap-6">
                        <div v-for="post in latestPosts" :key="post.id" 
                             class="group bg-white rounded-xl shadow-sm hover:shadow-md border border-gray-100 overflow-hidden transition flex flex-col md:flex-row h-full">
                            
                            <div class="md:w-1/3 h-48 md:h-auto bg-gray-200 relative overflow-hidden">
                                <img v-if="post.thumbnail" :src="'/storage/' + post.thumbnail" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                                <div v-else class="w-full h-full flex items-center justify-center bg-emerald-50 text-emerald-200">
                                    <i class="pi pi-image text-4xl"></i>
                                </div>
                            </div>

                            <div class="p-6 flex flex-col justify-between md:w-2/3">
                                <div>
                                    <div class="flex items-center gap-2 text-xs text-gray-500 mb-2">
                                        <i class="pi pi-calendar"></i> {{ formatDate(post.published_at) }}
                                        <span>•</span>
                                        <span class="text-emerald-600 font-semibold">Berita</span>
                                    </div>
                                    <Link :href="route('public.news.show', post.slug)">
                                        <h3 class="text-xl font-bold text-gray-900 mb-2 group-hover:text-emerald-600 transition">
                                            {{ post.title }}
                                        </h3>
                                    </Link>
                                    <p class="text-gray-600 line-clamp-2 text-sm">
                                        {{ post.excerpt || 'Klik judul untuk membaca berita selengkapnya...' }}
                                    </p>
                                </div>
                                <div class="mt-4 pt-4 border-t border-gray-50 flex items-center justify-between">
                                    <div class="text-xs text-gray-500">
                                        Oleh: <span class="font-medium text-gray-700">{{ post.author?.name || 'Admin' }}</span>
                                    </div>
                                    <Link :href="route('public.news.show', post.slug)" class="text-sm font-semibold text-emerald-600 hover:text-emerald-800">
                                        Baca Selengkapnya
                                    </Link>
                                </div>
                            </div>
                        </div>

                        <div v-if="latestPosts.length === 0" class="text-center py-12 bg-gray-50 rounded-xl border border-dashed border-gray-300">
                            <i class="pi pi-inbox text-4xl text-gray-300 mb-3"></i>
                            <p class="text-gray-500">Belum ada berita yang diterbitkan.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="bg-gray-100 py-16">
            <div class="max-w-4xl mx-auto text-center px-4">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">Bergabunglah Menjadi Bagian Dari Kami</h2>
                <p class="text-gray-600 mb-8">
                    Dukung gerakan dakwah Muhammadiyah Muara Aman. Daftarkan diri Anda sebagai anggota atau simpatisan untuk akses layanan umat yang lebih luas.
                </p>
                <div class="flex justify-center gap-4">
                    <Button label="Kontak Kami" severity="help" outlined class="!rounded-full" />
                    <Button label="Daftar Anggota" class="!bg-emerald-600 !border-emerald-600 !rounded-full" />
                </div>
            </div>
        </div>
    </WebLayout>
</template>

<style scoped>
/* Animasi Sederhana */
.animate-fade-in-up {
    animation: fadeInUp 0.8s ease-out;
}
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>