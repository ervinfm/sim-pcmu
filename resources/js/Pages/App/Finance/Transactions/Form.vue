<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

// PrimeVue Components
import InputNumber from 'primevue/inputnumber';
import Select from 'primevue/select';
import Button from 'primevue/button';
import DatePicker from 'primevue/datepicker';
import Textarea from 'primevue/textarea';
import FileUpload from 'primevue/fileupload';
import SelectButton from 'primevue/selectbutton';
import Card from 'primevue/card'; // (Opsional jika ingin dipakai, tapi di template ini kita pakai div class)

const props = defineProps({
    transaction: Object, // Jika ada isi = Mode Edit
    cashAccounts: Array,
    revenueAccounts: Array,
    expenseAccounts: Array,
});

// Deteksi Mode Edit
const isEditing = computed(() => !!props.transaction);

// DATA OPSI LOKAL
const fundTypes = [
    { label: 'Dana Bebas / Operasional', value: 'UNRESTRICTED' },
    { label: 'Dana Terikat (Zakat/Donasi Khusus)', value: 'RESTRICTED' },
    { label: 'Dana Abadi / Wakaf', value: 'ENDOWMENT' }
];

const typeOptions = [
    { label: 'Pemasukan', value: 'INCOME', icon: 'pi pi-arrow-down-left' },
    { label: 'Pengeluaran', value: 'EXPENSE', icon: 'pi pi-arrow-up-right' },
    { label: 'Transfer', value: 'TRANSFER', icon: 'pi pi-arrow-right-arrow-left' }
];

// Inisialisasi Form
const form = useForm({
    _method: isEditing.value ? 'PUT' : 'POST', 
    type: props.transaction?.type || 'INCOME',
    date: props.transaction?.date ? new Date(props.transaction.date) : new Date(),
    amount: props.transaction?.amount ? Number(props.transaction.amount) : null,
    
    cash_coa_id: props.transaction?.cash_coa_id || null,
    category_coa_id: (props.transaction?.type !== 'TRANSFER') ? props.transaction?.category_coa_id : null,
    destination_coa_id: (props.transaction?.type === 'TRANSFER') ? props.transaction?.category_coa_id : null,
    
    fund_type: props.transaction?.fund_type || 'UNRESTRICTED',
    description: props.transaction?.description || '',
    proof: null
});

// Computed untuk Tema Warna UI berdasarkan Tipe Transaksi
const theme = computed(() => {
    switch (form.type) {
        case 'INCOME':
            return {
                bg: 'bg-emerald-50',
                border: 'border-emerald-200',
                text: 'text-emerald-700',
                lightText: 'text-emerald-600',
                iconBg: 'bg-emerald-100',
                button: 'success', // PrimeVue severity
                ring: 'focus:ring-emerald-500'
            };
        case 'EXPENSE':
            return {
                bg: 'bg-rose-50',
                border: 'border-rose-200',
                text: 'text-rose-700',
                lightText: 'text-rose-600',
                iconBg: 'bg-rose-100',
                button: 'danger',
                ring: 'focus:ring-rose-500'
            };
        case 'TRANSFER':
            return {
                bg: 'bg-blue-50',
                border: 'border-blue-200',
                text: 'text-blue-700',
                lightText: 'text-blue-600',
                iconBg: 'bg-blue-100',
                button: 'info',
                ring: 'focus:ring-blue-500'
            };
        default:
            return { bg: 'bg-gray-50', border: 'border-gray-200', text: 'text-gray-700', button: 'secondary' };
    }
});

// Handle Submit
const submit = () => {
    if (isEditing.value) {
        form.post(route('finance.transactions.update', props.transaction.id), {
            preserveScroll: true,
        });
    } else {
        form.post(route('finance.transactions.store'), {
            preserveScroll: true,
            onSuccess: () => form.reset(),
        });
    }
};

const onFileSelect = (event) => {
    form.proof = event.files[0];
};
</script>

<template>
    <Head :title="isEditing ? 'Edit Transaksi' : 'Transaksi Baru'" />

    <AppLayout>
        <div class="mb-6">
            <div class="flex items-center gap-4">
                <Link :href="route('finance.transactions.index')">
                    <Button icon="pi pi-arrow-left" text rounded severity="secondary" v-tooltip="'Kembali ke Daftar'" />
                </Link>
                <div>
                    <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                        {{ isEditing ? 'Edit Data Transaksi' : 'Catat Transaksi Baru' }}
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">
                        {{ isEditing ? 'Perbarui data transaksi yang sudah tercatat.' : 'Pastikan data yang diinput valid dan memiliki bukti fisik.' }}
                    </p>
                </div>
            </div>
        </div>

        <div class="pb-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <form @submit.prevent="submit">
                    <div class="relative shadow-sm rounded-2xl border overflow-hidden transition-all duration-300 group mb-3" 
                        :class="[theme.bg, theme.border]">
                        
                        <div class="absolute top-0 right-0 -mt-4 -mr-4 w-32 h-32 rounded-full opacity-10 pointer-events-none" 
                            :class="theme.text === 'text-gray-700' ? 'bg-gray-400' : theme.text.replace('text-', 'bg-')">
                        </div>

                        <div class="relative p-5 md:p-6 flex flex-col md:flex-row justify-between items-center gap-6">
                            
                            <div class="flex items-center gap-5 w-full md:w-auto">
                                <div class="relative flex-shrink-0">
                                    <div class="w-16 h-16 rounded-2xl bg-white border shadow-sm flex items-center justify-center text-2xl transition-transform group-hover:scale-105"
                                        :class="[theme.text, theme.border]">
                                        <i v-if="form.type === 'INCOME'" class="pi pi-arrow-down-left"></i>
                                        <i v-else-if="form.type === 'EXPENSE'" class="pi pi-arrow-up-right"></i>
                                        <i v-else class="pi pi-arrow-right-arrow-left"></i>
                                    </div>
                                    <div class="absolute -bottom-1 -right-1 w-5 h-5 bg-white rounded-full flex items-center justify-center shadow-sm">
                                        <div class="w-3 h-3 rounded-full animate-pulse" 
                                            :class="theme.text === 'text-gray-700' ? 'bg-gray-400' : theme.text.replace('text-', 'bg-')"></div>
                                    </div>
                                </div>

                                <div>
                                    <span class="block text-xs font-bold uppercase tracking-widest opacity-60 mb-1" :class="theme.text">
                                        Mode Transaksi
                                    </span>
                                    <h3 class="text-xl md:text-2xl font-extrabold tracking-tight" :class="theme.text">
                                        {{ form.type === 'INCOME' ? 'Pemasukan Dana' : (form.type === 'EXPENSE' ? 'Pengeluaran Dana' : 'Transfer Akun') }}
                                    </h3>
                                    <p class="text-xs font-medium opacity-80 mt-0.5" :class="theme.text">
                                        {{ form.type === 'INCOME' ? 'Mencatat uang masuk ke kas' : (form.type === 'EXPENSE' ? 'Mencatat beban biaya operasional' : 'Memindahkan dana antar kas') }}
                                    </p>
                                </div>
                            </div>

                            <div class="w-full md:w-auto bg-white/60 p-1.5 rounded-xl border border-white/50 backdrop-blur-sm shadow-sm">
                                <SelectButton v-model="form.type" :options="typeOptions" optionLabel="label" optionValue="value"
                                            :allowEmpty="false" class="w-full md:w-auto font-medium">
                                    <template #option="slotProps">
                                        <div class="flex items-center gap-2 px-4 py-2">
                                            <i :class="slotProps.option.icon" class="text-sm"></i>
                                            <span class="text-sm font-bold">{{ slotProps.option.label }}</span>
                                        </div>
                                    </template>
                                </SelectButton>
                            </div>

                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                        <div class="lg:col-span-8 bg-white shadow-sm rounded-xl border border-gray-100 p-6 md:p-8 space-y-6">
                            
                            <h3 class="font-bold text-gray-800 text-lg border-b pb-2 mb-4">Rincian Transaksi</h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="flex flex-col gap-2">
                                    <label class="font-semibold text-gray-600 text-sm flex items-center gap-2">
                                        <i class="pi pi-calendar text-gray-400"></i> Tanggal
                                    </label>
                                    <DatePicker v-model="form.date" dateFormat="dd/mm/yy" showIcon fluid showButtonBar 
                                                class="w-full" :class="{'p-invalid': form.errors.date}" />
                                    <small v-if="form.errors.date" class="text-red-500">{{ form.errors.date }}</small>
                                </div>

                                <div class="flex flex-col gap-2">
                                    <label class="font-semibold text-gray-600 text-sm flex items-center gap-2">
                                        <i class="pi pi-tag text-gray-400"></i> Klasifikasi Dana
                                    </label>
                                    <Select v-model="form.fund_type" :options="fundTypes" optionLabel="label" optionValue="value" 
                                            placeholder="Pilih Klasifikasi" class="w-full" 
                                            :class="{'p-invalid': form.errors.fund_type}" />
                                    <small v-if="form.errors.fund_type" class="text-red-500">{{ form.errors.fund_type }}</small>
                                </div>
                            </div>

                            <div class="h-px bg-gray-100 my-2"></div>

                            <div class="flex flex-col gap-2">
                                <label class="font-semibold text-gray-600 text-sm flex items-center gap-2">
                                    <i class="pi pi-wallet text-gray-400"></i>
                                    {{ form.type === 'INCOME' ? 'Masuk ke Kas/Bank (Debet)' : 'Diambil dari Kas/Bank (Kredit)' }}
                                </label>
                                <Select v-model="form.cash_coa_id" :options="cashAccounts" optionLabel="name" optionValue="id" 
                                        filter placeholder="Pilih Akun Kas" class="w-full" 
                                        :class="{'p-invalid': form.errors.cash_coa_id}">
                                        <template #option="slotProps">
                                        <div class="flex flex-col py-1">
                                            <span class="font-bold text-gray-800">{{ slotProps.option.name }}</span>
                                            <span class="text-xs text-gray-500">Kode: {{ slotProps.option.code }}</span>
                                        </div>
                                    </template>
                                    <template #value="slotProps">
                                        <div v-if="slotProps.value" class="flex items-center">
                                            <span>{{ cashAccounts.find(x => x.id === slotProps.value)?.name }}</span>
                                        </div>
                                        <span v-else class="text-gray-400">{{ slotProps.placeholder }}</span>
                                    </template>   
                                </Select>
                                <small v-if="form.errors.cash_coa_id" class="text-red-500">{{ form.errors.cash_coa_id }}</small>
                            </div>

                            <div v-if="form.type !== 'TRANSFER'" class="flex flex-col gap-2">
                                <label class="font-semibold text-gray-600 text-sm flex items-center gap-2">
                                    <i class="pi pi-list text-gray-400"></i>
                                    {{ form.type === 'INCOME' ? 'Sumber Pendapatan (Kredit)' : 'Keperluan / Beban (Debet)' }}
                                </label>
                                <Select v-model="form.category_coa_id" 
                                        :options="form.type === 'INCOME' ? revenueAccounts : expenseAccounts" 
                                        optionLabel="name" optionValue="id" 
                                        filter placeholder="Pilih Kategori Akun" class="w-full"
                                        :class="{'p-invalid': form.errors.category_coa_id}">
                                    <template #option="slotProps">
                                        <div class="flex flex-col py-1">
                                            <span class="font-bold text-gray-800">{{ slotProps.option.name }}</span>
                                            <span class="text-xs text-gray-500">Kode: {{ slotProps.option.code }}</span>
                                        </div>
                                    </template>
                                    <template #value="slotProps">
                                        <div v-if="slotProps.value" class="flex items-center">
                                            <span>{{ (form.type === 'INCOME' ? revenueAccounts : expenseAccounts).find(x => x.id === slotProps.value)?.name }}</span>
                                        </div>
                                        <span v-else class="text-gray-400">{{ slotProps.placeholder }}</span>
                                    </template>
                                </Select>
                                <small v-if="form.errors.category_coa_id" class="text-red-500">{{ form.errors.category_coa_id }}</small>
                            </div>

                            <div v-else class="flex flex-col gap-2">
                                <label class="font-semibold text-gray-600 text-sm flex items-center gap-2">
                                    <i class="pi pi-send text-gray-400"></i> Transfer Ke (Tujuan)
                                </label>
                                <Select v-model="form.destination_coa_id" :options="cashAccounts" optionLabel="name" optionValue="id" 
                                        filter placeholder="Pilih Bank Tujuan" class="w-full"
                                        :class="{'p-invalid': form.errors.destination_coa_id}" />
                                <small v-if="form.errors.destination_coa_id" class="text-red-500">{{ form.errors.destination_coa_id }}</small>
                            </div>

                            <div class="flex flex-col gap-2">
                                <label class="font-semibold text-gray-600 text-sm flex items-center gap-2">
                                    <i class="pi pi-align-left text-gray-400"></i> Keterangan / Uraian
                                </label>
                                <Textarea v-model="form.description" rows="4" placeholder="Tuliskan keterangan detail transaksi..." 
                                          class="w-full" :class="{'p-invalid': form.errors.description}" />
                                <small v-if="form.errors.description" class="text-red-500">{{ form.errors.description }}</small>
                            </div>

                        </div>

                        <div class="lg:col-span-4 flex flex-col gap-6">
                            
                            <div class="bg-white shadow-lg rounded-xl overflow-hidden border" :class="theme.border">
                                <div class="p-6 text-center" :class="theme.bg">
                                    <label class="block font-bold text-gray-500 text-xs uppercase tracking-widest mb-2">Nominal (IDR)</label>
                                    <InputNumber v-model="form.amount" inputId="currency-id" mode="currency" currency="IDR" locale="id-ID" 
                                                 class="w-full p-inputtext-lg text-center" 
                                                 :class="[{'p-invalid': form.errors.amount}]" 
                                                 placeholder="Rp 0" 
                                                 :min="0" />
                                     <small v-if="form.errors.amount" class="text-red-500 block mt-2 font-bold">{{ form.errors.amount }}</small>
                                </div>
                                <div class="px-6 py-3 bg-gray-50 border-t border-gray-100">
                                    <p class="text-xs text-gray-500 text-center">
                                        Pastikan nominal sesuai dengan bukti fisik.
                                    </p>
                                </div>
                            </div>

                            <div class="bg-white shadow-sm rounded-xl border border-gray-100 p-6">
                                <label class="font-semibold text-gray-700 mb-3 block flex items-center gap-2">
                                    <i class="pi pi-camera text-gray-400"></i> Bukti Transaksi
                                </label>
                                
                                <div class="border-2 border-dashed rounded-xl p-6 flex flex-col items-center justify-center text-center transition-all hover:bg-gray-50"
                                     :class="[form.proof ? 'border-emerald-400 bg-emerald-50' : 'border-gray-300']">
                                    
                                    <div v-if="!form.proof" class="mb-3">
                                        <i class="pi pi-cloud-upload text-4xl text-gray-300"></i>
                                    </div>

                                    <FileUpload mode="basic" name="proof" accept="image/*" :maxFileSize="3000000" 
                                                @select="onFileSelect" chooseLabel="Pilih File" auto customUpload 
                                                class="p-button-outlined p-button-sm mb-2" 
                                                :class="{'p-button-success': form.proof, 'p-button-secondary': !form.proof}" />
                                    
                                    <span v-if="form.proof" class="text-sm text-emerald-600 font-bold mt-2 break-all">
                                        <i class="pi pi-check-circle"></i> {{ form.proof.name }}
                                    </span>
                                    <span v-else class="text-xs text-gray-400 mt-1">
                                        JPG, PNG (Max 3MB)
                                    </span>
                                </div>
                                
                                <div v-if="isEditing && props.transaction.proof_path && !form.proof" class="mt-4 p-3 border rounded-lg bg-blue-50 border-blue-100 flex items-center justify-between">
                                    <div class="flex items-center gap-2 overflow-hidden">
                                        <div class="w-8 h-8 rounded bg-blue-200 flex items-center justify-center text-blue-600 flex-shrink-0">
                                            <i class="pi pi-image"></i>
                                        </div>
                                        <span class="text-sm font-medium text-gray-700 truncate">Bukti Tersimpan</span>
                                    </div>
                                    <a :href="'/storage/' + props.transaction.proof_path" target="_blank" class="text-xs font-bold text-blue-600 hover:text-blue-800 flex items-center gap-1 bg-white px-2 py-1 rounded shadow-sm border border-blue-100">
                                        Lihat <i class="pi pi-external-link"></i>
                                    </a>
                                </div>
                            </div>

                            <div class="flex flex-col gap-3 mt-2">
                                <Button type="submit" 
                                        :label="isEditing ? 'Simpan Perubahan' : 'Simpan Transaksi'" 
                                        icon="pi pi-check" 
                                        :severity="theme.button" 
                                        :loading="form.processing" 
                                        raised 
                                        class="w-full py-3 text-lg" />
                                
                                <Link :href="route('finance.transactions.index')" class="w-full">
                                    <Button label="Batal" icon="pi pi-times" text severity="secondary" class="w-full" />
                                </Link>
                            </div>

                        </div>
                    </div>
                </form>

            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Custom overrides jika diperlukan untuk input number agar teks lebih besar di 'hero' input */
:deep(.p-inputnumber-input) {
    font-weight: 700;
}
</style>