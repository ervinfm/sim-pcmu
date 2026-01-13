<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { FilterMatchMode } from '@primevue/core/api';

// PrimeVue v4 Components
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import Tag from 'primevue/tag';
import SelectButton from 'primevue/selectbutton';
import ConfirmPopup from 'primevue/confirmpopup';
import Badge from 'primevue/badge';
import Avatar from 'primevue/avatar';
import Select from 'primevue/select';
import { useConfirm } from "primevue/useconfirm";

const props = defineProps({ posts: Array });

const filters = ref({ global: { value: null, matchMode: FilterMatchMode.CONTAINS } });
const viewMode = ref('grid'); 
const viewOptions = ref([{ icon: 'pi pi-th-large', value: 'grid' }, { icon: 'pi pi-list', value: 'list' }]);
const selectedCategory = ref(null);

const confirm = useConfirm();

// --- HELPER FUNCTIONS ---

const handleDelete = (event, id) => {
    confirm.require({
        target: event.currentTarget,
        message: 'Hapus konten ini beserta seluruh lampirannya?',
        icon: 'pi pi-trash',
        acceptClass: 'p-button-danger',
        accept: () => router.delete(route('posts.destroy', id))
    });
};

const getStatusSeverity = (status) => {
    return { 'PUBLISHED': 'success', 'DRAFT': 'secondary', 'ARCHIVED': 'warn' }[status] || 'info';
};

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
};

// Ambil list kategori unik dari data posts untuk filter client-side
const categories = computed(() => {
    const cats = props.posts.map(p => p.category?.name).filter(Boolean);
    return [...new Set(cats)].map(c => ({ label: c, value: c }));
});

// Filter data berdasarkan kategori yang dipilih
const filteredPosts = computed(() => {
    let data = props.posts;
    if (selectedCategory.value) {
        data = data.filter(p => p.category?.name === selectedCategory.value);
    }
    return data;
});
</script>

<template>
    <Head title="Newsroom & Agenda" />

    <AppLayout>
        <div class="space-y-8 max-w-7xl mx-auto pb-10">
            
            <div class="relative bg-gradient-to-r from-gray-900 to-gray-800 rounded-3xl p-8 overflow-hidden shadow-2xl">
                <div class="absolute top-0 right-0 -mt-10 -mr-10 w-64 h-64 bg-emerald-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob"></div>
                <div class="absolute bottom-0 left-0 -mb-10 -ml-10 w-64 h-64 bg-blue-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob animation-delay-2000"></div>
                
                <div class="relative z-10 flex flex-col md:flex-row justify-between items-center gap-6 text-white">
                    <div>
                        <h1 class="text-3xl font-black tracking-tight mb-2">Newsroom & Kegiatan</h1>
                        <p class="text-gray-400 max-w-xl">Pusat pengelolaan informasi, berita, agenda kegiatan, dan arsip digital organisasi secara terpusat.</p>
                    </div>
                    <Link :href="route('posts.create')">
                        <Button label="Buat Postingan Baru" icon="pi pi-plus" class="!bg-emerald-500 !border-emerald-500 hover:!bg-emerald-400 !text-white !font-bold !rounded-xl !px-6 !py-3 shadow-lg shadow-emerald-900/50 transition-all hover:scale-105" />
                    </Link>
                </div>
            </div>

            <div class="flex flex-col md:flex-row justify-between items-center gap-4 bg-white/50 backdrop-blur-md p-4 rounded-2xl border border-gray-200/60 sticky top-4 z-40 shadow-sm">
                
                <div class="flex items-center gap-3 w-full md:w-auto">
                    <SelectButton v-model="viewMode" :options="viewOptions" optionValue="value" :allowEmpty="false">
                        <template #option="{ option }">
                            <i :class="option.icon"></i>
                        </template>
                    </SelectButton>
                    
                    <Select v-model="selectedCategory" :options="categories" optionLabel="label" optionValue="value" 
                            showClear placeholder="Semua Kategori" class="w-full md:w-48 !rounded-xl" />
                </div>

                <div class="w-full md:w-auto">
                    <IconField iconPosition="left" class="w-full md:w-80">
                        <InputIcon class="pi pi-search text-gray-400" />
                        <InputText v-model="filters['global'].value" placeholder="Cari judul, penulis, atau unit..." class="w-full !rounded-xl !bg-white focus:!ring-emerald-500" />
                    </IconField>
                </div>
            </div>

            <div v-if="viewMode === 'grid'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 animate-fade-in">
                <div v-for="post in filteredPosts" :key="post.id" class="group bg-white rounded-3xl border border-gray-100 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col overflow-hidden h-full">
                    
                    <div class="h-52 relative overflow-hidden bg-gray-100">
                        <img v-if="post.thumbnail" :src="'/storage/post/' + post.thumbnail" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                        <div v-else class="w-full h-full flex items-center justify-center text-gray-300 bg-gray-50">
                            <i class="pi pi-image text-5xl"></i>
                        </div>
                        
                        <div class="absolute top-4 right-4 flex gap-2">
                            <Tag :value="post.status" :severity="getStatusSeverity(post.status)" class="!shadow-lg !font-bold" rounded />
                        </div>
                        <div class="absolute top-4 left-4">
                            <Tag :value="post.category?.name || 'Umum'" severity="contrast" class="!bg-white/90 !text-gray-900 !backdrop-blur-sm !shadow-lg !font-bold" rounded />
                        </div>

                        <div v-if="post.event_date_start" class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/80 to-transparent p-4 pt-10 text-white">
                            <div class="flex items-center gap-2 font-bold text-sm">
                                <i class="pi pi-calendar text-emerald-400"></i>
                                <span>{{ formatDate(post.event_date_start) }}</span>
                                <span v-if="post.event_location" class="font-normal text-gray-300 truncate max-w-[150px]">
                                    • {{ post.event_location }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 flex-1 flex flex-col">
                        
                        <div class="flex items-center gap-2 mb-3 text-xs text-gray-500 font-medium uppercase tracking-wide">
                            <i class="pi pi-building text-emerald-600"></i>
                            <span>{{ post.organization_unit?.name || 'PCMU Muara Aman' }}</span>
                        </div>

                        <h3 class="font-bold text-gray-800 text-xl mb-3 line-clamp-2 leading-snug group-hover:text-emerald-600 transition-colors">
                            {{ post.title }}
                        </h3>
                        <p class="text-sm text-gray-500 line-clamp-3 mb-6 leading-relaxed flex-1">
                            {{ post.excerpt || 'Tidak ada ringkasan.' }}
                        </p>
                        
                        <div class="pt-4 border-t border-gray-100 flex items-center justify-between mt-auto">
                            <div class="flex items-center gap-2">
                                <Avatar :label="post.author?.name[0]" shape="circle" size="small" class="!bg-gray-200 !text-gray-600 !text-xs" />
                                <span class="text-xs text-gray-500 font-medium truncate max-w-[100px]">{{ post.author?.name }}</span>
                            </div>
                            
                            <div class="flex gap-2 items-center">
                                <span v-if="post.attachments_count > 0" class="text-xs text-gray-400 flex items-center gap-1" title="Lampiran">
                                    <i class="pi pi-paperclip"></i> {{ post.attachments_count }}
                                </span>
                                <span v-if="post.galleries_count > 0" class="text-xs text-gray-400 flex items-center gap-1" title="Galeri">
                                    <i class="pi pi-images"></i> {{ post.galleries_count }}
                                </span>

                                <Link :href="route('posts.edit', post.id)">
                                    <Button icon="pi pi-pencil" rounded text severity="secondary" size="small" v-tooltip="'Edit'" />
                                </Link>
                                <Button icon="pi pi-trash" rounded text severity="danger" size="small" @click="handleDelete($event, post.id)" v-tooltip="'Hapus'" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden animate-fade-in">
                <DataTable v-model:filters="filters" :value="filteredPosts" paginator :rows="10" stripedRows>
                    <Column header="Info Konten" style="width: 45%">
                        <template #body="{ data }">
                            <div class="flex gap-4 items-start py-2">
                                <div class="w-20 h-14 bg-gray-100 rounded-lg overflow-hidden border border-gray-200 shrink-0">
                                    <img v-if="data.thumbnail" :src="'/storage/' + data.thumbnail" class="w-full h-full object-cover">
                                </div>
                                <div>
                                    <div class="font-bold text-gray-800 text-base mb-1">{{ data.title }}</div>
                                    <div class="flex items-center gap-2 text-xs text-gray-500">
                                        <Badge :value="data.category?.name || 'Umum'" severity="secondary" class="!font-normal" />
                                        <span>• {{ formatDate(data.updated_at) }}</span>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </Column>

                    <Column header="Publisher" style="width: 20%">
                        <template #body="{ data }">
                            <div class="flex flex-col">
                                <span class="text-sm font-semibold text-gray-700">{{ data.organization_unit?.name || 'Global' }}</span>
                                <span class="text-xs text-gray-400">{{ data.author?.name }}</span>
                            </div>
                        </template>
                    </Column>

                    <Column header="Agenda" style="width: 15%">
                        <template #body="{ data }">
                            <div v-if="data.event_date_start" class="text-xs">
                                <div class="font-bold text-emerald-600"><i class="pi pi-calendar"></i> {{ formatDate(data.event_date_start) }}</div>
                                <div class="text-gray-500 truncate max-w-[120px]">{{ data.event_location }}</div>
                            </div>
                            <span v-else class="text-gray-300">-</span>
                        </template>
                    </Column>

                    <Column field="status" header="Status" style="width: 10%">
                        <template #body="{ data }">
                            <Tag :value="data.status" :severity="getStatusSeverity(data.status)" class="!text-[10px] !px-2" rounded />
                        </template>
                    </Column>

                    <Column header="Aksi" style="width: 10%; text-align: right">
                        <template #body="{ data }">
                            <div class="flex justify-end gap-1">
                                <Link :href="route('posts.edit', data.id)">
                                    <Button icon="pi pi-pencil" text rounded severity="info" size="small" />
                                </Link>
                                <Button icon="pi pi-trash" text rounded severity="danger" size="small" @click="handleDelete($event, data.id)" />
                            </div>
                        </template>
                    </Column>
                </DataTable>
            </div>

        </div>
        <ConfirmPopup />
    </AppLayout>
</template>

<style>
.animate-fade-in { animation: fadeIn 0.4s ease-out; }
.animate-blob { animation: blob 7s infinite; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
@keyframes blob { 0% { transform: translate(0px, 0px) scale(1); } 33% { transform: translate(30px, -50px) scale(1.1); } 66% { transform: translate(-20px, 20px) scale(0.9); } 100% { transform: translate(0px, 0px) scale(1); } }
</style>