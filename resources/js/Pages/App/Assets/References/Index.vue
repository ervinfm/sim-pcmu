<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

// PrimeVue Components
import TabView from 'primevue/tabview';
import TabPanel from 'primevue/tabpanel';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Dialog from 'primevue/dialog';
import Textarea from 'primevue/textarea';
import Tag from 'primevue/tag';

// PROPS (Data Mentah dari Controller)
const props = defineProps({
    units: Array,
    locations: Array,
});

// --- STATE: UI & TABS ---
const activeIndex = ref(0);

// --- 1. CLIENT-SIDE SEARCH LOGIC (REALTIME) ---
// Kita tidak perlu request ke server setiap ketik. Filter langsung di browser.
const searchUnit = ref('');
const searchLocation = ref('');

// Computed Property untuk Filter Satuan
const filteredUnits = computed(() => {
    if (!searchUnit.value) return props.units;
    const lowerSearch = searchUnit.value.toLowerCase();
    return props.units.filter(u => 
        u.name.toLowerCase().includes(lowerSearch) || 
        u.code.toLowerCase().includes(lowerSearch)
    );
});

// Computed Property untuk Filter Lokasi
const filteredLocations = computed(() => {
    if (!searchLocation.value) return props.locations;
    const lowerSearch = searchLocation.value.toLowerCase();
    return props.locations.filter(l => 
        l.name.toLowerCase().includes(lowerSearch) || 
        (l.description && l.description.toLowerCase().includes(lowerSearch))
    );
});

// =============================================================================
// LOGIC CRUD: SATUAN (UNITS)
// =============================================================================
const showUnitDialog = ref(false);
const isEditUnit = ref(false);
const formUnit = useForm({ id: null, code: '', name: '' });

const openCreateUnit = () => {
    isEditUnit.value = false;
    formUnit.reset();
    showUnitDialog.value = true;
};

const openEditUnit = (unit) => {
    isEditUnit.value = true;
    formUnit.id = unit.id;
    formUnit.code = unit.code;
    formUnit.name = unit.name;
    showUnitDialog.value = true;
};

const submitUnit = () => {
    const routeName = isEditUnit.value ? 'assets.references.units.update' : 'assets.references.units.store';
    const options = { onSuccess: () => showUnitDialog.value = false };
    
    if (isEditUnit.value) formUnit.put(route(routeName, formUnit.id), options);
    else formUnit.post(route(routeName), options);
};

const deleteUnit = (id) => {
    if (confirm('Hapus satuan ini?')) router.delete(route('assets.references.units.destroy', id));
};

// =============================================================================
// LOGIC CRUD: LOKASI (LOCATIONS)
// =============================================================================
const showLocDialog = ref(false);
const isEditLoc = ref(false);
const formLoc = useForm({ id: null, name: '', description: '' });

const openCreateLoc = () => {
    isEditLoc.value = false;
    formLoc.reset();
    showLocDialog.value = true;
};

const openEditLoc = (loc) => {
    isEditLoc.value = true;
    formLoc.id = loc.id;
    formLoc.name = loc.name;
    formLoc.description = loc.description;
    showLocDialog.value = true;
};

const submitLoc = () => {
    const routeName = isEditLoc.value ? 'assets.references.locations.update' : 'assets.references.locations.store';
    const options = { onSuccess: () => showLocDialog.value = false };

    if (isEditLoc.value) formLoc.put(route(routeName, formLoc.id), options);
    else formLoc.post(route(routeName), options);
};

const deleteLoc = (id) => {
    if (confirm('Hapus lokasi ini? Pastikan kosong.')) router.delete(route('assets.references.locations.destroy', id));
};
</script>

<template>
    <Head title="Data Master Aset" />

    <AppLayout>
        <div class="max-w-7xl mx-auto space-y-8 pb-12">
            
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 border-b border-gray-200 pb-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Data Master</h1>
                    <p class="text-gray-500 mt-1 text-sm">
                        Pusat pengaturan referensi untuk standarisasi data aset.
                    </p>
                </div>
                
                <div>
                    <Link :href="route('assets.index')" class="bg-white border border-gray-300 text-gray-700 px-5 py-2.5 rounded-xl text-sm font-semibold shadow-sm hover:bg-gray-50 hover:text-blue-600 transition flex items-center gap-2">
                        <i class="pi pi-arrow-left"></i>
                        <span>Kembali ke Aset</span>
                    </Link>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden min-h-[600px]">
                <TabView v-model:activeIndex="activeIndex" class="p-0">
                    
                    <TabPanel>
                        <template #header>
                            <div class="flex items-center gap-2 px-1 py-1">
                                <i class="pi pi-box"></i>
                                <span class="font-bold tracking-wide">Satuan Barang</span>
                            </div>
                        </template>

                        <div class="p-6 md:p-8">
                            <div class="flex flex-col sm:flex-row justify-between items-center gap-4 mb-6 bg-gray-50 p-4 rounded-xl border border-gray-100">
                                <div class="relative w-full sm:w-72">
                                    <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                                    <input 
                                        v-model="searchUnit" 
                                        type="text" 
                                        placeholder="Cari kode atau nama..." 
                                        class="w-full pl-10 pr-4 py-2.5 rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm transition"
                                    >
                                </div>
                                <Button @click="openCreateUnit" label="Tambah Satuan" icon="pi pi-plus" class="!bg-blue-600 !border-blue-600 !rounded-lg !px-4 !py-2.5 !text-sm !font-bold shadow-md shadow-blue-100 w-full sm:w-auto" />
                            </div>

                            <DataTable :value="filteredUnits" paginator :rows="10" stripedRows tableStyle="min-width: 50rem" class="p-datatable-sm">
                                <template #empty> 
                                    <div class="flex flex-col items-center justify-center py-12 text-gray-400">
                                        <i class="pi pi-inbox text-4xl mb-2 opacity-50"></i>
                                        <p>Data satuan tidak ditemukan.</p>
                                    </div> 
                                </template>
                                
                                <Column field="code" header="Kode" sortable style="width: 20%">
                                    <template #body="slotProps">
                                        <Tag :value="slotProps.data.code" severity="contrast" class="font-mono text-xs" />
                                    </template>
                                </Column>
                                <Column field="name" header="Nama Satuan" sortable style="width: 60%">
                                    <template #body="slotProps">
                                        <span class="font-semibold text-gray-700">{{ slotProps.data.name }}</span>
                                    </template>
                                </Column>
                                <Column header="Aksi" style="width: 20%; text-align: right">
                                    <template #body="slotProps">
                                        <div class="flex justify-end gap-2">
                                            <button @click="openEditUnit(slotProps.data)" class="w-8 h-8 rounded-full bg-yellow-50 text-yellow-600 hover:bg-yellow-100 flex items-center justify-center transition">
                                                <i class="pi pi-pencil text-xs"></i>
                                            </button>
                                            <button @click="deleteUnit(slotProps.data.id)" class="w-8 h-8 rounded-full bg-red-50 text-red-600 hover:bg-red-100 flex items-center justify-center transition">
                                                <i class="pi pi-trash text-xs"></i>
                                            </button>
                                        </div>
                                    </template>
                                </Column>
                            </DataTable>
                        </div>
                    </TabPanel>

                    <TabPanel>
                        <template #header>
                            <div class="flex items-center gap-2 px-1 py-1">
                                <i class="pi pi-map-marker"></i>
                                <span class="font-bold tracking-wide">Lokasi Penyimpanan</span>
                            </div>
                        </template>

                        <div class="p-6 md:p-8">
                            <div class="flex flex-col sm:flex-row justify-between items-center gap-4 mb-6 bg-gray-50 p-4 rounded-xl border border-gray-100">
                                <div class="relative w-full sm:w-72">
                                    <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                                    <input 
                                        v-model="searchLocation" 
                                        type="text" 
                                        placeholder="Cari lokasi..." 
                                        class="w-full pl-10 pr-4 py-2.5 rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm transition"
                                    >
                                </div>
                                <Button @click="openCreateLoc" label="Tambah Lokasi" icon="pi pi-plus" class="!bg-blue-600 !border-blue-600 !rounded-lg !px-4 !py-2.5 !text-sm !font-bold shadow-md shadow-blue-100 w-full sm:w-auto" />
                            </div>

                            <DataTable :value="filteredLocations" paginator :rows="10" stripedRows tableStyle="min-width: 50rem" class="p-datatable-sm">
                                <template #empty> 
                                    <div class="flex flex-col items-center justify-center py-12 text-gray-400">
                                        <i class="pi pi-map text-4xl mb-2 opacity-50"></i>
                                        <p>Data lokasi tidak ditemukan.</p>
                                    </div> 
                                </template>

                                <Column field="name" header="Nama Lokasi" sortable style="width: 30%">
                                    <template #body="slotProps">
                                        <span class="font-bold text-gray-800 text-sm">{{ slotProps.data.name }}</span>
                                    </template>
                                </Column>
                                <Column field="organization_unit.name" header="Unit Organisasi" sortable style="width: 25%">
                                    <template #body="slotProps">
                                        <span class="text-[10px] bg-blue-50 text-blue-600 px-2 py-1 rounded font-bold uppercase tracking-wider">
                                            {{ slotProps.data.organization_unit?.name || 'GLOBAL' }}
                                        </span>
                                    </template>
                                </Column>
                                <Column field="description" header="Deskripsi" style="width: 30%">
                                    <template #body="slotProps">
                                        <span class="text-gray-500 text-sm truncate block max-w-xs">{{ slotProps.data.description || '-' }}</span>
                                    </template>
                                </Column>
                                <Column header="Aksi" style="width: 15%; text-align: right">
                                    <template #body="slotProps">
                                        <div class="flex justify-end gap-2">
                                            <button @click="openEditLoc(slotProps.data)" class="w-8 h-8 rounded-full bg-yellow-50 text-yellow-600 hover:bg-yellow-100 flex items-center justify-center transition">
                                                <i class="pi pi-pencil text-xs"></i>
                                            </button>
                                            <button @click="deleteLoc(slotProps.data.id)" class="w-8 h-8 rounded-full bg-red-50 text-red-600 hover:bg-red-100 flex items-center justify-center transition">
                                                <i class="pi pi-trash text-xs"></i>
                                            </button>
                                        </div>
                                    </template>
                                </Column>
                            </DataTable>
                        </div>
                    </TabPanel>

                </TabView>
            </div>

            <Dialog v-model:visible="showUnitDialog" modal :header="isEditUnit ? 'Edit Satuan' : 'Satuan Baru'" :style="{ width: '400px' }" class="p-fluid">
                <div class="space-y-5 pt-3">
                    <div class="space-y-1">
                        <label class="text-sm font-semibold text-gray-700">Kode Satuan</label>
                        <InputText v-model="formUnit.code" placeholder="Contoh: PCS" class="!rounded-lg uppercase" :class="{'p-invalid': formUnit.errors.code}" />
                        <small class="text-gray-400 text-xs">Singkatan unik, maksimal 10 karakter.</small>
                        <small class="text-red-500 block" v-if="formUnit.errors.code">{{ formUnit.errors.code }}</small>
                    </div>
                    <div class="space-y-1">
                        <label class="text-sm font-semibold text-gray-700">Nama Satuan</label>
                        <InputText v-model="formUnit.name" placeholder="Contoh: Pieces" class="!rounded-lg" :class="{'p-invalid': formUnit.errors.name}" />
                        <small class="text-red-500" v-if="formUnit.errors.name">{{ formUnit.errors.name }}</small>
                    </div>
                </div>
                <template #footer>
                    <div class="flex justify-end gap-2 pt-4">
                        <Button label="Batal" icon="pi pi-times" text class="!text-gray-500" @click="showUnitDialog = false" />
                        <Button label="Simpan Data" icon="pi pi-check" class="!bg-blue-600 !border-blue-600 !rounded-lg" @click="submitUnit" :loading="formUnit.processing" />
                    </div>
                </template>
            </Dialog>

            <Dialog v-model:visible="showLocDialog" modal :header="isEditLoc ? 'Edit Lokasi' : 'Lokasi Baru'" :style="{ width: '500px' }" class="p-fluid">
                <div class="space-y-5 pt-3">
                    <div class="bg-blue-50 p-3 rounded-lg border border-blue-100 flex items-start gap-3">
                        <i class="pi pi-info-circle text-blue-500 mt-0.5"></i>
                        <p class="text-xs text-blue-700">Lokasi ini akan terikat dengan Unit Organisasi Anda saat ini.</p>
                    </div>

                    <div class="space-y-1">
                        <label class="text-sm font-semibold text-gray-700">Nama Ruangan / Lokasi</label>
                        <InputText v-model="formLoc.name" placeholder="Contoh: Gudang Belakang" class="!rounded-lg" :class="{'p-invalid': formLoc.errors.name}" />
                        <small class="text-red-500" v-if="formLoc.errors.name">{{ formLoc.errors.name }}</small>
                    </div>
                    <div class="space-y-1">
                        <label class="text-sm font-semibold text-gray-700">Deskripsi</label>
                        <Textarea v-model="formLoc.description" rows="3" placeholder="Keterangan detail lokasi..." class="!rounded-lg" />
                    </div>
                </div>
                <template #footer>
                    <div class="flex justify-end gap-2 pt-4">
                        <Button label="Batal" icon="pi pi-times" text class="!text-gray-500" @click="showLocDialog = false" />
                        <Button label="Simpan Data" icon="pi pi-check" class="!bg-blue-600 !border-blue-600 !rounded-lg" @click="submitLoc" :loading="formLoc.processing" />
                    </div>
                </template>
            </Dialog>

        </div>
    </AppLayout>
</template>

<style scoped>
/* Styling TabView agar terlihat seperti Navigation Modern */
:deep(.p-tabview-nav) {
    background-color: transparent;
    border-bottom: 1px solid #f3f4f6;
    padding: 0 1.5rem;
}
:deep(.p-tabview-nav-link) {
    background: transparent !important;
    border: none !important;
    border-bottom: 2px solid transparent !important;
    color: #64748b !important; /* slate-500 */
    font-weight: 600;
    padding: 1.25rem 1.5rem !important;
    transition: all 0.2s;
}
:deep(.p-tabview-nav-link:hover) {
    color: #1e293b !important; /* slate-800 */
}
:deep(.p-highlight .p-tabview-nav-link) {
    color: #2563eb !important; /* blue-600 */
    border-bottom-color: #2563eb !important;
}
:deep(.p-tabview-panels) {
    padding: 0;
}
</style>