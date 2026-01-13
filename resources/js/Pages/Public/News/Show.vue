<script setup>
import { Head, Link } from '@inertiajs/vue3';
import WebLayout from '@/Layouts/WebLayout.vue';
import Divider from 'primevue/divider';
import Card from 'primevue/card';

// Menerima data 1 Post utama & Related Posts
const props = defineProps({
    post: Object,
    relatedPosts: Array
});

// Format Tanggal
const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: 'numeric', month: 'long', year: 'numeric'
    });
};
</script>

<template>
    <Head :title="post.title" />

    <WebLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
                
                <div class="lg:col-span-2">
                    <div class="text-sm text-gray-500 mb-4 flex items-center gap-2">
                        <Link :href="route('public.home')" class="hover:text-emerald-600">Beranda</Link>
                        <i class="pi pi-chevron-right text-xs"></i>
                        <Link :href="route('public.news.index')" class="hover:text-emerald-600">Berita</Link>
                        <i class="pi pi-chevron-right text-xs"></i>
                        <span class="text-gray-800 font-medium truncate max-w-[200px]">{{ post.title }}</span>
                    </div>

                    <article class="bg-white rounded-2xl p-6 md:p-8 shadow-sm border border-gray-100">
                        <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-6 leading-tight">
                            {{ post.title }}
                        </h1>

                        <div class="flex flex-wrap items-center gap-4 text-sm text-gray-500 mb-8 pb-8 border-b border-gray-100">
                            <div class="flex items-center gap-2">
                                <i class="pi pi-calendar text-emerald-600"></i>
                                {{ formatDate(post.published_at) }}
                            </div>
                            <div class="flex items-center gap-2">
                                <i class="pi pi-user text-emerald-600"></i>
                                {{ post.author?.name || 'Admin PCM' }}
                            </div>
                            <div v-if="post.organization_unit" class="flex items-center gap-2">
                                <i class="pi pi-building text-emerald-600"></i>
                                {{ post.organization_unit.name }}
                            </div>
                        </div>

                        <div class="mb-8 rounded-xl overflow-hidden bg-gray-100 shadow-inner">
                            <img v-if="post.thumbnail" :src="'/storage/' + post.thumbnail" class="w-full h-auto object-cover">
                        </div>

                        <div class="prose prose-lg max-w-none text-gray-700 leading-relaxed text-justify">
                            <div v-html="post.content"></div>
                        </div>
                    </article>
                </div>

                <div class="lg:col-span-1">
                    <div class="sticky top-24 space-y-8">
                        
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                            <h3 class="text-lg font-bold text-gray-900 mb-4 border-l-4 border-emerald-500 pl-3">
                                Berita Lainnya
                            </h3>
                            
                            <div class="space-y-4">
                                <div v-for="related in relatedPosts" :key="related.id" class="flex gap-3 group">
                                    <div class="w-20 h-16 shrink-0 bg-gray-200 rounded-md overflow-hidden">
                                        <img v-if="related.thumbnail" :src="'/storage/' + related.thumbnail" class="w-full h-full object-cover">
                                        <div v-else class="w-full h-full bg-emerald-50 flex items-center justify-center">
                                            <i class="pi pi-image text-emerald-300"></i>
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <Link :href="route('public.news.show', related.slug)">
                                            <h4 class="text-sm font-semibold text-gray-800 group-hover:text-emerald-600 line-clamp-2 leading-snug mb-1">
                                                {{ related.title }}
                                            </h4>
                                        </Link>
                                        <span class="text-xs text-gray-400 block">
                                            {{ formatDate(related.published_at) }}
                                        </span>
                                    </div>
                                </div>

                                <div v-if="relatedPosts.length === 0" class="text-sm text-gray-400 italic">
                                    Tidak ada berita terkait.
                                </div>
                            </div>
                        </div>

                        <div class="bg-emerald-900 rounded-xl p-6 text-center text-white relative overflow-hidden">
                            <div class="absolute inset-0 opacity-10 bg-[url('/images/pattern.svg')]"></div>
                            <div class="relative z-10">
                                <h3 class="font-bold text-xl mb-2">LazisMu</h3>
                                <p class="text-sm text-emerald-100 mb-4">Salurkan Zakat, Infaq, dan Shodaqoh Anda melalui LazisMu Kantor Layanan Muara Aman.</p>
                                <button class="px-4 py-2 bg-yellow-400 text-emerald-900 font-bold rounded-lg text-sm hover:bg-yellow-300">
                                    Donasi Sekarang
                                </button>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </WebLayout>
</template>

<style>
.prose h2 { font-size: 1.5rem; font-weight: bold; margin-top: 2rem; margin-bottom: 1rem; color: #111827; }
.prose h3 { font-size: 1.25rem; font-weight: bold; margin-top: 1.5rem; margin-bottom: 0.75rem; color: #374151; }
.prose p { margin-bottom: 1.25rem; }
.prose ul { list-style-type: disc; padding-left: 1.5rem; margin-bottom: 1.25rem; }
.prose ol { list-style-type: decimal; padding-left: 1.5rem; margin-bottom: 1.25rem; }
.prose blockquote { border-left: 4px solid #10b981; padding-left: 1rem; font-style: italic; color: #4b5563; }
</style>