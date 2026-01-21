<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

// --- PRIMEVUE COMPONENTS ---
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Dialog from 'primevue/dialog';
import Textarea from 'primevue/textarea';
import Tag from 'primevue/tag';
import ConfirmPopup from 'primevue/confirmpopup';
import { useConfirm } from 'primevue/useconfirm';

// PROPS
const props = defineProps({
    units: Array,
    locations: Array,
});

// INIT
const confirm = useConfirm();

// --- SEARCH LOGIC ---
const searchUnit = ref('');
const searchLocation = ref('');

const filteredUnits = computed(() => {
    if (!searchUnit.value) return props.units;
    const q = searchUnit.value.toLowerCase();
    return props.units.filter(u => u.name.toLowerCase().includes(q) || u.code.toLowerCase().includes(q));
});

const filteredLocations = computed(() => {
    if (!searchLocation.value) return props.locations;
    const q = searchLocation.value.toLowerCase();
    return props.locations.filter(l => l.name.toLowerCase().includes(q) || (l.description && l.description.toLowerCase().includes(q)));
});

// --- HELPER VALIDASI INPUT ---
// 1. Force Uppercase & No Space (Untuk KODE)
const handleCodeInput = (event, form, field) => {
    // Ubah ke Kapital dan Hapus Spasi
    form[field] = event.target.value.toUpperCase().replace(/\s+/g, '');
};

// 2. Force Title Case (Untuk NAMA - Opsional jika ingin memaksa huruf besar awal)
const handleNameInput = (event, form, field) => {
    // Hanya visual class 'capitalize' biasanya cukup, tapi jika ingin memaksa data:
    // form[field] = event.target.value.replace(/\b\w/g, l => l.toUpperCase());
    // Disini kita biarkan input normal tapi visualnya kapital
};

// --- UNIT LOGIC ---
const showUnitDialog = ref(false);
const isEditUnit = ref(false);
const formUnit = useForm({ id: null, code: '', name: '' });

const openCreateUnit = () => { isEditUnit.value = false; formUnit.reset(); showUnitDialog.value = true; };
const openEditUnit = (unit) => { isEditUnit.value = true; formUnit.id = unit.id; formUnit.code = unit.code; formUnit.name = unit.name; showUnitDialog.value = true; };

const submitUnit = () => {
    const routeName = isEditUnit.value ? 'assets.references.units.update' : 'assets.references.units.store';
    const options = { onSuccess: () => showUnitDialog.value = false };
    if (isEditUnit.value) formUnit.put(route(routeName, formUnit.id), options);
    else formUnit.post(route(routeName), options);
};

const confirmDeleteUnit = (event, id) => {
    confirm.require({
        target: event.currentTarget,
        message: 'Hapus satuan ini?',
        icon: 'pi pi-exclamation-triangle',
        acceptClass: 'p-button-danger p-button-sm',
        acceptLabel: 'Hapus',
        rejectLabel: 'Batal',
        accept: () => router.delete(route('assets.references.units.destroy', id))
    });
};

// --- LOCATION LOGIC ---
const showLocDialog = ref(false);
const isEditLoc = ref(false);
const formLoc = useForm({ id: null, name: '', description: '' });

const openCreateLoc = () => { isEditLoc.value = false; formLoc.reset(); showLocDialog.value = true; };
const openEditLoc = (loc) => { isEditLoc.value = true; formLoc.id = loc.id; formLoc.name = loc.name; formLoc.description = loc.description; showLocDialog.value = true; };

const submitLoc = () => {
    const routeName = isEditLoc.value ? 'assets.references.locations.update' : 'assets.references.locations.store';
    const options = { onSuccess: () => showLocDialog.value = false };
    if (isEditLoc.value) formLoc.put(route(routeName, formLoc.id), options);
    else formLoc.post(route(routeName), options);
};

const confirmDeleteLoc = (event, id) => {
    confirm.require({
        target: event.currentTarget,
        message: 'Hapus lokasi ini?',
        icon: 'pi pi-exclamation-triangle',
        acceptClass: 'p-button-danger p-button-sm',
        acceptLabel: 'Hapus',
        rejectLabel: 'Batal',
        accept: () => router.delete(route('assets.references.locations.destroy', id))
    });
};
</script>

<template>
    <Head title="Data Master Aset" />

    <AppLayout>
        <ConfirmPopup />

        <div class="max-w-7xl mx-auto space-y-6 pb-12">
            
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-gray-200 pb-4">
                <div>
                    <h1 class="text-3xl font-black text-gray-900 tracking-tight font-sans">
                        Data Master <span class="text-blue-600">Aset</span>
                    </h1>
                    <p class="text-gray-500 mt-1 text-sm">Kelola referensi satuan dan lokasi penyimpanan.</p>
                </div>
                <Link :href="route('assets.index')" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-xl text-sm font-bold shadow-sm hover:bg-gray-50 transition flex items-center gap-2">
                    <i class="pi pi-arrow-left"></i> Kembali
                </Link>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 flex flex-col h-full overflow-hidden transition-all hover:shadow-xl">
                    
                    <div class="p-5 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white flex justify-between items-center">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 shadow-sm">
                                <i class="pi pi-box text-lg"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-800">Satuan Barang</h3>
                                <p class="text-xs text-gray-500">{{ props.units.length }} Data Tersedia</p>
                            </div>
                        </div>
                        <Button icon="pi pi-plus" rounded class="!bg-blue-600 !border-blue-600 !w-8 !h-8 !p-0" @click="openCreateUnit" v-tooltip.top="'Tambah Satuan'" />
                    </div>

                    <div class="px-5 pt-4 pb-2">
                        <div class="relative">
                            <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                            <input v-model="searchUnit" type="text" placeholder="Cari satuan..." 
                                class="w-full pl-9 pr-3 py-2 bg-gray-50 border-none rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-blue-100 transition-all placeholder-gray-400 uppercase">
                        </div>
                    </div>

                    <div class="p-2 flex-1">
                        <DataTable :value="filteredUnits" paginator :rows="5" size="small" class="modern-table">
                            <template #empty><div class="text-center py-8 text-gray-400 text-sm">Data kosong.</div></template>
                            
                            <Column field="code" header="KODE">
                                <template #body="slotProps">
                                    <Tag :value="slotProps.data.code" severity="contrast" class="!font-mono !text-[10px] !px-2" />
                                </template>
                            </Column>
                            <Column field="name" header="NAMA">
                                <template #body="slotProps">
                                    <span class="font-semibold text-gray-700 text-sm">{{ slotProps.data.name }}</span>
                                </template>
                            </Column>
                            <Column header="" style="width: 80px; text-align: right">
                                <template #body="slotProps">
                                    <div class="flex gap-1 justify-end">
                                        <button @click="openEditUnit(slotProps.data)" class="w-7 h-7 rounded-lg bg-orange-50 text-orange-600 hover:bg-orange-100 flex items-center justify-center transition">
                                            <i class="pi pi-pencil text-[10px]"></i>
                                        </button>
                                        <button @click="confirmDeleteUnit($event, slotProps.data.id)" class="w-7 h-7 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 flex items-center justify-center transition">
                                            <i class="pi pi-trash text-[10px]"></i>
                                        </button>
                                    </div>
                                </template>
                            </Column>
                        </DataTable>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 flex flex-col h-full overflow-hidden transition-all hover:shadow-xl">
                    
                    <div class="p-5 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white flex justify-between items-center">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600 shadow-sm">
                                <i class="pi pi-map-marker text-lg"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-800">Lokasi Aset</h3>
                                <p class="text-xs text-gray-500">{{ props.locations.length }} Data Tersedia</p>
                            </div>
                        </div>
                        <Button icon="pi pi-plus" rounded class="!bg-emerald-600 !border-emerald-600 !w-8 !h-8 !p-0" @click="openCreateLoc" v-tooltip.top="'Tambah Lokasi'" />
                    </div>

                    <div class="px-5 pt-4 pb-2">
                        <div class="relative">
                            <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                            <input v-model="searchLocation" type="text" placeholder="Cari ruangan..." 
                                class="w-full pl-9 pr-3 py-2 bg-gray-50 border-none rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-emerald-100 transition-all placeholder-gray-400 capitalize">
                        </div>
                    </div>

                    <div class="p-2 flex-1">
                        <DataTable :value="filteredLocations" paginator :rows="5" size="small" class="modern-table">
                            <template #empty><div class="text-center py-8 text-gray-400 text-sm">Data kosong.</div></template>
                            
                            <Column field="name" header="NAMA RUANGAN">
                                <template #body="slotProps">
                                    <span class="font-bold text-gray-700 text-sm">{{ slotProps.data.name }}</span>
                                    <div class="text-[10px] text-gray-400 mt-0.5 truncate max-w-[150px]">{{ slotProps.data.organization_unit?.name }}</div>
                                </template>
                            </Column>
                            <Column field="description" header="KET">
                                <template #body="slotProps">
                                    <span class="text-gray-500 text-xs truncate max-w-[100px] block" :title="slotProps.data.description">{{ slotProps.data.description || '-' }}</span>
                                </template>
                            </Column>
                            <Column header="" style="width: 80px; text-align: right">
                                <template #body="slotProps">
                                    <div class="flex gap-1 justify-end">
                                        <button @click="openEditLoc(slotProps.data)" class="w-7 h-7 rounded-lg bg-orange-50 text-orange-600 hover:bg-orange-100 flex items-center justify-center transition">
                                            <i class="pi pi-pencil text-[10px]"></i>
                                        </button>
                                        <button @click="confirmDeleteLoc($event, slotProps.data.id)" class="w-7 h-7 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 flex items-center justify-center transition">
                                            <i class="pi pi-trash text-[10px]"></i>
                                        </button>
                                    </div>
                                </template>
                            </Column>
                        </DataTable>
                    </div>
                </div>

            </div>

            <Dialog v-model:visible="showUnitDialog" modal :header="isEditUnit ? 'Edit Satuan' : 'Satuan Baru'" :style="{ width: '350px' }" class="rounded-2xl">
                <div class="space-y-4 pt-2">
                    <div>
                        <label class="text-xs font-bold text-gray-500 uppercase">
                            Kode <span class="text-red-500">*</span>
                        </label>
                        <InputText 
                            v-model="formUnit.code" 
                            @input="(e) => handleCodeInput(e, formUnit, 'code')"
                            placeholder="CONTOH: PCS" 
                            class="w-full !rounded-lg mt-1 uppercase font-mono" 
                            :class="{'p-invalid': formUnit.errors.code}"
                        />
                        <small class="text-red-500 text-xs" v-if="formUnit.errors.code">{{ formUnit.errors.code }}</small>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-gray-500 uppercase">
                            Nama Satuan <span class="text-red-500">*</span>
                        </label>
                        <InputText 
                            v-model="formUnit.name" 
                            placeholder="Pieces / Unit" 
                            class="w-full !rounded-lg mt-1 capitalize" 
                            :class="{'p-invalid': formUnit.errors.name}"
                        />
                        <small class="text-red-500 text-xs" v-if="formUnit.errors.name">{{ formUnit.errors.name }}</small>
                    </div>
                </div>
                <template #footer>
                    <div class="flex justify-end gap-2 pt-4">
                        <Button label="Batal" text @click="showUnitDialog = false" class="!text-gray-500 !text-sm" />
                        <Button label="Simpan" @click="submitUnit" :loading="formUnit.processing" class="!bg-blue-600 !border-none !rounded-lg !text-sm" />
                    </div>
                </template>
            </Dialog>

            <Dialog v-model:visible="showLocDialog" modal :header="isEditLoc ? 'Edit Lokasi' : 'Lokasi Baru'" :style="{ width: '400px' }" class="rounded-2xl">
                <div class="space-y-4 pt-2">
                    <div>
                        <label class="text-xs font-bold text-gray-500 uppercase">
                            Nama Ruangan / Lokasi <span class="text-red-500">*</span>
                        </label>
                        <InputText 
                            v-model="formLoc.name" 
                            placeholder="Contoh: Gudang Belakang" 
                            class="w-full !rounded-lg mt-1 capitalize" 
                            :class="{'p-invalid': formLoc.errors.name}"
                        />
                        <small class="text-red-500 text-xs" v-if="formLoc.errors.name">{{ formLoc.errors.name }}</small>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-gray-500 uppercase">Deskripsi</label>
                        <Textarea v-model="formLoc.description" rows="3" placeholder="Keterangan..." class="w-full !rounded-lg mt-1" />
                    </div>
                </div>
                <template #footer>
                    <div class="flex justify-end gap-2 pt-4">
                        <Button label="Batal" text @click="showLocDialog = false" class="!text-gray-500 !text-sm" />
                        <Button label="Simpan" @click="submitLoc" :loading="formLoc.processing" class="!bg-emerald-600 !border-none !rounded-lg !text-sm" />
                    </div>
                </template>
            </Dialog>

        </div>
    </AppLayout>
</template>

<style scoped>
/* Table Compact Styling */
:deep(.modern-table .p-datatable-thead > tr > th) {
    background: transparent;
    color: #94a3b8;
    font-size: 0.7rem;
    font-weight: 800;
    letter-spacing: 0.05em;
    padding: 0.75rem 1rem;
    border-bottom: 1px solid #f1f5f9;
}
:deep(.modern-table .p-datatable-tbody > tr > td) {
    padding: 0.75rem 1rem;
    border-bottom: 1px solid #f8fafc;
}
:deep(.p-datatable .p-paginator-bottom) {
    border-top: 1px solid #f3f4f6;
    padding: 0.5rem;
}
</style>