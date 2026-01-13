<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

// Components
import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import Dropdown from 'primevue/dropdown';
import Calendar from 'primevue/calendar';
import Textarea from 'primevue/textarea';
import Button from 'primevue/button';
import Card from 'primevue/card';

const props = defineProps({
    asset: Object,
    units: Array, // Data Unit Organisasi
});

const isEdit = computed(() => !!props.asset);

const form = useForm({
    organization_unit_id: props.asset?.organization_unit_id || null,
    inventory_code: props.asset?.inventory_code || '',
    name: props.asset?.name || '',
    category: props.asset?.category || null,
    acquisition_date: props.asset?.acquisition_date ? new Date(props.asset.acquisition_date) : new Date(),
    acquisition_value: props.asset?.acquisition_value ? Number(props.asset.acquisition_value) : 0,
    current_value: props.asset?.current_value ? Number(props.asset.current_value) : null,
    condition: props.asset?.condition || 'BAIK',
    location: props.asset?.location || '',
    description: props.asset?.description || '',
});

// Master Data (Hardcoded sesuai Migrasi)
const categories = [
    'TANAH', 'BANGUNAN', 'KENDARAAN', 'ELEKTRONIK', 'PERABOT', 'PERALATAN_KANTOR', 'LAINNYA'
];

const conditions = [
    { label: 'Baik', value: 'BAIK' },
    { label: 'Rusak Ringan', value: 'RUSAK_RINGAN' },
    { label: 'Rusak Berat', value: 'RUSAK_BERAT' },
    { label: 'Hilang', value: 'HILANG' },
    { label: 'Pemusnahan', value: 'PEMUSNAHAN' }
];

const submit = () => {
    if (isEdit.value) {
        form.put(route('assets.update', props.asset.id));
    } else {
        form.post(route('assets.store'));
    }
};
</script>

<template>
    <Head :title="isEdit ? 'Edit Aset' : 'Registrasi Aset'" />

    <AppLayout>
        <div class="max-w-4xl mx-auto">
            <div class="mb-6">
                <Link :href="route('assets.index')" class="text-sm text-gray-500 hover:text-emerald-600 mb-2 inline-block">
                    <i class="pi pi-arrow-left text-xs"></i> Kembali
                </Link>
                <h1 class="text-2xl font-bold text-gray-800">{{ isEdit ? 'Edit Data Aset' : 'Registrasi Aset Baru' }}</h1>
            </div>

            <form @submit.prevent="submit">
                <Card class="border border-gray-100 shadow-sm">
                    <template #title>Detail Aset</template>
                    <template #content>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            
                            <div class="flex flex-col gap-2">
                                <label class="font-semibold text-gray-700">Unit Pemilik</label>
                                <Dropdown v-model="form.organization_unit_id" :options="units" optionLabel="name" optionValue="id" 
                                          placeholder="Pilih Unit" filter class="w-full" :class="{ 'p-invalid': form.errors.organization_unit_id }" />
                                <small class="text-red-500">{{ form.errors.organization_unit_id }}</small>
                            </div>

                            <div class="flex flex-col gap-2">
                                <label class="font-semibold text-gray-700">Kode Inventaris</label>
                                <InputText v-model="form.inventory_code" placeholder="Contoh: INV/2026/001" class="w-full" />
                                <small class="text-gray-500 text-xs">Harus unik. Gunakan format standar organisasi.</small>
                                <small class="text-red-500 block">{{ form.errors.inventory_code }}</small>
                            </div>

                            <div class="flex flex-col gap-2 md:col-span-2">
                                <label class="font-semibold text-gray-700">Nama Barang</label>
                                <InputText v-model="form.name" class="w-full" placeholder="Contoh: Laptop Asus ROG" />
                                <small class="text-red-500">{{ form.errors.name }}</small>
                            </div>

                            <div class="flex flex-col gap-2">
                                <label class="font-semibold text-gray-700">Kategori</label>
                                <Dropdown v-model="form.category" :options="categories" placeholder="Pilih Kategori" class="w-full" />
                                <small class="text-red-500">{{ form.errors.category }}</small>
                            </div>

                            <div class="flex flex-col gap-2">
                                <label class="font-semibold text-gray-700">Lokasi Fisik</label>
                                <InputText v-model="form.location" placeholder="Contoh: Gudang Lt. 1 / Ruang Ketua" class="w-full" />
                            </div>

                            <div class="flex flex-col gap-2">
                                <label class="font-semibold text-gray-700">Tanggal Perolehan</label>
                                <Calendar v-model="form.acquisition_date" dateFormat="dd/mm/yy" showIcon class="w-full" />
                            </div>

                            <div class="flex flex-col gap-2">
                                <label class="font-semibold text-gray-700">Nilai Perolehan (Rp)</label>
                                <InputNumber v-model="form.acquisition_value" mode="currency" currency="IDR" locale="id-ID" class="w-full" />
                            </div>

                            <div class="flex flex-col gap-2">
                                <label class="font-semibold text-gray-700">Nilai Taksiran Sekarang (Rp)</label>
                                <InputNumber v-model="form.current_value" mode="currency" currency="IDR" locale="id-ID" class="w-full" />
                                <small class="text-gray-500">Kosongkan jika sama dengan nilai beli.</small>
                            </div>

                            <div class="flex flex-col gap-2">
                                <label class="font-semibold text-gray-700">Kondisi Barang</label>
                                <Dropdown v-model="form.condition" :options="conditions" optionLabel="label" optionValue="value" class="w-full" />
                            </div>

                            <div class="flex flex-col gap-2 md:col-span-2">
                                <label class="font-semibold text-gray-700">Keterangan Tambahan</label>
                                <Textarea v-model="form.description" rows="3" class="w-full" />
                            </div>

                        </div>

                        <div class="flex justify-end gap-3 mt-8">
                            <Link :href="route('assets.index')">
                                <Button label="Batal" severity="secondary" text />
                            </Link>
                            <Button label="Simpan Data" type="submit" icon="pi pi-check" class="!bg-emerald-600 !border-emerald-600" :loading="form.processing" />
                        </div>
                    </template>
                </Card>
            </form>
        </div>
    </AppLayout>
</template>