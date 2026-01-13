<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import AppLayout from '@/Layouts/AppLayout.vue';

// Components
import FileUpload from 'primevue/fileupload';
import Dropdown from 'primevue/dropdown';
import Button from 'primevue/button';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import ProgressBar from 'primevue/progressbar';
import Card from 'primevue/card';
import Toast from 'primevue/toast';
import { useToast } from "primevue/usetoast";

const props = defineProps({
    units: Array // Data unit untuk dropdown basis
});

const toast = useToast();
const step = ref(1); // 1: Upload, 2: Preview, 3: Process
const loading = ref(false);

// State Data
const selectedUnit = ref(null);
const fileData = ref(null); // File mentah
const previewRows = ref([]); // Data hasil parsing
const importStats = ref({ valid: 0, invalid: 0 });

// State Progress
const progress = ref(0);
const processedCount = ref(0);
const isComplete = ref(false);

// --- STEP 1 LOGIC ---
const onFileSelect = (event) => {
    fileData.value = event.files[0];
};

const goToPreview = async () => {
    if (!selectedUnit.value) return toast.add({ severity: 'error', summary: 'Wajib', detail: 'Pilih Organisasi Basis dulu!', life: 3000 });
    if (!fileData.value) return toast.add({ severity: 'error', summary: 'Wajib', detail: 'Upload file Excel dulu!', life: 3000 });

    loading.value = true;
    const formData = new FormData();
    formData.append('file', fileData.value);

    try {
        const response = await axios.post(route('members.import.parse'), formData);
        previewRows.value = response.data.preview;
        importStats.value = response.data.stats;
        step.value = 2; // Pindah ke Step 2
    } catch (error) {
        toast.add({ severity: 'error', summary: 'Gagal Parsing', detail: 'Format Excel tidak sesuai.', life: 3000 });
    } finally {
        loading.value = false;
    }
};

// --- STEP 2 LOGIC ---
const validRows = computed(() => previewRows.value.filter(r => r.status === 'VALID'));

// --- STEP 3 LOGIC (CHUNKING) ---
const startImport = async () => {
    step.value = 3;
    progress.value = 0;
    processedCount.value = 0;
    isComplete.value = false;

    // Bagi data menjadi chunk (misal per 50 baris)
    const chunkSize = 50;
    const totalData = validRows.value.length;
    const chunks = [];
    
    for (let i = 0; i < totalData; i += chunkSize) {
        chunks.push(validRows.value.slice(i, i + chunkSize));
    }

    // Proses per chunk secara sekuensial
    for (const chunk of chunks) {
        try {
            await axios.post(route('members.import.execute'), {
                rows: chunk,
                organization_unit_id: selectedUnit.value.id // KUNCI UTAMA: Binding Basis
            });
            
            // Update Progress
            processedCount.value += chunk.length;
            progress.value = Math.round((processedCount.value / totalData) * 100);
            
        } catch (error) {
            console.error('Chunk error', error);
            // Opsional: Handle partial failure
        }
    }

    isComplete.value = true;
    toast.add({ severity: 'success', summary: 'Selesai', detail: `${processedCount.value} data berhasil diimport!`, life: 5000 });
};
</script>

<template>
    <Head title="Import Wizard" />
    <AppLayout>
        <div class="min-h-screen bg-slate-50 flex flex-col items-center py-10 -m-6">
            
            <div class="text-center mb-10">
                <h1 class="text-4xl font-black text-slate-800 tracking-tight mb-2">Mass Import Wizard</h1>
                <p class="text-slate-500">Import data anggota massal dengan validasi cerdas.</p>
            </div>

            <div class="flex items-center gap-4 mb-8 w-full max-w-3xl justify-center">
                <div class="flex flex-col items-center gap-2">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold transition-all duration-500"
                         :class="step >= 1 ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-200 scale-110' : 'bg-gray-200 text-gray-400'">1</div>
                    <span class="text-xs font-bold uppercase tracking-wider" :class="step >= 1 ? 'text-emerald-600' : 'text-gray-400'">Upload</span>
                </div>
                <div class="w-20 h-1 bg-gray-200 rounded-full overflow-hidden">
                    <div class="h-full bg-emerald-500 transition-all duration-500" :style="{ width: step >= 2 ? '100%' : '0%' }"></div>
                </div>
                <div class="flex flex-col items-center gap-2">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold transition-all duration-500"
                         :class="step >= 2 ? 'bg-blue-600 text-white shadow-lg shadow-blue-200 scale-110' : 'bg-gray-200 text-gray-400'">2</div>
                    <span class="text-xs font-bold uppercase tracking-wider" :class="step >= 2 ? 'text-blue-600' : 'text-gray-400'">Validasi</span>
                </div>
                <div class="w-20 h-1 bg-gray-200 rounded-full overflow-hidden">
                    <div class="h-full bg-blue-500 transition-all duration-500" :style="{ width: step >= 3 ? '100%' : '0%' }"></div>
                </div>
                <div class="flex flex-col items-center gap-2">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold transition-all duration-500"
                         :class="step >= 3 ? 'bg-purple-600 text-white shadow-lg shadow-purple-200 scale-110' : 'bg-gray-200 text-gray-400'">3</div>
                    <span class="text-xs font-bold uppercase tracking-wider" :class="step >= 3 ? 'text-purple-600' : 'text-gray-400'">Proses</span>
                </div>
            </div>

            <div class="w-full max-w-5xl bg-white/80 backdrop-blur-xl border border-white/50 shadow-xl rounded-3xl p-8 transition-all duration-500">
                
                <div v-if="step === 1" class="space-y-8 animate-fade-in">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-6">
                            <div class="bg-blue-50 p-6 rounded-2xl border border-blue-100">
                                <h3 class="font-bold text-blue-800 mb-2 flex items-center gap-2">
                                    <i class="pi pi-building"></i> Target Organisasi (Basis)
                                </h3>
                                <p class="text-sm text-blue-600/80 mb-4 leading-relaxed">
                                    Semua data dalam Excel ini akan otomatis dimasukkan sebagai anggota unit yang Anda pilih di sini.
                                </p>
                                <Dropdown v-model="selectedUnit" :options="units" optionLabel="name" filter placeholder="Pilih PRM / Unit Basis" class="w-full" />
                            </div>

                            <div class="bg-gray-50 p-6 rounded-2xl border border-gray-100">
                                <h3 class="font-bold text-gray-800 mb-2">Template Excel</h3>
                                <p class="text-sm text-gray-500 mb-4">Gunakan format standar agar data terbaca.</p>
                                
                                <a :href="route('members.download_template')" class="block w-full">
                                    <Button label="Download Template Resmi" icon="pi pi-download" severity="secondary" outlined class="w-full" />
                                </a>
                            </div>
                        </div>

                        <div class="border-2 border-dashed border-gray-300 rounded-2xl p-8 flex flex-col items-center justify-center text-center hover:border-emerald-400 hover:bg-emerald-50/30 transition-all group">
                            <div class="w-20 h-20 bg-emerald-100 rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                                <i class="pi pi-cloud-upload text-3xl text-emerald-600"></i>
                            </div>
                            <h3 class="text-lg font-bold text-gray-700">Upload File Excel</h3>
                            <p class="text-sm text-gray-400 mb-6">Drag & drop atau klik tombol di bawah</p>
                            
                            <FileUpload mode="basic" name="file" accept=".xlsx, .xls" :maxFileSize="5000000" 
                                        @select="onFileSelect" chooseLabel="Pilih File" 
                                        class="!bg-emerald-600 !border-emerald-600" />
                            
                            <div v-if="fileData" class="mt-4 bg-white px-4 py-2 rounded-lg shadow-sm border border-gray-200 flex items-center gap-2 text-emerald-600 font-bold animate-pulse">
                                <i class="pi pi-file-excel"></i> {{ fileData.name }}
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end pt-4 border-t border-gray-100">
                        <Button label="Lanjut Preview" icon="pi pi-arrow-right" iconPos="right" 
                                class="!bg-gray-900 !border-gray-900 !px-6 !py-3 !rounded-xl !font-bold" 
                                @click="goToPreview" :loading="loading" />
                    </div>
                </div>

                <div v-if="step === 2" class="animate-fade-in">
                    <div class="flex justify-between items-center mb-6">
                        <div class="flex gap-4">
                            <div class="bg-emerald-50 px-4 py-2 rounded-lg border border-emerald-100 text-emerald-700 font-bold">
                                {{ importStats.valid }} Data Valid
                            </div>
                            <div class="bg-red-50 px-4 py-2 rounded-lg border border-red-100 text-red-700 font-bold">
                                {{ importStats.invalid }} Data Error
                            </div>
                        </div>
                        <div class="bg-blue-50 px-4 py-2 rounded-lg border border-blue-100 text-blue-700 text-sm font-medium">
                            Target Basis: <strong>{{ selectedUnit.name }}</strong>
                        </div>
                    </div>

                    <div class="border rounded-xl overflow-hidden mb-6">
                        <DataTable :value="previewRows" scrollable scrollHeight="400px" size="small" stripedRows>
                            <Column field="row_index" header="No" style="width:50px" class="font-bold text-gray-500"></Column>
                            <Column field="full_name" header="Nama"></Column>
                            <Column field="nbm" header="NBM"></Column>
                            <Column header="Status Validasi">
                                <template #body="{ data }">
                                    <Tag :value="data.status" :severity="data.status === 'VALID' ? 'success' : 'danger'" />
                                </template>
                            </Column>
                            <Column field="errors" header="Keterangan" class="text-red-500 italic text-xs"></Column>
                        </DataTable>
                    </div>

                    <div class="flex justify-between pt-4 border-t border-gray-100">
                        <Button label="Upload Ulang" icon="pi pi-arrow-left" text severity="secondary" @click="step = 1" />
                        <div class="text-right">
                            <p class="text-xs text-gray-400 mb-2">Hanya data VALID yang akan diimport.</p>
                            <Button :label="`Proses Import (${validRows.length} Data)`" icon="pi pi-check" 
                                    class="!bg-gray-900 !border-gray-900 !px-6 !py-3 !rounded-xl !font-bold" 
                                    @click="startImport" :disabled="validRows.length === 0" />
                        </div>
                    </div>
                </div>

                <div v-if="step === 3" class="text-center py-10 animate-fade-in">
                    <div v-if="!isComplete">
                        <div class="relative w-32 h-32 mx-auto mb-6">
                            <i class="pi pi-cog pi-spin text-6xl text-blue-500 absolute inset-0 flex items-center justify-center"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-800 mb-2">Sedang Memproses Data...</h3>
                        <p class="text-gray-500 mb-8">Mohon jangan tutup halaman ini.</p>
                        
                        <div class="max-w-md mx-auto">
                            <ProgressBar :value="progress" class="h-6 rounded-full" />
                            <p class="mt-2 text-sm font-bold text-blue-600">{{ processedCount }} / {{ validRows.length }} Data</p>
                        </div>
                    </div>

                    <div v-else>
                        <div class="w-24 h-24 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6 animate-bounce">
                            <i class="pi pi-check text-5xl text-green-600"></i>
                        </div>
                        <h3 class="text-3xl font-black text-gray-800 mb-2">Import Selesai!</h3>
                        <p class="text-gray-500 mb-8">Seluruh data valid telah berhasil dimasukkan ke basis data.</p>
                        <Link :href="route('members.index')">
                            <Button label="Lihat Data Anggota" icon="pi pi-users" class="!bg-emerald-600 !border-emerald-600 !px-8 !py-3 !rounded-xl !font-bold shadow-lg shadow-emerald-200" />
                        </Link>
                    </div>
                </div>

            </div>
        </div>
        <Toast />
    </AppLayout>
</template>

<style>
.animate-fade-in {
    animation: fadeIn 0.5s ease-out;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>