<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, Head } from '@inertiajs/vue3';

defineProps({ archives: Array });
</script>

<template>
    <Head title="E-Arsip" />
    <AppLayout title="E-Arsip Digital">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">E-Arsip & Persuratan</h1>
                <p class="text-gray-500 text-sm">Penyimpanan digital Surat Masuk, Keluar, dan SK.</p>
            </div>
            <Link :href="route('archives.create')" class="bg-emerald-600 text-white px-4 py-2 rounded-lg hover:bg-emerald-700 transition">
                + Arsip Baru
            </Link>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full text-left text-sm text-gray-600">
                <thead class="bg-gray-50 uppercase text-xs font-bold text-gray-500">
                    <tr>
                        <th class="px-6 py-3">Tanggal</th>
                        <th class="px-6 py-3">No. Surat / SK</th>
                        <th class="px-6 py-3">Perihal</th>
                        <th class="px-6 py-3">Kategori</th>
                        <th class="px-6 py-3">File</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="item in archives" :key="item.id" class="hover:bg-gray-50 transition">
                        <td class="px-6 py-3">{{ item.document_date }}</td>
                        <td class="px-6 py-3 font-medium text-gray-800">{{ item.reference_number }}</td>
                        <td class="px-6 py-3">{{ item.title }}</td>
                        <td class="px-6 py-3">
                            <span class="px-2 py-1 rounded text-xs font-bold"
                                :class="{
                                    'bg-blue-100 text-blue-700': item.category === 'SURAT_MASUK',
                                    'bg-orange-100 text-orange-700': item.category === 'SURAT_KELUAR',
                                    'bg-purple-100 text-purple-700': item.category === 'SK',
                                }">
                                {{ item.category.replace('_', ' ') }}
                            </span>
                        </td>
                        <td class="px-6 py-3">
                            <a v-if="item.file_path" :href="`/storage/${item.file_path}`" target="_blank" class="text-emerald-600 hover:underline">
                                <i class="pi pi-file-pdf"></i> Lihat
                            </a>
                        </td>
                    </tr>
                    <tr v-if="archives.length === 0">
                        <td colspan="5" class="px-6 py-8 text-center text-gray-400">Belum ada arsip dokumen.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AppLayout>
</template>