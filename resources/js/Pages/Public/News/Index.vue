<script setup>
import { Head, Link } from '@inertiajs/vue3';
import WebLayout from '@/Layouts/WebLayout.vue';
import Button from 'primevue/button';
import Tag from 'primevue/tag';

// Menerima data dari PostController (paginate)
const props = defineProps({
    posts: Object
});

// Helper Format Tanggal
const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: 'numeric', month: 'long', year: 'numeric'
    });
};
</script>

<template>
    <Head title="Berita & Artikel" />

    <WebLayout>
        <div class="bg-emerald-50 py-12 border-b border-emerald-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h1 class="text-3xl md:text-4xl font-bold text-emerald-900 mb-2">Kabar Persyarikatan</h1>
                <p class="text-gray-600">Informasi kegiatan, artikel dakwah, dan agenda terbaru PCM Muara Aman</p>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            
            <div v-if="posts.data.length > 0">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div v-for="post in posts.data" :key="post.id" 
                         class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-lg transition duration-300 flex flex-col h-full group">
                        
                        <div class="h-48 bg-gray-200 overflow-hidden relative">
                            <img v-if="post.thumbnail" :src="'/storage/' + post.thumbnail" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                            <div v-else class="w-full h-full flex items-center justify-center bg-emerald-50 text-emerald-300">
                                <i class="pi pi-image text-5xl"></i>
                            </div>
                            
                            <div class="absolute top-4 left-4 bg-white/90 backdrop-blur text-emerald-800 text-xs font-bold px-3 py-1 rounded-full shadow-sm">
                                {{ formatDate(post.published_at) }}
                            </div>
                        </div>

                        <div class="p-6 flex flex-col flex-grow">
                            <div class="flex items-center gap-2 mb-3">
                                <Tag value="Berita" severity="success" class="!text-[10px]" />
                                <span class="text-xs text-gray-500 flex items-center gap-1">
                                    <i class="pi pi-user"></i> {{ post.author?.name || 'Admin' }}
                                </span>
                            </div>

                            <Link :href="route('public.news.show', post.slug)" class="block">
                                <h3 class="text-xl font-bold text-gray-900 mb-3 group-hover:text-emerald-600 transition line-clamp-2">
                                    {{ post.title }}
                                </h3>
                            </Link>
                            
                            <p class="text-gray-600 text-sm line-clamp-3 mb-6 flex-grow">
                                {{ post.excerpt || 'Klik tombol di bawah untuk membaca artikel selengkapnya...' }}
                            </p>

                            <div class="mt-auto">
                                <Link :href="route('public.news.show', post.slug)">
                                    <Button label="Baca Selengkapnya" icon="pi pi-arrow-right" iconPos="right" size="small" outlined class="w-full !border-emerald-600 !text-emerald-600 hover:!bg-emerald-50" />
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-12 flex justify-center gap-2">
                    <Link v-for="(link, key) in posts.links" :key="key" 
                          :href="link.url || '#'"
                          v-html="link.label"
                          class="px-4 py-2 text-sm rounded-lg border transition"
                          :class="{ 
                              'bg-emerald-600 text-white border-emerald-600': link.active,
                              'bg-white text-gray-700 hover:bg-gray-50 border-gray-300': !link.active,
                              'opacity-50 cursor-not-allowed': !link.url 
                          }" />
                </div>
            </div>

            <div v-else class="text-center py-20 bg-gray-50 rounded-2xl border border-dashed border-gray-300">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-white rounded-full shadow-sm mb-4">
                    <i class="pi pi-inbox text-4xl text-gray-400"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-700">Belum Ada Berita</h3>
                <p class="text-gray-500 mt-2">Saat ini belum ada artikel yang diterbitkan oleh admin.</p>
                <div class="mt-6">
                    <Link :href="route('public.home')">
                        <Button label="Kembali ke Beranda" severity="secondary" text />
                    </Link>
                </div>
            </div>

        </div>
    </WebLayout>
</template>