<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

// PrimeVue V4 Components
import Select from 'primevue/select'; 
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import DatePicker from 'primevue/datepicker'; 
import Button from 'primevue/button';
import Tag from 'primevue/tag';

const props = defineProps({
    available_assets: Array, 
    members: Array, 
});

const form = useForm({
    asset_id: null,
    borrower_type: 'INTERNAL', 
    member_id: null,
    borrower_name: '',
    borrower_contact: '',
    loan_date: new Date(),
    return_date_plan: null,
    description: ''
});

// Helper Previews
const selectedAsset = computed(() => props.available_assets.find(a => a.id === form.asset_id));
const selectedMember = computed(() => {
    if (form.borrower_type === 'INTERNAL') {
        return props.members.find(m => m.id === form.member_id) || {};
    }
    return { nama: form.borrower_name, contact: form.borrower_contact };
});

const submit = () => {
    form.post(route('assets.loans.store'), { preserveScroll: true });
};
</script>

<template>
    <Head title="Buat Peminjaman" />

    <AppLayout>
        <div class="max-w-7xl mx-auto pb-12 space-y-6">
            
            <div class="flex items-center justify-between border-b border-gray-200 pb-4">
                <h1 class="text-2xl font-bold text-gray-900">Form Peminjaman</h1>
                <Link :href="route('assets.loans.index')" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-xl text-sm font-bold hover:bg-gray-50">Batal</Link>
            </div>

            <form @submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Pilih Aset <span class="text-red-500">*</span></label>
                        <Select v-model="form.asset_id" :options="available_assets" optionLabel="name" optionValue="id" filter filterFields="['name', 'code']" placeholder="Cari aset..." class="w-full !rounded-xl" :class="{'p-invalid': form.errors.asset_id}">
                            <template #option="slotProps">
                                <div class="flex items-center gap-3">
                                    <img v-if="slotProps.option.image_url" :src="`/storage/${slotProps.option.image_url}`" class="w-8 h-8 rounded bg-gray-100 object-cover">
                                    <div v-else class="w-8 h-8 rounded bg-gray-100 flex items-center justify-center text-gray-400"><i class="pi pi-image text-xs"></i></div>
                                    <div>
                                        <div class="font-bold text-sm">{{ slotProps.option.name }}</div>
                                        <div class="text-[10px] text-gray-500">{{ slotProps.option.code }}</div>
                                    </div>
                                </div>
                            </template>
                            <template #value="slotProps">
                                <span v-if="slotProps.value">{{ available_assets.find(a => a.id === slotProps.value)?.name }}</span>
                                <span v-else class="text-gray-400">{{ slotProps.placeholder }}</span>
                            </template>
                        </Select>
                        <small class="text-red-500" v-if="form.errors.asset_id">{{ form.errors.asset_id }}</small>
                    </div>

                    <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm space-y-4">
                        <div class="flex bg-gray-100 p-1 rounded-lg w-fit">
                            <button type="button" @click="form.borrower_type = 'INTERNAL'" class="px-4 py-1.5 rounded-md text-sm font-bold transition-all" :class="form.borrower_type === 'INTERNAL' ? 'bg-white shadow text-blue-600' : 'text-gray-500'">Member</button>
                            <button type="button" @click="form.borrower_type = 'EXTERNAL'" class="px-4 py-1.5 rounded-md text-sm font-bold transition-all" :class="form.borrower_type === 'EXTERNAL' ? 'bg-white shadow text-purple-600' : 'text-gray-500'">Eksternal</button>
                        </div>

                        <div v-if="form.borrower_type === 'INTERNAL'">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Pilih Anggota <span class="text-red-500">*</span></label>
                            <Select v-model="form.member_id" :options="members" optionLabel="nama" optionValue="id" filter filterFields="['nama', 'nbm']" placeholder="Cari anggota..." class="w-full !rounded-xl" :class="{'p-invalid': form.errors.member_id}">
                                <template #option="slotProps">
                                    <div>
                                        <div class="font-bold text-sm">{{ slotProps.option.nama }}</div>
                                        <div class="text-xs text-gray-500">NBM: {{ slotProps.option.nbm }}</div>
                                    </div>
                                </template>
                                <template #value="slotProps">
                                    <span v-if="slotProps.value">{{ members.find(m => m.id === slotProps.value)?.nama }}</span>
                                    <span v-else class="text-gray-400">{{ slotProps.placeholder }}</span>
                                </template>
                            </Select>
                            <small class="text-red-500" v-if="form.errors.member_id">{{ form.errors.member_id }}</small>
                        </div>

                        <div v-else class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Nama <span class="text-red-500">*</span></label>
                                <InputText v-model="form.borrower_name" class="w-full !rounded-xl" :class="{'p-invalid': form.errors.borrower_name}" />
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Kontak</label>
                                <InputText v-model="form.borrower_contact" class="w-full !rounded-xl" :class="{'p-invalid': form.errors.borrower_contact}" />
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Tgl Pinjam</label>
                            <DatePicker v-model="form.loan_date" showIcon dateFormat="dd/mm/yy" class="w-full" />
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Rencana Kembali</label>
                            <DatePicker v-model="form.return_date_plan" :minDate="form.loan_date" showIcon dateFormat="dd/mm/yy" class="w-full" :class="{'p-invalid': form.errors.return_date_plan}" />
                        </div>
                        <div class="col-span-2">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Keperluan</label>
                            <Textarea v-model="form.description" rows="2" class="w-full !rounded-xl" />
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-1">
                    <div class="bg-gray-900 text-white rounded-xl p-6 shadow-xl sticky top-6">
                        <h3 class="font-bold text-lg mb-4">Ringkasan</h3>
                        <div class="space-y-4 text-sm opacity-90">
                            <div>
                                <p class="text-xs uppercase font-bold text-gray-400">Aset</p>
                                <p class="font-bold text-lg">{{ selectedAsset?.name || '-' }}</p>
                                <p class="text-xs">{{ selectedAsset?.code }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase font-bold text-gray-400">Peminjam</p>
                                <p class="font-bold text-lg">{{ form.borrower_type === 'INTERNAL' ? selectedMember.nama : form.borrower_name || '-' }}</p>
                            </div>
                        </div>
                        <Button type="submit" label="Proses" class="w-full mt-6 !bg-blue-600 !border-none !rounded-xl !font-bold" :loading="form.processing" />
                    </div>
                </div>

            </form>
        </div>
    </AppLayout>
</template>