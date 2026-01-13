<script setup>
import { computed, ref, onMounted } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

// PrimeVue v4 Components
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import Select from 'primevue/select';
import Button from 'primevue/button';
import FileUpload from 'primevue/fileupload';
import Editor from 'primevue/editor';
import Tabs from 'primevue/tabs';
import TabList from 'primevue/tablist';
import Tab from 'primevue/tab';
import TabPanels from 'primevue/tabpanels';
import TabPanel from 'primevue/tabpanel';
import DatePicker from 'primevue/datepicker';
import Card from 'primevue/card';
import Divider from 'primevue/divider';
import Tag from 'primevue/tag';

const props = defineProps({ post: Object, categories: Array });
const isEdit = computed(() => !!props.post);

const form = useForm({
    _method: isEdit.value ? 'PUT' : 'POST',
    title: props.post?.title || '',
    slug: props.post?.slug || '',
    excerpt: props.post?.excerpt || '',
    content: props.post?.content || '',
    status: props.post?.status || 'PUBLISHED',
    category_id: props.post?.category_id || null,
    
    // Media & Files
    thumbnail: null,
    attachments: [],
    galleries: [],

    // Event Metadata
    event_location: props.post?.event_location || '',
    event_date_start: props.post?.event_date_start ? new Date(props.post.event_date_start) : null,
    event_date_end: props.post?.event_date_end ? new Date(props.post.event_date_end) : null,
    
    meta_keywords: props.post?.meta_keywords || '',
});

const statusOptions = [
    { label: 'Terbit (Published)', value: 'PUBLISHED', icon: 'pi pi-check-circle', class: 'text-emerald-600' },
    { label: 'Konsep (Draft)', value: 'DRAFT', icon: 'pi pi-pencil', class: 'text-gray-600' },
    { label: 'Arsip (Hidden)', value: 'ARCHIVED', icon: 'pi pi-lock', class: 'text-orange-600' }
];

// Handlers
const onThumbnailSelect = (e) => { form.thumbnail = e.files[0]; };
const onAttachmentSelect = (e) => { form.attachments = e.files; };
const onGallerySelect = (e) => { form.galleries = e.files; };

const deleteItem = (routeKey, id) => {
    if(confirm('Hapus item ini secara permanen?')) {
        router.delete(route(routeKey, id), { preserveScroll: true });
    }
};

const submit = () => {
    const routeName = isEdit.value ? route('posts.update', props.post.id) : route('posts.store');
    form.post(routeName, { forceFormData: true });
};
</script>

<template>
    <Head :title="isEdit ? 'Edit Konten' : 'Konten Baru'" />

    <AppLayout>
        <div class="max-w-7xl mx-auto space-y-6 pb-20">
            
            <div class="flex items-center justify-between bg-white/80 backdrop-blur-md p-4 rounded-2xl border border-gray-200 shadow-sm top-4 z-50">
                <div class="flex items-center gap-3">
                    <Link :href="route('posts.index')">
                        <Button icon="pi pi-arrow-left" text rounded severity="secondary" />
                    </Link>
                    <div>
                        <h1 class="text-xl font-black text-gray-800 tracking-tight">{{ isEdit ? 'Edit Konten' : 'Buat Konten Baru' }}</h1>
                        <p class="text-xs text-gray-500 hidden md:block">Isi formulir di bawah untuk mempublikasikan informasi.</p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <Button label="Simpan & Publish" @click="submit" icon="pi pi-save" 
                            class="!bg-gray-900 !border-gray-900 !rounded-xl font-bold shadow-lg shadow-gray-400/50" 
                            :loading="form.processing" />
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <div class="lg:col-span-2 space-y-6">
                    
                    <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm transition-all focus-within:shadow-md focus-within:border-emerald-200">
                        <label class="block text-xs font-bold text-gray-400 uppercase mb-2 tracking-widest">Judul Utama</label>
                        <InputText v-model="form.title" placeholder="Tulis Judul Berita / Kegiatan..." 
                                   class="w-full !text-2xl !font-black !border-0 !border-b !border-gray-200 !rounded-none focus:!border-emerald-500 !px-0 shadow-none placeholder:text-gray-300" />
                        <small class="text-red-500 mt-1 block">{{ form.errors.title }}</small>
                    </div>

                    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden min-h-[600px]">
                        <Tabs value="content">
                            <TabList class="!bg-gray-50 !border-b !border-gray-100">
                                <Tab value="content" class="!px-6 !py-4 font-bold text-gray-600"><i class="pi pi-file-edit mr-2"></i> Konten Berita</Tab>
                                <Tab value="event" class="!px-6 !py-4 font-bold text-gray-600"><i class="pi pi-calendar mr-2"></i> Detail Acara</Tab>
                                <Tab value="gallery" class="!px-6 !py-4 font-bold text-gray-600"><i class="pi pi-images mr-2"></i> Galeri Foto</Tab>
                                <Tab value="files" class="!px-6 !py-4 font-bold text-gray-600"><i class="pi pi-folder mr-2"></i> Lampiran</Tab>
                            </TabList>
                            
                            <TabPanels class="p-0">
                                <TabPanel value="content" class="p-0">
                                    <Editor v-model="form.content" editorStyle="height: 500px; font-size: 16px; border: none;" placeholder="Mulai menulis cerita anda..." />
                                    <small class="text-red-500 px-6 block">{{ form.errors.content }}</small>
                                </TabPanel>

                                <TabPanel value="event">
                                    <div class="p-8">
                                        <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 mb-6 flex items-start gap-3">
                                            <i class="pi pi-info-circle text-blue-600 text-xl mt-1"></i>
                                            <div>
                                                <h4 class="font-bold text-blue-800 text-sm">Mode Agenda</h4>
                                                <p class="text-xs text-blue-600">Isi bagian ini hanya jika postingan merupakan agenda kegiatan yang memiliki waktu dan tempat spesifik.</p>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <div class="space-y-1">
                                                <label class="font-bold text-sm text-gray-700">Waktu Mulai</label>
                                                <DatePicker v-model="form.event_date_start" showTime hourFormat="24" class="w-full !rounded-xl" placeholder="Pilih tanggal..." />
                                            </div>
                                            <div class="space-y-1">
                                                <label class="font-bold text-sm text-gray-700">Waktu Selesai</label>
                                                <DatePicker v-model="form.event_date_end" showTime hourFormat="24" class="w-full !rounded-xl" placeholder="Pilih tanggal..." />
                                            </div>
                                            <div class="md:col-span-2 space-y-1">
                                                <label class="font-bold text-sm text-gray-700">Lokasi Kegiatan</label>
                                                <span class="p-input-icon-left w-full">
                                                    <InputText v-model="form.event_location" placeholder="Contoh: Aula Masjid Agung..." class="w-full !rounded-xl" />
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </TabPanel>

                                <TabPanel value="gallery">
                                    <div class="p-8 space-y-6">
                                        <div class="border-2 border-dashed border-gray-300 rounded-2xl p-8 text-center bg-gray-50 hover:bg-gray-100 transition-colors">
                                            <div class="mb-4">
                                                <i class="pi pi-images text-4xl text-gray-400"></i>
                                            </div>
                                            <h3 class="font-bold text-gray-700 mb-1">Upload Dokumentasi</h3>
                                            <p class="text-xs text-gray-500 mb-4">Support JPG, PNG. Max 5MB per foto.</p>
                                            <FileUpload mode="basic" name="galleries[]" :multiple="true" accept="image/*" :maxFileSize="5000000"
                                                        @select="onGallerySelect" chooseLabel="Pilih Foto" auto customUpload class="!rounded-lg" />
                                        </div>

                                        <Divider align="center"><span class="text-xs text-gray-400 font-bold uppercase">Foto Tersimpan</span></Divider>

                                        <div v-if="post?.galleries?.length" class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                            <div v-for="img in post.galleries" :key="img.id" class="relative group rounded-xl overflow-hidden shadow-sm aspect-square bg-gray-100">
                                                <img :src="'/storage/' + img.image_path" class="w-full h-full object-cover transition duration-500 group-hover:scale-110">
                                                <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                                                    <button @click="deleteItem('posts.gallery.destroy', img.id)" type="button" class="bg-red-500 text-white p-2 rounded-full hover:bg-red-600 transition shadow-lg">
                                                        <i class="pi pi-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <div v-else class="text-center text-gray-400 text-sm italic py-4">Belum ada foto galeri.</div>
                                    </div>
                                </TabPanel>

                                <TabPanel value="files">
                                    <div class="p-8 space-y-6">
                                        <div class="flex justify-between items-center mb-4">
                                            <h3 class="font-bold text-gray-700">File Dokumen</h3>
                                            <FileUpload mode="basic" name="attachments[]" :multiple="true" :maxFileSize="10000000"
                                                        @select="onAttachmentSelect" chooseLabel="+ Tambah File" auto customUpload class="!rounded-lg" />
                                        </div>

                                        <div v-if="post?.attachments?.length" class="space-y-3">
                                            <div v-for="file in post.attachments" :key="file.id" class="flex items-center justify-between bg-white p-4 border border-gray-200 rounded-xl shadow-sm hover:border-emerald-300 transition">
                                                <div class="flex items-center gap-4">
                                                    <div class="w-10 h-10 bg-red-50 text-red-500 rounded-lg flex items-center justify-center">
                                                        <i class="pi pi-file-pdf text-xl" v-if="file.file_type?.includes('pdf')"></i>
                                                        <i class="pi pi-file-word text-xl" v-else-if="file.file_type?.includes('doc')"></i>
                                                        <i class="pi pi-file text-xl" v-else></i>
                                                    </div>
                                                    <div>
                                                        <div class="font-bold text-sm text-gray-800">{{ file.file_name }}</div>
                                                        <div class="text-xs text-gray-400">{{ (file.file_size / 1024).toFixed(1) }} KB • {{ file.download_count }}x Unduh</div>
                                                    </div>
                                                </div>
                                                <Button icon="pi pi-trash" text rounded severity="danger" @click="deleteItem('posts.attachment.destroy', file.id)" />
                                            </div>
                                        </div>
                                        <div v-else class="text-center py-10 bg-gray-50 rounded-xl border border-dashed border-gray-300">
                                            <i class="pi pi-folder-open text-3xl text-gray-400 mb-2"></i>
                                            <p class="text-sm text-gray-500">Belum ada lampiran dokumen (SK, Undangan, dll).</p>
                                        </div>
                                    </div>
                                </TabPanel>
                            </TabPanels>
                        </Tabs>
                    </div>
                </div>

                <div class="space-y-6">
                    
                    <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm space-y-5">
                        <h3 class="font-bold text-gray-800 text-sm tracking-widest uppercase border-b border-gray-100 pb-3">Publikasi</h3>
                        
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-gray-500">Status</label>
                            <Select v-model="form.status" :options="statusOptions" optionLabel="label" optionValue="value" class="w-full !rounded-xl">
                                <template #option="slotProps">
                                    <div class="flex items-center gap-2">
                                        <i :class="slotProps.option.icon + ' ' + slotProps.option.class"></i>
                                        <span>{{ slotProps.option.label }}</span>
                                    </div>
                                </template>
                            </Select>
                        </div>

                        <div class="space-y-2">
                            <label class="text-xs font-bold text-gray-500">Kategori</label>
                            <Select v-model="form.category_id" :options="categories" optionLabel="name" optionValue="id" placeholder="Pilih Kategori" class="w-full !rounded-xl" filter />
                            <small class="text-red-500">{{ form.errors.category_id }}</small>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm space-y-4">
                        <h3 class="font-bold text-gray-800 text-sm tracking-widest uppercase border-b border-gray-100 pb-3">Cover Utama</h3>
                        
                        <div class="relative w-full aspect-[4/3] bg-gray-100 rounded-2xl overflow-hidden border border-gray-200 group">
                            <img v-if="post?.thumbnail" :src="'/storage/' + post.thumbnail" class="w-full h-full object-cover">
                            <div v-else class="w-full h-full flex flex-col items-center justify-center text-gray-400">
                                <i class="pi pi-image text-4xl mb-2"></i>
                                <span class="text-xs">Preview Cover</span>
                            </div>
                            
                            <div class="absolute inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                <FileUpload mode="basic" name="thumbnail" accept="image/*" :maxFileSize="2000000" 
                                            @select="onThumbnailSelect" chooseLabel="Ganti Foto" auto customUpload 
                                            class="!bg-white !text-black !border-none !px-4 !py-2 !rounded-full !text-xs !font-bold hover:scale-105 transition-transform" />
                            </div>
                        </div>
                        <div v-if="!post?.thumbnail" class="text-center">
                             <FileUpload mode="basic" name="thumbnail" accept="image/*" :maxFileSize="2000000" 
                                            @select="onThumbnailSelect" chooseLabel="Upload Cover" class="w-full !rounded-xl" auto customUpload />
                        </div>
                        <small class="text-red-500 block text-center">{{ form.errors.thumbnail }}</small>
                    </div>

                    <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm space-y-4">
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-gray-500">Ringkasan (Excerpt)</label>
                            <Textarea v-model="form.excerpt" rows="3" class="w-full !text-sm !rounded-xl !bg-gray-50 border-0 focus:ring-1 focus:ring-emerald-500" placeholder="Muncul di kartu depan..." />
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-gray-500">SEO Keywords</label>
                            <InputText v-model="form.meta_keywords" class="w-full !text-sm !rounded-xl !bg-gray-50 border-0" placeholder="muhammadiyah, pengajian, dll" />
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style>
/* Clean Editor Style */
.p-editor-toolbar { border: none !important; border-bottom: 1px solid #f3f4f6 !important; background: #ffffff !important; }
.p-editor-content { border: none !important; font-family: 'Georgia', serif; color: #374151; }
</style>