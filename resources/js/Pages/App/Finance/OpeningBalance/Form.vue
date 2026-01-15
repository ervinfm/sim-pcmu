<script setup>
import { useForm, Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputNumber from 'primevue/inputnumber';
import DatePicker from 'primevue/datepicker';
import Button from 'primevue/button';
import Message from 'primevue/message';

const props = defineProps({
    cashAccounts: Array, // Dari controller
    equityAccounts: Array
});

const form = useForm({
    date: new Date(),
    equity_coa_id: props.equityAccounts[0]?.id, // Default ke akun Modal/Saldo Awal pertama
    items: props.cashAccounts.map(acc => ({
        cash_coa_id: acc.id,
        name: acc.name,
        code: acc.code,
        amount: 0
    }))
});

const submit = () => {
    form.post(route('finance.opening-balances.store'));
};

const totalSaldo = () => {
    return form.items.reduce((sum, item) => sum + (item.amount || 0), 0);
};
</script>

<template>
    <Head title="Input Saldo Awal" />

    <AppLayout>
        <div class="p-4 md:p-6 max-w-4xl mx-auto">
            
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                    <div>
                        <h1 class="text-xl font-black text-gray-800">Form Saldo Awal</h1>
                        <p class="text-sm text-gray-500">Masukkan nilai riil uang yang ada saat ini.</p>
                    </div>
                    <Link :href="route('finance.opening-balances.index')" class="text-sm font-bold text-gray-500 hover:text-gray-800">
                        Batal
                    </Link>
                </div>

                <form @submit.prevent="submit" class="p-6 space-y-8">
                    
                    <Message severity="info" class="mb-4">
                        Sistem otomatis akan mencatat: <b>Debit Kas</b> dan <b>Kredit Modal/Saldo Awal</b>.
                    </Message>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="font-bold text-sm text-gray-700">Tanggal Cut-Off</label>
                            <DatePicker v-model="form.date" showIcon fluid dateFormat="dd/mm/yy" />
                            <small class="text-gray-400">Biasanya tanggal 1 bulan ini atau awal tahun.</small>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <h3 class="font-bold text-gray-800 border-b pb-2">Rincian Kas & Bank</h3>
                        
                        <div v-for="(item, index) in form.items" :key="item.cash_coa_id" 
                             class="flex flex-col md:flex-row md:items-center justify-between gap-4 p-4 rounded-xl border border-gray-100 hover:border-emerald-200 hover:bg-emerald-50/30 transition bg-white">
                            
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center font-bold text-xs">
                                    {{ item.code.split('-')[1] }}
                                </div>
                                <div>
                                    <p class="font-bold text-gray-800">{{ item.name }}</p>
                                    <p class="text-xs text-gray-400">Kode: {{ item.code }}</p>
                                </div>
                            </div>

                            <div class="w-full md:w-64">
                                <InputNumber v-model="item.amount" mode="currency" currency="IDR" locale="id-ID" 
                                             placeholder="Rp 0" class="w-full" :class="{'p-invalid': form.errors[`items.${index}.amount`]}" />
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-900 text-white p-6 rounded-xl flex justify-between items-center">
                        <span class="text-gray-400 font-medium">Total Saldo Awal</span>
                        <span class="text-2xl font-black">Rp {{ totalSaldo().toLocaleString('id-ID') }}</span>
                    </div>

                    <div class="flex justify-end pt-4">
                        <Button type="submit" label="Simpan Saldo Awal" icon="pi pi-check" 
                                class="!bg-emerald-600 !border-emerald-600 !px-8 !py-3 !rounded-xl font-bold hover:!bg-emerald-700" 
                                :loading="form.processing" />
                    </div>

                </form>
            </div>
        </div>
    </AppLayout>
</template>