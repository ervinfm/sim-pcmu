<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { FilterMatchMode } from '@primevue/core/api';

// Components PrimeVue
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import Tag from 'primevue/tag';
import Avatar from 'primevue/avatar';
import ConfirmPopup from 'primevue/confirmpopup';
import Dialog from 'primevue/dialog';
import FileUpload from 'primevue/fileupload';
import Toast from 'primevue/toast';
import Toolbar from 'primevue/toolbar';
import Menu from 'primevue/menu'; // Component Menu untuk Dropdown
import { useConfirm } from "primevue/useconfirm";
import { useToast } from "primevue/usetoast";

const props = defineProps({
    members: Array,
});

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
});

const confirm = useConfirm();
const toast = useToast();

// --- LOGIC MENU DROPDOWN (ACTION) ---
const menu = ref();
const selectedMember = ref(null);

const toggleMenu = (event, data) => {
    selectedMember.value = data;
    menu.value.toggle(event);
};

const menuItems = computed(() => [
    {
        label: 'Opsi Data',
        items: [
            { label: 'Lihat Detail', icon: 'pi pi-eye', command: () => router.visit(route('members.show', selectedMember.value.id)) },
            { label: 'Edit Data', icon: 'pi pi-file-edit', command: () => router.visit(route('members.edit', selectedMember.value.id)) },
            {
                label: selectedMember.value?.user_id ? 'Akun Sudah Ada' : 'Generate Akun',
                icon: 'pi pi-key',
                disabled: !!selectedMember.value?.user_id,
                class: selectedMember.value?.user_id ? 'opacity-50 cursor-not-allowed' : 'text-emerald-600 font-semibold',
                command: () => confirmGenerateAccount(selectedMember.value)
            }
        ]
    },
    { separator: true },
    { label: 'Hapus', icon: 'pi pi-trash', class: 'text-red-600', command: () => handleDelete(selectedMember.value) }
]);

// --- HELPER VISUAL ---
const getInitials = (name) => {
    if (!name) return 'M';
    return name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
};

const getAvatarColor = (name) => {
    const colors = ['bg-blue-100 text-blue-700', 'bg-emerald-100 text-emerald-700', 'bg-orange-100 text-orange-700', 'bg-purple-100 text-purple-700'];
    let hash = 0;
    for (let i = 0; i < name.length; i++) hash = name.charCodeAt(i) + ((hash << 5) - hash);
    return colors[Math.abs(hash) % colors.length];
};

// Hitung Umur
const getAge = (birthDate) => {
    if (!birthDate) return '-';
    const today = new Date();
    const birth = new Date(birthDate);
    let age = today.getFullYear() - birth.getFullYear();
    const m = today.getMonth() - birth.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < birth.getDate())) age--;
    return age + ' Th';
};

// Translate Status
const getStatusLabel = (status) => {
    const map = { 'ACTIVE': 'Aktif', 'INACTIVE': 'Non-Aktif', 'MOVED': 'Pindah', 'DECEASED': 'Meninggal' };
    return map[status] || status;
};

const getStatusSeverity = (status) => {
    const map = { 'ACTIVE': 'success', 'INACTIVE': 'secondary', 'MOVED': 'warn', 'DECEASED': 'danger' };
    return map[status] || 'info';
};

// --- LOGIC LAINNYA (SAMA SEPERTI SEBELUMNYA) ---
const importDialog = ref(false);
const importForm = useForm({ file: null });
const openImport = () => { importDialog.value = true; importForm.reset(); };
const onFileSelect = (event) => { importForm.file = event.files[0]; };
const submitImport = () => {
    if (!importForm.file) return toast.add({ severity: 'warn', summary: 'Peringatan', detail: 'Pilih file dulu.', life: 3000 });
    importForm.post(route('members.import'), {
        onSuccess: () => { importDialog.value = false; importForm.reset(); toast.add({ severity: 'success', summary: 'Berhasil', detail: 'Data diimport.', life: 3000 }); },
        onError: () => toast.add({ severity: 'error', summary: 'Gagal', detail: 'Error import.', life: 3000 })
    });
};
const confirmGenerateAccount = (member) => {
    confirm.require({
        message: `Buat akun untuk "${member.full_name}"?`, header: 'Konfirmasi', icon: 'pi pi-key', acceptClass: 'p-button-success',
        accept: () => router.post(route('members.generate_account', member.id), {}, { onSuccess: () => toast.add({ severity: 'success', summary: 'Sukses', detail: 'Akun dibuat.', life: 3000 }) })
    });
};
const handleDelete = (member) => {
    confirm.require({
        message: 'Hapus data ini?', header: 'Konfirmasi', icon: 'pi pi-exclamation-triangle', acceptClass: 'p-button-danger',
        accept: () => router.delete(route('members.destroy', member.id))
    });
};
</script>

<template>
    <Head title="Data Anggota" />
    <AppLayout>
        <div class="space-y-6">
            
            <Toolbar class="!border-none !bg-white !p-4 !rounded-xl shadow-sm">
                <template #start>
                    <div>
                        <h1 class="text-2xl font-black text-gray-800 tracking-tight">Database Anggota</h1>
                        <p class="text-gray-500 text-sm mt-1">Manajemen data kader dan pimpinan.</p>
                    </div>
                </template>
                <template #end>
                    <div class="flex gap-2">
                        <Link :href="route('members.import_wizard')">
                            <Button label="Import Excel" icon="pi pi-file-excel" severity="success" text class="hover:bg-green-50" />
                        </Link>
                        <Link :href="route('members.create')">
                            <Button label="Anggota Baru" icon="pi pi-user-plus" class="!bg-emerald-600 !border-emerald-600 hover:!bg-emerald-700 !rounded-lg !text-sm font-bold shadow-lg shadow-emerald-100" />
                        </Link>
                    </div>
                </template>
            </Toolbar>

            <div class="card bg-white p-1 rounded-xl border border-gray-100 shadow-sm">
                <DataTable v-model:filters="filters" :value="members" paginator :rows="10" :rowsPerPageOptions="[10, 20, 50]"
                           :globalFilterFields="['full_name', 'nbm', 'organization_unit.name']"
                           sortField="full_name" :sortOrder="1" class="p-datatable-sm" stripedRows removableSort>
                    
                    <template #header>
                        <div class="flex justify-between items-center py-2 px-3">
                            <span class="text-lg font-bold text-gray-700">List Data</span>
                            <IconField iconPosition="left">
                                <InputIcon class="pi pi-search" />
                                <InputText v-model="filters['global'].value" placeholder="Cari anggota..." class="w-full md:w-64 !rounded-lg" />
                            </IconField>
                        </div>
                    </template>

                    <template #empty><div class="text-center py-8 text-gray-400">Belum ada data anggota.</div></template>

                    <Column header="Profil Anggota" style="width: 35%" sortable field="full_name">
                        <template #body="{ data }">
                            <div class="flex items-center gap-3 py-1">
                                <Avatar v-if="data.photo_path" :image="'/storage/' + data.photo_path" class="!w-10 !h-10 rounded-full border border-gray-100 shadow-sm object-cover" />
                                <Avatar v-else :label="getInitials(data.full_name)" class="!w-10 !h-10 !font-bold border border-white shadow-sm rounded-full" :class="getAvatarColor(data.full_name)" />
                                
                                <div>
                                    <div class="font-bold text-gray-800 text-sm">{{ data.full_name }}</div>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        <span v-if="data.nbm" class="text-[10px] bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded border border-gray-200 font-mono tracking-wide">{{ data.nbm }}</span>
                                        <span class="text-[10px] text-gray-400 border-l pl-2 border-gray-300">{{ getAge(data.birth_date) }}</span>
                                        <span class="text-[10px] text-gray-400 border-l pl-2 border-gray-300 font-bold">{{ data.last_education }}</span>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </Column>

                    <Column field="organization_unit.name" header="Basis & Jabatan" sortable style="width: 25%">
                        <template #body="{ data }">
                            <div class="flex flex-col">
                                <span class="text-sm font-semibold text-gray-700">{{ data.organization_unit?.name }}</span>
                                <span class="text-[10px] text-emerald-600 font-bold uppercase tracking-wider mt-0.5">
                                    {{ data.muhammadiyah_position || 'ANGGOTA' }}
                                </span>
                            </div>
                        </template>
                    </Column>
                    
                    <Column header="Kontak" style="width: 20%">
                        <template #body="{ data }">
                            <div class="text-xs space-y-1">
                                <div v-if="data.phone_number" class="flex items-center gap-1.5">
                                    <i class="pi pi-whatsapp text-green-500"></i> 
                                    <a :href="`https://wa.me/${data.phone_number.replace(/^0/, '62')}`" target="_blank" class="hover:underline text-gray-600 font-medium">{{ data.phone_number }}</a>
                                </div>
                                <div v-if="data.address" class="flex items-start gap-1.5 text-gray-500">
                                    <i class="pi pi-map-marker text-red-400 mt-0.5"></i>
                                    <span class="truncate max-w-[140px]" :title="data.address">{{ data.address }}</span>
                                </div>
                            </div>
                        </template>
                    </Column>

                    <Column field="status" header="Status" sortable style="width: 10%">
                        <template #body="{ data }">
                            <Tag :value="getStatusLabel(data.status)" :severity="getStatusSeverity(data.status)" class="!text-[10px] !px-2 uppercase" rounded />
                        </template>
                    </Column>

                    <Column header="Aksi" style="width: 10%; text-align: center">
                        <template #body="{ data }">
                            <Button label="Kelola" icon="pi pi-chevron-down" iconPos="right" 
                                    size="small" outlined severity="secondary" 
                                    class="!text-xs !py-1 !px-2 !h-8 !border-gray-300 hover:!bg-gray-50 hover:!text-gray-900"
                                    @click="toggleMenu($event, data)" aria-haspopup="true" aria-controls="overlay_menu" />
                        </template>
                    </Column>
                </DataTable>
            </div>
        </div>

        <Menu ref="menu" id="overlay_menu" :model="menuItems" :popup="true" class="w-48 text-sm" />

        <ConfirmPopup />
        <Toast />
    </AppLayout>
</template>