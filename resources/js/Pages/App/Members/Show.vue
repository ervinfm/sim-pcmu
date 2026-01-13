<script setup>
import { ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useConfirm } from "primevue/useconfirm";
import { useToast } from "primevue/usetoast";

// PrimeVue Components
import Button from 'primevue/button';
import Card from 'primevue/card';
import Tag from 'primevue/tag';
import Avatar from 'primevue/avatar';
import TabView from 'primevue/tabview';
import TabPanel from 'primevue/tabpanel';
import ConfirmPopup from 'primevue/confirmpopup';
import Chip from 'primevue/chip';
import Divider from 'primevue/divider';

import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Dropdown from 'primevue/dropdown';

const props = defineProps({
    member: Object,
});

const confirm = useConfirm();
const toast = useToast();

// Format Tanggal Indo
const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
};

// Helper Warna Status
const getStatusSeverity = (status) => {
    switch (status) {
        case 'ACTIVE': return 'success';
        case 'INACTIVE': return 'secondary';
        case 'MOVED': return 'warning';
        case 'DECEASED': return 'danger';
        default: return 'info';
    }
};

// Generate Akun Action
const generateAccount = (event) => {
    confirm.require({
        target: event.currentTarget,
        message: 'Buatkan akun login default? Password: "password"',
        icon: 'pi pi-key',
        acceptClass: 'p-button-success',
        accept: () => {
            router.post(route('members.generate_account', props.member.id), {}, {
                onSuccess: () => toast.add({ severity: 'success', summary: 'Sukses', detail: 'Akun login berhasil dibuat.', life: 3000 })
            });
        }
    });
};

// --- LOGIC MANAJEMEN RIWAYAT ---
const historyDialog = ref(false);
const historyType = ref(''); // 'ortom' atau 'training'
const historyForm = useForm({
    type: '',
    action: 'add',
    value: ''
});

// Opsi untuk Dropdown Ortom
const ortomOptions = [
    'Aisyiyah', 'Pemuda Muhammadiyah', 'Nasyiatul Aisyiyah', 
    'IPM (Ikatan Pelajar Muhammadiyah)', 'IMM (Ikatan Mahasiswa Muhammadiyah)', 
    'Tapak Suci Putera Muhammadiyah', 'Hizbul Wathon'
];

// Opsi untuk Dropdown Training (Bisa di-custom text juga)
const trainingOptions = [
    'Baitul Arqam Dasar', 'Baitul Arqam Madya', 'Baitul Arqam Purna',
    'Darul Arqam Dasar', 'Darul Arqam Madya', 'Darul Arqam Purna',
    'Latihan Instruktur', 'Diklat Khusus'
];

const openHistoryDialog = (type) => {
    historyType.value = type;
    historyForm.type = type;
    historyForm.value = '';
    historyDialog.value = true;
};

const submitHistory = () => {
    historyForm.post(route('members.update_history', props.member.id), {
        onSuccess: () => {
            historyDialog.value = false;
            toast.add({ severity: 'success', summary: 'Tersimpan', detail: 'Data berhasil ditambahkan.', life: 3000 });
        }
    });
};

const removeHistory = (type, value) => {
    confirm.require({
        message: `Hapus "${value}" dari riwayat?`,
        header: 'Konfirmasi Hapus',
        icon: 'pi pi-trash',
        acceptClass: 'p-button-danger',
        accept: () => {
            historyForm.type = type;
            historyForm.action = 'remove';
            historyForm.value = value;
            historyForm.post(route('members.update_history', props.member.id), {
                onSuccess: () => toast.add({ severity: 'info', summary: 'Dihapus', detail: 'Data dihapus dari riwayat.', life: 3000 })
            });
        }
    });
};
</script>

<template>
    <Head :title="member.full_name" />

    <AppLayout>
        <div class="max-w-6xl mx-auto space-y-6">
            
            <div class="flex items-center justify-between">
                <Link :href="route('members.index')" class="text-gray-500 hover:text-emerald-600 flex items-center gap-2 transition">
                    <i class="pi pi-arrow-left"></i> Kembali ke Daftar
                </Link>
                <div class="flex gap-2">
                    <Link :href="route('members.edit', member.id)">
                        <Button label="Edit Data" icon="pi pi-pencil" size="small" outlined severity="info" />
                    </Link>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <div class="space-y-6">
                    <Card class="border border-gray-100 shadow-sm text-center relative overflow-hidden">
                        <template #content>
                            <div class="absolute top-0 left-0 right-0 h-24 bg-gradient-to-r from-emerald-500 to-teal-500"></div>
                            
                            <div class="relative z-10 pt-8">
                                <div class="w-32 h-32 mx-auto rounded-full border-4 border-white shadow-md overflow-hidden bg-white mb-4">
                                    <img v-if="member.photo_path" :src="'/storage/' + member.photo_path" class="w-full h-full object-cover">
                                    <div v-else class="w-full h-full bg-emerald-50 flex items-center justify-center text-emerald-200">
                                        <i class="pi pi-user text-6xl"></i>
                                    </div>
                                </div>

                                <h2 class="text-xl font-bold text-gray-800">{{ member.full_name }}</h2>
                                <p class="text-sm text-gray-500 mb-2">{{ member.nbm ? 'NBM: ' + member.nbm : 'Belum Memiliki NBM' }}</p>
                                
                                <Tag :value="member.status" :severity="getStatusSeverity(member.status)" class="uppercase text-xs" />

                                <Divider />

                                <div class="bg-gray-50 rounded-lg p-3 text-left">
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-xs font-bold text-gray-500 uppercase">Akun Sistem</span>
                                        <i v-if="member.user_id" class="pi pi-check-circle text-green-500"></i>
                                        <i v-else class="pi pi-times-circle text-red-400"></i>
                                    </div>
                                    
                                    <div v-if="member.user_id && member.user">
                                        <p class="text-sm font-semibold text-gray-800 truncate">{{ member.user.email }}</p>
                                        <p class="text-xs text-gray-400">Role: {{ member.user.role }}</p>
                                    </div>
                                    <div v-else>
                                        <p class="text-xs text-gray-400 mb-2">Belum memiliki akses login.</p>
                                        <Button label="Buat Akun" icon="pi pi-key" size="small" class="w-full" severity="success" 
                                                @click="generateAccount" />
                                    </div>
                                </div>
                            </div>
                        </template>
                    </Card>

                    <Card class="border border-gray-100 shadow-sm">
                        <template #title><span class="text-base font-bold">Kontak</span></template>
                        <template #content>
                            <ul class="space-y-3 text-sm">
                                <li class="flex items-start gap-3">
                                    <i class="pi pi-phone text-emerald-500 mt-1"></i>
                                    <span>{{ member.phone_number || '-' }}</span>
                                </li>
                                <li class="flex items-start gap-3">
                                    <i class="pi pi-map-marker text-emerald-500 mt-1"></i>
                                    <span>{{ member.address }} <br> 
                                        <span class="text-gray-400 text-xs">{{ member.village }}, {{ member.district }}, {{ member.regency }}</span>
                                    </span>
                                </li>
                            </ul>
                        </template>
                    </Card>
                </div>

                <div class="lg:col-span-2">
                    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden min-h-[500px]">
                        <TabView class="p-4">
                            
                            <TabPanel header="Biodata">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-y-6 gap-x-8 p-2">
                                    <div>
                                        <span class="text-xs text-gray-400 uppercase font-bold block mb-1">Tempat, Tanggal Lahir</span>
                                        <span class="text-gray-800">{{ member.birth_place }}, {{ formatDate(member.birth_date) }}</span>
                                    </div>
                                    <div>
                                        <span class="text-xs text-gray-400 uppercase font-bold block mb-1">Jenis Kelamin</span>
                                        <span class="text-gray-800">{{ member.gender === 'L' ? 'Laki-laki' : 'Perempuan' }}</span>
                                    </div>
                                    <div>
                                        <span class="text-xs text-gray-400 uppercase font-bold block mb-1">Pekerjaan</span>
                                        <span class="text-gray-800">{{ member.job || '-' }}</span>
                                    </div>
                                    <div>
                                        <span class="text-xs text-gray-400 uppercase font-bold block mb-1">Pendidikan Terakhir</span>
                                        <span class="text-gray-800 font-semibold">{{ member.last_education }}</span>
                                        <span class="text-gray-500 text-sm block">{{ member.education_institution }}</span>
                                    </div>
                                </div>
                            </TabPanel>

                            <TabPanel header="Keorganisasian">
                                <div class="space-y-6 p-2">
                                    <div>
                                        <div class="flex justify-between items-center border-b border-blue-100 pb-2 mb-3">
                                            <h3 class="text-sm font-bold text-blue-800">Keaktifan Ortom</h3>
                                            <Button icon="pi pi-plus" size="small" text rounded severity="info" 
                                                    @click="openHistoryDialog('ortom')" v-tooltip="'Tambah Ortom'" />
                                        </div>
                                        
                                        <div class="flex flex-wrap gap-2" v-if="member.active_ortoms && member.active_ortoms.length">
                                            <div v-for="(ortom, i) in member.active_ortoms" :key="i" 
                                                 class="flex items-center gap-2 bg-blue-50 text-blue-700 px-3 py-1 rounded-full text-sm border border-blue-100">
                                                <i class="pi pi-bookmark text-xs"></i>
                                                <span>{{ ortom }}</span>
                                                <i class="pi pi-times text-xs cursor-pointer hover:text-red-500 ml-1" 
                                                   @click="removeHistory('ortom', ortom)"></i>
                                            </div>
                                        </div>
                                        <p v-else class="text-gray-400 italic text-sm">Tidak ada data keaktifan Ortom.</p>
                                    </div>

                                    </div>
                            </TabPanel>

                            <TabPanel header="Riwayat Perkaderan">
                                <div class="p-2">
                                    <div class="flex justify-between items-center mb-4">
                                        <h3 class="text-sm font-bold text-gray-700">Pelatihan Formal</h3>
                                        <Button label="Tambah Pelatihan" icon="pi pi-plus" size="small" severity="warning" outlined 
                                                @click="openHistoryDialog('training')" />
                                    </div>

                                    <div v-if="member.training_history && member.training_history.length">
                                        <ul class="space-y-3">
                                            <li v-for="(training, index) in member.training_history" :key="index" 
                                                class="group flex justify-between items-center p-3 bg-gray-50 rounded-lg border border-gray-100 hover:border-orange-200 transition">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-8 h-8 rounded-full bg-orange-100 flex items-center justify-center text-orange-600 font-bold text-sm">
                                                        {{ index + 1 }}
                                                    </div>
                                                    <span class="font-medium text-gray-700">{{ training }}</span>
                                                </div>
                                                <Button icon="pi pi-trash" text rounded severity="danger" size="small" 
                                                        class="opacity-0 group-hover:opacity-100 transition" 
                                                        @click="removeHistory('training', training)" />
                                            </li>
                                        </ul>
                                    </div>
                                    <div v-else class="text-center py-8">
                                        <i class="pi pi-info-circle text-4xl text-gray-200 mb-2"></i>
                                        <p class="text-gray-400">Belum ada riwayat perkaderan formal.</p>
                                        <Button label="Input Data" text size="small" class="mt-2" @click="openHistoryDialog('training')" />
                                    </div>
                                </div>
                            </TabPanel>

                        </TabView>
                    </div>
                </div>

            </div>
        </div>
        <Dialog v-model:visible="historyDialog" modal :header="historyType === 'ortom' ? 'Tambah Keaktifan Ortom' : 'Tambah Riwayat Perkaderan'" :style="{ width: '25rem' }">
            <div class="space-y-4">
                <div class="flex flex-col gap-2">
                    <label class="text-sm font-bold text-gray-700">Pilih / Ketik Nama</label>
                    <Dropdown v-if="historyType === 'ortom'" 
                              v-model="historyForm.value" :options="ortomOptions" editable 
                              placeholder="Pilih atau ketik manual..." class="w-full" />
                    
                    <Dropdown v-else 
                              v-model="historyForm.value" :options="trainingOptions" editable 
                              placeholder="Contoh: Baitul Arqam Dasar" class="w-full" />
                    
                    <small class="text-gray-500">Anda bisa mengetik manual jika pilihan tidak tersedia.</small>
                </div>
            </div>
            <template #footer>
                <Button label="Batal" icon="pi pi-times" text @click="historyDialog = false" />
                <Button label="Simpan" icon="pi pi-check" @click="submitHistory" :loading="historyForm.processing" autofocus />
            </template>
        </Dialog>
        <ConfirmPopup />
    </AppLayout>
</template>


<style scoped>
/* Styling TabView agar lebih clean */
:deep(.p-tabview-nav) {
    border-color: #f3f4f6 !important;
}
:deep(.p-tabview-nav li .p-tabview-nav-link) {
    color: #6b7280;
    font-weight: 500;
}
:deep(.p-tabview-nav li.p-highlight .p-tabview-nav-link) {
    color: #10b981; /* Emerald */
    border-color: #10b981;
}
</style>