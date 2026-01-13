<script setup>
import { useForm, Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

// PrimeVue Components v4
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import Button from 'primevue/button';
import FileUpload from 'primevue/fileupload';
import Tabs from 'primevue/tabs';
import TabList from 'primevue/tablist';
import Tab from 'primevue/tab';
import TabPanels from 'primevue/tabpanels';
import TabPanel from 'primevue/tabpanel';
import ToggleSwitch from 'primevue/toggleswitch';
import Editor from 'primevue/editor';
import Divider from 'primevue/divider';
import Message from 'primevue/message';

const props = defineProps({
    settings: Object 
});

// Helper: Convert '1'/'0' string to boolean for ToggleSwitch
const toBool = (val) => val === '1' || val === 1 || val === true;

const form = useForm({
    _method: 'PATCH',
    
    // A. Identitas
    app_name: props.settings.app_name || '',
    app_logo: null,
    org_official_name: props.settings.org_official_name || '',
    org_sk_number: props.settings.org_sk_number || '',
    org_establishment_year: props.settings.org_establishment_year || '',
    org_address: props.settings.org_address || '',

    // B. Landing Page
    landing_banner_image: null,
    landing_title: props.settings.landing_title || '',
    landing_subtitle: props.settings.landing_subtitle || '',
    landing_show_clock: toBool(props.settings.landing_show_clock),

    // C. Sambutan Ketua
    chairman_name: props.settings.chairman_name || '',
    chairman_photo: null,
    chairman_speech: props.settings.chairman_speech || '',

    // D. Profil Organisasi
    org_history: props.settings.org_history || '',
    org_vision: props.settings.org_vision || '',
    org_mission: props.settings.org_mission || '',

    // E. Kontak & Footer
    contact_email: props.settings.contact_email || '',
    contact_phone: props.settings.contact_phone || '',
    social_facebook: props.settings.social_facebook || '',
    social_instagram: props.settings.social_instagram || '',
    social_youtube: props.settings.social_youtube || '',
    footer_text: props.settings.footer_text || '',

    // F. System
    system_maintenance_mode: toBool(props.settings.system_maintenance_mode),
    system_privacy_policy: props.settings.system_privacy_policy || '',
    system_help_content: props.settings.system_help_content || '',
});

const onFileSelect = (field, event) => {
    form[field] = event.files[0];
};

const submit = () => {
    form.post(route('settings.update'), {
        preserveScroll: true,
        forceFormData: true,
    });
};
</script>

<template>
    <Head title="Pengaturan Sistem" />

    <AppLayout>
        <div class="max-w-6xl mx-auto space-y-6 pb-20">
            
            <div class="flex items-center justify-between bg-white p-6 rounded-3xl border border-gray-100 shadow-sm top-4 z-50">
                <div>
                    <h1 class="text-2xl font-black text-gray-800">Pengaturan Sistem</h1>
                    <p class="text-gray-500 text-sm">Konfigurasi Web Profil dan Sistem Internal (SIM).</p>
                </div>
                <Button label="Simpan Semua" icon="pi pi-save" @click="submit" :loading="form.processing" class="!bg-gray-900 !border-gray-900 !rounded-xl font-bold shadow-lg" />
            </div>

            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden min-h-[600px]">
                <Tabs value="identity">
                    <TabList class="!bg-gray-50 !border-b !border-gray-100">
                        <Tab value="identity" class="!px-5 !py-4 font-bold text-gray-600"><i class="pi pi-id-card mr-2"></i> Identitas</Tab>
                        <Tab value="landing" class="!px-5 !py-4 font-bold text-gray-600"><i class="pi pi-desktop mr-2"></i> Landing Page</Tab>
                        <Tab value="chairman" class="!px-5 !py-4 font-bold text-gray-600"><i class="pi pi-user mr-2"></i> Ketua PCM</Tab>
                        <Tab value="profile" class="!px-5 !py-4 font-bold text-gray-600"><i class="pi pi-building mr-2"></i> Profil Org</Tab>
                        <Tab value="contact" class="!px-5 !py-4 font-bold text-gray-600"><i class="pi pi-phone mr-2"></i> Kontak</Tab>
                        <Tab value="system" class="!px-5 !py-4 font-bold text-red-600"><i class="pi pi-cog mr-2"></i> System</Tab>
                    </TabList>
                    
                    <TabPanels class="p-0">
                        
                        <TabPanel value="identity" class="p-8 space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <label class="font-bold text-sm">Nama Aplikasi (Header SIM)</label>
                                    <InputText v-model="form.app_name" class="w-full" />
                                </div>
                                <div class="space-y-2">
                                    <label class="font-bold text-sm">Logo Aplikasi</label>
                                    <div class="flex gap-4 items-center">
                                        <div v-if="settings.app_logo" class="w-12 h-12 bg-gray-100 border rounded flex items-center justify-center p-1">
                                            <img :src="'/storage/' + settings.app_logo" class="max-w-full max-h-full">
                                        </div>
                                        <FileUpload mode="basic" name="app_logo" accept="image/*" :maxFileSize="1000000" @select="(e) => onFileSelect('app_logo', e)" chooseLabel="Upload Logo" auto customUpload />
                                    </div>
                                </div>
                            </div>
                            <Divider />
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <label class="font-bold text-sm">Nama Resmi Organisasi</label>
                                    <InputText v-model="form.org_official_name" class="w-full" />
                                </div>
                                <div class="space-y-2">
                                    <label class="font-bold text-sm">Nomor SK Pendirian</label>
                                    <InputText v-model="form.org_sk_number" class="w-full" />
                                </div>
                                <div class="space-y-2">
                                    <label class="font-bold text-sm">Tahun Berdiri</label>
                                    <InputText v-model="form.org_establishment_year" class="w-full" />
                                </div>
                                <div class="space-y-2">
                                    <label class="font-bold text-sm">Alamat Kantor</label>
                                    <Textarea v-model="form.org_address" rows="3" class="w-full" />
                                </div>
                            </div>
                        </TabPanel>

                        <TabPanel value="landing" class="p-8 space-y-6">
                            <div class="bg-blue-50 border border-blue-100 p-4 rounded-xl mb-4">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-blue-800 text-sm">Jam Digital di Header</span>
                                    <ToggleSwitch v-model="form.landing_show_clock" />
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label class="font-bold text-sm">Banner Utama (Hero Image)</label>
                                <div class="relative w-full h-48 bg-gray-100 rounded-xl overflow-hidden group border">
                                    <img v-if="settings.landing_banner_image" :src="'/storage/' + settings.landing_banner_image" class="w-full h-full object-cover">
                                    <div v-else class="w-full h-full flex items-center justify-center text-gray-400">No Image</div>
                                    <div class="absolute inset-0 bg-black/50 hidden group-hover:flex items-center justify-center">
                                        <FileUpload mode="basic" name="landing_banner_image" accept="image/*" @select="(e) => onFileSelect('landing_banner_image', e)" chooseLabel="Ganti Banner" auto customUpload />
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <label class="font-bold text-sm">Tulisan Utama (Headline)</label>
                                    <InputText v-model="form.landing_title" class="w-full font-bold text-lg" />
                                </div>
                                <div class="space-y-2">
                                    <label class="font-bold text-sm">Sub Tulisan Utama</label>
                                    <InputText v-model="form.landing_subtitle" class="w-full" />
                                </div>
                            </div>
                        </TabPanel>

                        <TabPanel value="chairman" class="p-8 space-y-6">
                            <div class="flex gap-6 items-start">
                                <div class="space-y-2 text-center w-48 shrink-0">
                                    <label class="font-bold text-sm block">Foto Ketua</label>
                                    <div class="w-full aspect-[3/4] bg-gray-100 rounded-xl overflow-hidden border">
                                        <img v-if="settings.chairman_photo" :src="'/storage/' + settings.chairman_photo" class="w-full h-full object-cover">
                                        <div v-else class="w-full h-full flex items-center justify-center text-gray-400 text-xs">No Foto</div>
                                    </div>
                                    <FileUpload mode="basic" name="chairman_photo" accept="image/*" @select="(e) => onFileSelect('chairman_photo', e)" chooseLabel="Upload" auto customUpload class="w-full" />
                                </div>
                                <div class="flex-1 space-y-4">
                                    <div class="space-y-2">
                                        <label class="font-bold text-sm">Nama Ketua PCM</label>
                                        <InputText v-model="form.chairman_name" class="w-full" />
                                    </div>
                                    <div class="space-y-2">
                                        <label class="font-bold text-sm">Teks Sambutan</label>
                                        <Editor v-model="form.chairman_speech" editorStyle="height: 300px" />
                                    </div>
                                </div>
                            </div>
                        </TabPanel>

                        <TabPanel value="profile" class="p-8 space-y-8">
                            <div class="space-y-2">
                                <label class="font-bold text-sm text-emerald-700">Sejarah Perjalanan</label>
                                <Editor v-model="form.org_history" editorStyle="height: 200px" />
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <label class="font-bold text-sm text-blue-700">Visi</label>
                                    <Editor v-model="form.org_vision" editorStyle="height: 200px" />
                                </div>
                                <div class="space-y-2">
                                    <label class="font-bold text-sm text-orange-700">Misi</label>
                                    <Editor v-model="form.org_mission" editorStyle="height: 200px" />
                                </div>
                            </div>
                        </TabPanel>

                        <TabPanel value="contact" class="p-8 space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <label class="font-bold text-sm">Email Resmi</label>
                                    <InputText v-model="form.contact_email" class="w-full" />
                                </div>
                                <div class="space-y-2">
                                    <label class="font-bold text-sm">No. Telepon / WhatsApp</label>
                                    <InputText v-model="form.contact_phone" class="w-full" />
                                </div>
                            </div>
                            <Divider align="left"><span class="text-xs text-gray-400 font-bold uppercase">Sosial Media</span></Divider>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="flex items-center gap-2">
                                    <i class="pi pi-facebook text-blue-600 text-xl"></i>
                                    <InputText v-model="form.social_facebook" placeholder="Facebook URL" class="w-full" />
                                </div>
                                <div class="flex items-center gap-2">
                                    <i class="pi pi-instagram text-pink-500 text-xl"></i>
                                    <InputText v-model="form.social_instagram" placeholder="Instagram URL" class="w-full" />
                                </div>
                                <div class="flex items-center gap-2">
                                    <i class="pi pi-youtube text-red-600 text-xl"></i>
                                    <InputText v-model="form.social_youtube" placeholder="Youtube URL" class="w-full" />
                                </div>
                            </div>
                            <Divider />
                            <div class="space-y-2">
                                <label class="font-bold text-sm">Tulisan Footer (Motto/Copyright)</label>
                                <Textarea v-model="form.footer_text" rows="2" class="w-full" />
                            </div>
                        </TabPanel>

                        <TabPanel value="system" class="p-8 space-y-6">
                            <Message severity="warn" :closable="false">Pengaturan ini berdampak langsung pada operasional sistem.</Message>
                            
                            <div class="bg-red-50 border border-red-100 p-6 rounded-xl flex items-center justify-between">
                                <div>
                                    <h3 class="font-bold text-red-800">Mode Maintenance</h3>
                                    <p class="text-xs text-red-600">Jika aktif, hanya Super Admin yang bisa login. Halaman depan menampilkan pesan perbaikan.</p>
                                </div>
                                <ToggleSwitch v-model="form.system_maintenance_mode" />
                            </div>

                            <div class="space-y-2">
                                <label class="font-bold text-sm">Halaman Bantuan (Help)</label>
                                <Editor v-model="form.system_help_content" editorStyle="height: 200px" />
                            </div>

                            <div class="space-y-2">
                                <label class="font-bold text-sm">Kebijakan Privasi</label>
                                <Editor v-model="form.system_privacy_policy" editorStyle="height: 200px" />
                            </div>
                        </TabPanel>

                    </TabPanels>
                </Tabs>
            </div>
        </div>
    </AppLayout>
</template>