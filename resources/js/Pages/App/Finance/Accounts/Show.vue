<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import Badge from 'primevue/badge';

const props = defineProps({
    account: Object,
    mutations: Object,
    balance: Number,
    isDebitNormal: Boolean
});

// Helper Formatter
const formatCurrency = (value) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value);
const formatDate = (date) => new Date(date).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
const formatTime = (date) => new Date(date).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });

</script>

<template>
    <Head :title="'Mutasi - ' + account.name" />

    <AppLayout>
        <div class="max-w-6xl mx-auto space-y-6 pb-20">
            
            <div class="flex items-center gap-3">
                <Link :href="route('transactions.index')"> <Button icon="pi pi-arrow-left" text rounded severity="secondary" />
                </Link>
                <div>
                    <h1 class="text-xl font-black text-gray-800 tracking-tight">Rincian Akun</h1>
                    <p class="text-xs text-gray-500">Lihat riwayat transaksi dan mutasi saldo.</p>
                </div>
            </div>

            <div class="bg-gradient-to-r from-slate-900 to-slate-800 rounded-3xl p-8 text-white shadow-xl relative overflow-hidden">
                <div class="absolute top-0 right-0 -mr-10 -mt-10 h-64 w-64 rounded-full bg-white/5 blur-3xl"></div>
                
                <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                    <div>
                        <div class="flex items-center gap-3 mb-2">
                            <Badge :value="account.code" class="!bg-white/20 !text-white font-mono" />
                            <span class="text-xs font-bold uppercase tracking-widest text-slate-400">{{ account.type }}</span>
                        </div>
                        <h2 class="text-3xl font-black mb-1">{{ account.name }}</h2>
                        <p class="text-sm text-slate-400 opacity-80">
                            {{ account.is_cash ? 'Akun Kas & Setara Kas' : 'Akun Non-Kas' }}
                        </p>
                    </div>

                    <div class="text-left md:text-right">
                        <p class="text-xs font-bold uppercase tracking-widest text-slate-400 mb-1">Saldo Saat Ini</p>
                        <h3 class="text-4xl font-mono font-bold">{{ formatCurrency(balance) }}</h3>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="font-bold text-gray-800">Riwayat Mutasi</h3>
                    </div>

                <DataTable :value="mutations.data" stripedRows class="text-sm">
                    <template #empty>
                        <div class="text-center p-10 text-gray-400">Belum ada transaksi pada akun ini.</div>
                    </template>

                    <Column header="Waktu Transaksi" style="width: 20%">
                        <template #body="{ data }">
                            <div class="font-bold text-gray-700">{{ formatDate(data.journal.transaction_date) }}</div>
                            <div class="text-xs text-gray-400">{{ formatTime(data.created_at) }}</div>
                        </template>
                    </Column>

                    <Column header="Keterangan / Referensi" style="width: 40%">
                        <template #body="{ data }">
                            <div class="font-bold text-gray-800 text-base mb-1">{{ data.journal.description }}</div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded font-mono">
                                    #{{ data.journal.reference_no }}
                                </span>
                            </div>
                        </template>
                    </Column>

                    <Column header="Mutasi" style="width: 20%; text-align: right">
                        <template #body="{ data }">
                            <div class="flex flex-col items-end gap-1">
                                <span v-if="Number(data.debit) > 0" :class="isDebitNormal ? 'text-emerald-600 font-bold' : 'text-red-500 font-bold'">
                                    {{ isDebitNormal ? '+' : '-' }} {{ formatCurrency(data.debit) }}
                                    <span class="text-[10px] text-gray-400 font-normal uppercase ml-1">(Debit)</span>
                                </span>

                                <span v-if="Number(data.credit) > 0" :class="!isDebitNormal ? 'text-emerald-600 font-bold' : 'text-red-500 font-bold'">
                                    {{ !isDebitNormal ? '+' : '-' }} {{ formatCurrency(data.credit) }}
                                    <span class="text-[10px] text-gray-400 font-normal uppercase ml-1">(Kredit)</span>
                                </span>
                            </div>
                        </template>
                    </Column>
                </DataTable>

                <div class="p-4 border-t border-gray-100 flex justify-center gap-2" v-if="mutations.links.length > 3">
                    <Link v-for="(link, k) in mutations.links" :key="k" 
                          :href="link.url || '#'" 
                          v-html="link.label"
                          :class="['px-3 py-1 rounded text-xs font-bold transition', 
                                   link.active ? 'bg-slate-900 text-white' : 'bg-white text-slate-600 hover:bg-slate-50',
                                   !link.url ? 'opacity-50 cursor-not-allowed' : '']" />
                </div>
            </div>

        </div>
    </AppLayout>
</template>