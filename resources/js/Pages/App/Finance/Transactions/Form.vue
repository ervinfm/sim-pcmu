<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

// PrimeVue V4
import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import Select from 'primevue/select';
import Button from 'primevue/button';
import DatePicker from 'primevue/datepicker';
import Textarea from 'primevue/textarea';
import FileUpload from 'primevue/fileupload';
import Message from 'primevue/message';

const props = defineProps({
    cashAccounts: Array,    // Akun Kas/Bank
    revenueAccounts: Array, // Akun Pendapatan
    expenseAccounts: Array, // Akun Beban
});

const form = useForm({
    type: 'INCOME', // Default
    date: new Date(),
    amount: null,
    cash_coa_id: null,
    category_coa_id: null,
    destination_coa_id: null, // Khusus Transfer
    description: '',
    proof: null
});

// UI State Management
const activeType = ref('INCOME');

// Ubah form type saat tab berubah & reset field terkait
const setType = (type) => {
    activeType.value = type;
    form.type = type;
    form.category_coa_id = null;
    form.destination_coa_id = null;
    form.description = '';
};

// Computed Label agar User Friendly
const formLabels = computed(() => {
    if (activeType.value === 'INCOME') {
        return {
            source: 'Simpan ke Akun (Debit)',
            category: 'Sumber Dana / Kategori (Kredit)',
            descPlaceholder: 'Contoh: Infaq Jumat, Donasi Hamba Allah...'
        };
    } else if (activeType.value === 'EXPENSE') {
        return {
            source: 'Bayar Menggunakan (Kredit)',
            category: 'Untuk Keperluan (Debit)',
            descPlaceholder: 'Contoh: Bayar Listrik, Honor Khatib...'
        };
    } else {
        return {
            source: 'Dari Akun Asal (Kredit)',
            destination: 'Ke Akun Tujuan (Debit)',
            descPlaceholder: 'Contoh: Setor Tunai ke Bank, Tarik Tunai...'
        };
    }
});

const onFileSelect = (event) => {
    form.proof = event.files[0];
};

const submit = () => {
    form.post(route('finance.transactions.store'), {
        forceFormData: true,
        preserveScroll: true
    });
};
</script>

<template>
    <Head title="Catat Transaksi" />

    <AppLayout>
        <div class="max-w-4xl mx-auto space-y-6 pb-20">
            
            <div class="flex items-center gap-3 mb-4">
                <Link :href="route('finance.transactions.index')">
                    <Button icon="pi pi-arrow-left" text rounded severity="secondary" />
                </Link>
                <div>
                    <h1 class="text-2xl font-black text-gray-800">Catat Transaksi Baru</h1>
                    <p class="text-sm text-gray-500">Pilih jenis transaksi dan lengkapi data keuangan.</p>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4 bg-white p-2 rounded-2xl border border-gray-200 shadow-sm">
                <button @click="setType('INCOME')" 
                    :class="['py-3 rounded-xl font-bold transition-all flex items-center justify-center gap-2', 
                    activeType === 'INCOME' ? 'bg-emerald-100 text-emerald-700 shadow-sm' : 'text-gray-500 hover:bg-gray-50']">
                    <i class="pi pi-arrow-down-left"></i> Pemasukan
                </button>
                <button @click="setType('EXPENSE')" 
                    :class="['py-3 rounded-xl font-bold transition-all flex items-center justify-center gap-2', 
                    activeType === 'EXPENSE' ? 'bg-red-100 text-red-700 shadow-sm' : 'text-gray-500 hover:bg-gray-50']">
                    <i class="pi pi-arrow-up-right"></i> Pengeluaran
                </button>
                <button @click="setType('TRANSFER')" 
                    :class="['py-3 rounded-xl font-bold transition-all flex items-center justify-center gap-2', 
                    activeType === 'TRANSFER' ? 'bg-blue-100 text-blue-700 shadow-sm' : 'text-gray-500 hover:bg-gray-50']">
                    <i class="pi pi-arrow-right-arrow-left"></i> Transfer
                </button>
            </div>

            <form @submit.prevent="submit" class="bg-white rounded-3xl border border-gray-100 shadow-lg overflow-hidden">
                
                <div :class="['h-2 w-full', 
                    activeType === 'INCOME' ? 'bg-emerald-500' : 
                    (activeType === 'EXPENSE' ? 'bg-red-500' : 'bg-blue-500')]">
                </div>

                <div class="p-8 space-y-8">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-2">
                            <label class="font-bold text-sm text-gray-600">Tanggal Transaksi</label>
                            <DatePicker v-model="form.date" showIcon dateFormat="dd/mm/yy" class="w-full !rounded-xl" />
                            <small class="text-red-500">{{ form.errors.date }}</small>
                        </div>
                        <div class="space-y-2">
                            <label class="font-bold text-sm text-gray-600">Nominal (Rp)</label>
                            <InputNumber v-model="form.amount" mode="currency" currency="IDR" locale="id-ID" 
                                         placeholder="0" class="w-full !rounded-xl" inputClass="!font-black !text-lg" />
                            <small class="text-red-500">{{ form.errors.amount }}</small>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 p-6 bg-gray-50 rounded-2xl border border-gray-100">
                        
                        <div class="space-y-2">
                            <label class="font-bold text-sm text-gray-700 flex items-center gap-2">
                                <i class="pi pi-wallet"></i> {{ formLabels.source }}
                            </label>
                            <Select v-model="form.cash_coa_id" :options="cashAccounts" optionLabel="name" optionValue="id" 
                                    filter placeholder="Pilih Kas/Bank..." class="w-full !rounded-xl" />
                            <small class="text-red-500">{{ form.errors.cash_coa_id }}</small>
                        </div>

                        <div class="space-y-2">
                            <template v-if="activeType === 'TRANSFER'">
                                <label class="font-bold text-sm text-gray-700 flex items-center gap-2">
                                    <i class="pi pi-arrow-right"></i> {{ formLabels.destination }}
                                </label>
                                <Select v-model="form.destination_coa_id" :options="cashAccounts" optionLabel="name" optionValue="id" 
                                        filter placeholder="Pilih Akun Tujuan..." class="w-full !rounded-xl" />
                                <small class="text-red-500">{{ form.errors.destination_coa_id }}</small>
                            </template>

                            <template v-else>
                                <label class="font-bold text-sm text-gray-700 flex items-center gap-2">
                                    <i class="pi pi-tag"></i> {{ formLabels.category }}
                                </label>
                                <Select v-model="form.category_coa_id" 
                                        :options="activeType === 'INCOME' ? revenueAccounts : expenseAccounts" 
                                        optionLabel="name" optionValue="id" 
                                        filter placeholder="Pilih Kategori..." class="w-full !rounded-xl" />
                                <small class="text-red-500">{{ form.errors.category_coa_id }}</small>
                            </template>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="space-y-2">
                            <label class="font-bold text-sm text-gray-600">Keterangan / Uraian</label>
                            <Textarea v-model="form.description" rows="2" class="w-full !rounded-xl" :placeholder="formLabels.descPlaceholder" />
                            <small class="text-red-500">{{ form.errors.description }}</small>
                        </div>

                        <div class="space-y-2">
                            <label class="font-bold text-sm text-gray-600">Upload Bukti / Nota (Opsional)</label>
                            <div class="flex items-center gap-4">
                                <FileUpload mode="basic" name="proof" accept="image/*" :maxFileSize="2000000" 
                                            @select="onFileSelect" chooseLabel="Pilih Foto Bukti" auto customUpload 
                                            class="!bg-white !text-gray-700 !border-gray-300 !rounded-xl" />
                                <span v-if="form.proof" class="text-sm text-emerald-600 font-bold"><i class="pi pi-check"></i> File terpilih</span>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-gray-100 flex justify-end">
                        <Button label="Simpan Transaksi" icon="pi pi-save" type="submit" 
                                class="!bg-gray-900 !border-gray-900 !rounded-xl !px-8 !py-3 font-bold shadow-xl shadow-gray-300 hover:scale-105 transition-transform" 
                                :loading="form.processing" />
                    </div>

                </div>
            </form>
        </div>
    </AppLayout>
</template>