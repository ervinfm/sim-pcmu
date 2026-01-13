<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Chip from 'primevue/chip';
import { useConfirm } from "primevue/useconfirm";
import ConfirmPopup from 'primevue/confirmpopup';

const props = defineProps({ organization: Object });

const form = useForm({
    name: '',
    postal_code: ''
});

const submit = () => {
    form.post(route('organizations.territory.store', props.organization.id), {
        onSuccess: () => form.reset(),
        preserveScroll: true
    });
};

const confirm = useConfirm();
const handleDelete = (event, id) => {
    confirm.require({
        target: event.currentTarget,
        message: 'Hapus wilayah ini dari cakupan?',
        icon: 'pi pi-trash',
        acceptClass: 'p-button-danger',
        accept: () => {
            form.delete(route('organizations.territory.destroy', id), { preserveScroll: true });
        }
    });
};
</script>

<template>
    <Head title="Kelola Wilayah" />
    <AppLayout>
        <div class="max-w-4xl mx-auto space-y-6">
            
            <div class="flex items-center justify-between">
                <div>
                    <Link :href="route('organizations.index')" class="text-sm font-bold text-gray-500 hover:text-blue-600 mb-1 block">
                        <i class="pi pi-arrow-left text-xs"></i> Kembali
                    </Link>
                    <h1 class="text-2xl font-black text-gray-800">Manajemen Wilayah</h1>
                    <p class="text-gray-500">Cakupan Dakwah: {{ organization.name }}</p>
                </div>
            </div>

            <div class="bg-white p-8 rounded-3xl border border-gray-200 shadow-sm">
                
                <div class="flex flex-col md:flex-row gap-4 items-end mb-8 p-4 bg-blue-50 rounded-xl border border-blue-100">
                    <div class="flex-1 w-full space-y-1">
                        <label class="text-xs font-bold text-blue-600 uppercase">Nama Desa / Kelurahan</label>
                        <InputText v-model="form.name" placeholder="Contoh: Desa Suka Maju" class="w-full" />
                    </div>
                    <div class="w-full md:w-48 space-y-1">
                        <label class="text-xs font-bold text-blue-600 uppercase">Kode Pos</label>
                        <InputText v-model="form.postal_code" placeholder="Optional" class="w-full" />
                    </div>
                    <Button label="Tambah" icon="pi pi-plus" @click="submit" :loading="form.processing" class="!bg-blue-600 !border-blue-600" />
                </div>

                <h3 class="font-bold text-gray-800 mb-4">Daftar Wilayah Binaan</h3>
                
                <div v-if="organization.territories.length > 0" class="flex flex-wrap gap-3">
                    <div v-for="geo in organization.territories" :key="geo.id" 
                         class="group flex items-center gap-2 bg-white border border-gray-200 px-4 py-2 rounded-full hover:border-red-200 hover:bg-red-50 transition-all cursor-default">
                        <i class="pi pi-map-marker text-gray-400 group-hover:text-red-400"></i>
                        <span class="font-medium text-gray-700 group-hover:text-red-700">{{ geo.name }}</span>
                        <span v-if="geo.postal_code" class="text-xs text-gray-400">({{ geo.postal_code }})</span>
                        
                        <button @click="handleDelete($event, geo.id)" class="ml-2 w-5 h-5 rounded-full bg-red-100 text-red-600 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity hover:bg-red-200 cursor-pointer">
                            <i class="pi pi-times text-[10px] font-bold"></i>
                        </button>
                    </div>
                </div>
                
                <div v-else class="text-center py-12 text-gray-400 border-2 border-dashed border-gray-100 rounded-xl">
                    <i class="pi pi-map text-4xl mb-2 text-gray-300"></i>
                    <p>Belum ada data wilayah. Silakan tambahkan.</p>
                </div>

            </div>
        </div>
        <ConfirmPopup />
    </AppLayout>
</template>