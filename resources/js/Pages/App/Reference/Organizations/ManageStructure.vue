<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Dropdown from 'primevue/dropdown';
import Calendar from 'primevue/calendar';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Avatar from 'primevue/avatar';
import Tag from 'primevue/tag';
import { useConfirm } from "primevue/useconfirm";
import ConfirmPopup from 'primevue/confirmpopup';

const props = defineProps({
    organization: Object,
    members: Array // List untuk dropdown
});

const form = useForm({
    member_id: null,
    position_name: '',
    position_type: 'PIMPINAN_HARIAN',
    sk_number: '',
    start_date: null,
    end_date: null
});

const positionTypes = [
    { label: 'Pimpinan Harian', value: 'PIMPINAN_HARIAN' },
    { label: 'Majelis / Lembaga', value: 'MAJELIS' },
    { label: 'Staf / Anggota', value: 'STAF' }
];

const submit = () => {
    form.post(route('organizations.structure.store', props.organization.id), {
        onSuccess: () => form.reset(),
        preserveScroll: true
    });
};

const confirm = useConfirm();
const handleDelete = (event, id) => {
    confirm.require({
        target: event.currentTarget,
        message: 'Lepaskan jabatan anggota ini?',
        icon: 'pi pi-exclamation-triangle',
        acceptClass: 'p-button-danger',
        accept: () => {
            // Gunakan router inertia manual untuk delete
            form.delete(route('organizations.structure.destroy', id), { preserveScroll: true });
        }
    });
};
</script>

<template>
    <Head title="Kelola Struktur" />
    <AppLayout>
        <div class="max-w-5xl mx-auto space-y-6">
            
            <div class="flex items-center justify-between">
                <div>
                    <Link :href="route('organizations.index')" class="text-sm font-bold text-gray-500 hover:text-emerald-600 mb-1 block">
                        <i class="pi pi-arrow-left text-xs"></i> Kembali
                    </Link>
                    <h1 class="text-2xl font-black text-gray-800">Manajemen Struktur</h1>
                    <p class="text-gray-500">{{ organization.name }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm h-fit">
                    <h3 class="font-bold text-gray-800 mb-4 border-b pb-2">Tambah Pengurus</h3>
                    <form @submit.prevent="submit" class="space-y-4">
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-gray-500 uppercase">Anggota</label>
                            <Dropdown v-model="form.member_id" :options="members" optionLabel="label" optionValue="value" 
                                      filter placeholder="Cari Nama..." class="w-full" />
                            <small class="text-red-500">{{ form.errors.member_id }}</small>
                        </div>
                        
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-gray-500 uppercase">Jabatan</label>
                            <InputText v-model="form.position_name" placeholder="Contoh: Ketua" class="w-full" />
                            <small class="text-red-500">{{ form.errors.position_name }}</small>
                        </div>

                        <div class="space-y-1">
                            <label class="text-xs font-bold text-gray-500 uppercase">Kategori</label>
                            <Dropdown v-model="form.position_type" :options="positionTypes" optionLabel="label" optionValue="value" class="w-full" />
                        </div>

                       <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                                    Mulai Menjabat
                                </label>
                                <div class="relative">
                                    <Calendar v-model="form.start_date" 
                                              showIcon 
                                              iconDisplay="input" 
                                              dateFormat="yy-mm-dd" 
                                              placeholder="Pilih Tanggal"
                                              class="w-full" 
                                              inputClass="w-full !rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-200 text-sm" />
                                </div>
                            </div>

                            <div class="space-y-1.5">
                                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                                    Selesai Menjabat
                                </label>
                                <div class="relative">
                                    <Calendar v-model="form.end_date" 
                                              showIcon 
                                              iconDisplay="input" 
                                              dateFormat="yy-mm-dd" 
                                              placeholder="Selesai (Opsional)"
                                              class="w-full" 
                                              inputClass="w-full !rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-200 text-sm" />
                                </div>
                            </div>
                        </div>

                        <Button type="submit" label="Simpan Pengurus" icon="pi pi-check" class="w-full !bg-emerald-600 !border-emerald-600 mt-2" :loading="form.processing" />
                    </form>
                </div>

                <div class="lg:col-span-2 bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
                    <h3 class="font-bold text-gray-800 mb-4">Daftar Pengurus Aktif</h3>
                    
                    <DataTable :value="organization.structures" paginator :rows="5" size="small" stripedRows>
                        <template #empty>Belum ada data pengurus.</template>
                        
                        <Column header="Nama Anggota">
                            <template #body="{data}">
                                <div class="flex items-center gap-3">
                                    <Avatar icon="pi pi-user" shape="circle" class="bg-emerald-50 text-emerald-600" />
                                    <div>
                                        <div class="font-bold text-gray-700">{{ data.member?.full_name }}</div>
                                        <div class="text-xs text-gray-400">{{ data.member?.nbm || 'Non-NBM' }}</div>
                                    </div>
                                </div>
                            </template>
                        </Column>
                        
                        <Column header="Jabatan">
                            <template #body="{data}">
                                <div class="font-bold text-emerald-700">{{ data.position_name }}</div>
                                <Tag :value="data.position_type.replace('_', ' ')" severity="info" class="!text-[9px]" />
                            </template>
                        </Column>

                        <Column header="Periode">
                            <template #body="{data}">
                                <span class="text-xs">{{ data.start_date ? new Date(data.start_date).getFullYear() : '?' }} - {{ data.end_date ? new Date(data.end_date).getFullYear() : 'Selesai' }}</span>
                            </template>
                        </Column>

                        <Column style="width: 10%">
                            <template #body="{data}">
                                <Button icon="pi pi-trash" text rounded severity="danger" size="small" @click="handleDelete($event, data.id)" />
                            </template>
                        </Column>
                    </DataTable>
                </div>

            </div>
        </div>
        <ConfirmPopup />
    </AppLayout>
</template>