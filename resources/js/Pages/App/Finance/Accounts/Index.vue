<script setup>
import { ref, computed, watch } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import axios from 'axios';
import AppLayout from '@/Layouts/AppLayout.vue';
import FinanceNavigation from '@/Components/Finance/FinanceNavigation.vue';

// --- PRIME VUE IMPORTS ---
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import Select from 'primevue/select';
import Dialog from 'primevue/dialog';
import Checkbox from 'primevue/checkbox';
import ConfirmPopup from 'primevue/confirmpopup';
import Badge from 'primevue/badge';
import Paginator from 'primevue/paginator';
import Sidebar from 'primevue/sidebar'; // [BARU] Untuk Wadah Tree View
import Tree from 'primevue/tree';       // [BARU] Komponen Tree
import { useConfirm } from "primevue/useconfirm";
import { useToast } from "primevue/usetoast";

const props = defineProps({
    accounts: Array,       
    parentOptions: Array,
});

const confirm = useConfirm();
const toast = useToast();

// --- STATE ---
const searchQuery = ref('');
const treeSearchQuery = ref(''); // Search khusus untuk Tree View
const selectedTypeFilter = ref('ALL');
const expandedCards = ref([]); 
const currentPage = ref(0);
const rowsPerPage = ref(9); 
const showTreeSidebar = ref(false); // [BARU] State visibility Sidebar Tree

const filterTypes = [
    { label: 'Semua Tipe', value: 'ALL', icon: 'pi pi-th-large' },
    { label: 'Aset (Harta)', value: 'ASSET', icon: 'pi pi-briefcase' },
    { label: 'Kewajiban (Utang)', value: 'LIABILITY', icon: 'pi pi-file' },
    { label: 'Ekuitas (Modal)', value: 'EQUITY', icon: 'pi pi-chart-pie' },
    { label: 'Pendapatan', value: 'REVENUE', icon: 'pi pi-wallet' },
    { label: 'Beban (Biaya)', value: 'EXPENSE', icon: 'pi pi-shopping-cart' },
];

const accountTypes = filterTypes.filter(t => t.value !== 'ALL');

// --- HIERARCHY BUILDER (CARD VIEW) ---
const buildTree = (data) => {
    const map = {};
    const roots = [];
    // Deep clone agar tidak memutasi props asli
    const dataCopy = data.map(i => ({...i, children: []}));
    
    dataCopy.forEach(item => { map[item.id] = item; });
    dataCopy.forEach(item => {
        if (item.parent_id && map[item.parent_id]) {
            map[item.parent_id].children.push(map[item.id]);
        } else {
            roots.push(map[item.id]);
        }
    });
    return roots;
};

// --- HIERARCHY BUILDER (PRIME VUE TREE COMPONENT) ---
// [BARU] Mengubah data flat menjadi format Node yang dibutuhkan PrimeVue Tree
const treeNodes = computed(() => {
    const map = {};
    const roots = [];
    
    // Format: { key, label, data, children }
    const nodes = props.accounts.map(acc => ({
        key: String(acc.id),
        label: acc.name,
        data: acc, // Simpan data asli untuk aksi edit/delete
        children: [],
        type: 'default'
    }));

    nodes.forEach(node => { map[node.key] = node; });
    nodes.forEach(node => {
        if (node.data.parent_id && map[node.data.parent_id]) {
            map[node.data.parent_id].children.push(node);
        } else {
            roots.push(node);
        }
    });

    return roots;
});


// --- FILTERING (CARD VIEW) ---
const displayedData = computed(() => {
    let data = props.accounts;
    if (selectedTypeFilter.value !== 'ALL') data = data.filter(acc => acc.type === selectedTypeFilter.value);
    
    if (searchQuery.value) {
        const lowerSearch = searchQuery.value.toLowerCase();
        return data.filter(acc => acc.name.toLowerCase().includes(lowerSearch) || acc.code.toLowerCase().includes(lowerSearch));
    }
    return buildTree(data);
});

const paginatedItems = computed(() => {
    const start = currentPage.value * rowsPerPage.value;
    const end = start + rowsPerPage.value;
    return displayedData.value.slice(start, end);
});

const onPageChange = (event) => { currentPage.value = event.page; };

const toggleExpand = (id) => {
    if (expandedCards.value.includes(id)) {
        expandedCards.value = expandedCards.value.filter(itemId => itemId !== id);
    } else {
        expandedCards.value.push(id);
    }
};

// --- MODAL STATE ---
const modalVisible = ref(false);
const isEdit = ref(false);
const isGeneratingCode = ref(false);

const form = useForm({
    id: null, name: '', code: '', type: 'ASSET', parent_id: null, is_cash: false
});

// --- CODE GENERATOR LOGIC ---
watch(() => form.parent_id, async (newParentId) => {
    if (!isEdit.value || (isEdit.value && form.parent_id !== props.accounts.find(a => a.id === form.id)?.parent_id)) {
        await generateCodeManual(newParentId);
    }
});

const generateCodeManual = async (parentId) => {
    isGeneratingCode.value = true;
    form.code = ''; 
    try {
        const response = await axios.get(route('finance.accounts.generate-code'), { params: { parent_id: parentId } });
        if (response.data.code) {
            form.code = response.data.code;
        } else {
            form.code = ''; 
        }
    } catch (e) {
        console.error("Auto code failed", e);
        const msg = e.response?.data?.message || 'Gagal koneksi ke server.';
        toast.add({ severity: 'error', summary: 'Gagal Generate Kode', detail: msg, life: 4000 });
    } finally { 
        isGeneratingCode.value = false; 
    }
};

// --- ACTIONS ---
const openCreateModal = () => {
    isEdit.value = false;
    form.reset();
    form.type = (selectedTypeFilter.value !== 'ALL') ? selectedTypeFilter.value : 'ASSET';
    form.parent_id = null;
    modalVisible.value = true;
    generateCodeManual(null); 
};

// [BARU] Helper untuk membuka modal create dengan parent yang sudah dipilih (dari Tree View)
const openCreateChildModal = (parentId) => {
    isEdit.value = false;
    form.reset();
    // Cari data parent untuk mengambil Tipe-nya (agar anak otomatis mewarisi tipe bapaknya)
    const parentAccount = props.accounts.find(a => a.id === parentId);
    form.type = parentAccount ? parentAccount.type : 'ASSET';
    
    form.parent_id = parentId;
    modalVisible.value = true;
    // Watcher akan otomatis men-trigger generateCodeManual
};

const openEditModal = (account) => {
    isEdit.value = true;
    form.id = account.id;
    form.name = account.name;
    form.code = account.code;
    form.type = account.type;
    form.parent_id = account.parent_id;
    form.is_cash = Boolean(account.is_cash);
    modalVisible.value = true;
};

const submitForm = () => {
    const successHandler = () => {
        modalVisible.value = false;
        toast.add({ severity: 'success', summary: 'Berhasil', detail: isEdit.value ? 'Akun diperbarui' : 'Akun dibuat', life: 3000 });
    };
    const errorHandler = () => {
        toast.add({ severity: 'error', summary: 'Gagal', detail: 'Periksa inputan berwarna merah.', life: 3000 });
    };

    if (isEdit.value) {
        form.put(route('finance.accounts.update', form.id), { onSuccess: successHandler, onError: errorHandler });
    } else {
        form.post(route('finance.accounts.store'), { onSuccess: successHandler, onError: errorHandler });
    }
};

const handleDelete = (event, id) => {
    confirm.require({
        target: event.currentTarget,
        message: 'Hapus akun ini beserta sub-akunnya?',
        icon: 'pi pi-exclamation-triangle',
        acceptClass: 'p-button-danger',
        accept: () => {
            router.delete(route('finance.accounts.destroy', id), {
                onSuccess: () => toast.add({ severity: 'info', summary: 'Dihapus', detail: 'Akun berhasil dihapus', life: 3000 }),
                onError: () => toast.add({ severity: 'error', summary: 'Gagal', detail: 'Akun sedang digunakan.', life: 3000 })
            });
        }
    });
};

// --- UI HELPERS ---
const formatCodeDisplay = (code) => {
    if (!code) return '';
    if (!code.includes('.')) return `${code}.00.00.000`;
    return code;
};

const getTheme = (type) => {
    const themes = {
        'ASSET': { card: 'bg-gradient-to-br from-white to-emerald-50 border-emerald-100', iconBg: 'text-emerald-500/10', badge: 'bg-emerald-100 text-emerald-700', mainIcon: 'pi pi-briefcase', text: 'text-emerald-800' },
        'LIABILITY': { card: 'bg-gradient-to-br from-white to-orange-50 border-orange-100', iconBg: 'text-orange-500/10', badge: 'bg-orange-100 text-orange-700', mainIcon: 'pi pi-file', text: 'text-orange-800' },
        'EQUITY': { card: 'bg-gradient-to-br from-white to-blue-50 border-blue-100', iconBg: 'text-blue-500/10', badge: 'bg-blue-100 text-blue-700', mainIcon: 'pi pi-chart-pie', text: 'text-blue-800' },
        'REVENUE': { card: 'bg-gradient-to-br from-white to-cyan-50 border-cyan-100', iconBg: 'text-cyan-500/10', badge: 'bg-cyan-100 text-cyan-700', mainIcon: 'pi pi-wallet', text: 'text-cyan-800' },
        'EXPENSE': { card: 'bg-gradient-to-br from-white to-rose-50 border-rose-100', iconBg: 'text-rose-500/10', badge: 'bg-rose-100 text-rose-700', mainIcon: 'pi pi-shopping-cart', text: 'text-rose-800' },
    };
    return themes[type] || themes['ASSET'];
};

const getTypeLabel = (type) => accountTypes.find(t => t.value === type)?.label || type;
const filteredParentOptions = computed(() => !isEdit.value ? props.parentOptions : props.parentOptions.filter(p => p.id !== form.id));
</script>

<template>
    <Head title="Master Akun" />

    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Master Akun (COA)</h2>
        </template>

        <div class="py-4 pb-20">
            <div class="max-w-7xl mx-auto sm:px-4 lg:px-6 space-y-6">
                
                <FinanceNavigation />

                <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex flex-col md:flex-row gap-4 items-center justify-between sticky top-4 z-20">
                    <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto flex-1">
                        <div class="w-full sm:w-64">
                            <IconField iconPosition="left">
                                <InputIcon class="pi pi-search" />
                                <InputText v-model="searchQuery" placeholder="Cari Akun..." class="w-full rounded-lg" />
                            </IconField>
                        </div>
                        <div class="w-full sm:w-56">
                            <Select v-model="selectedTypeFilter" :options="filterTypes" optionLabel="label" optionValue="value" class="w-full rounded-lg" placeholder="Filter Tipe">
                                <template #value="slotProps">
                                    <div v-if="slotProps.value" class="flex items-center gap-2">
                                        <i :class="filterTypes.find(t => t.value === slotProps.value)?.icon" class="text-slate-500"></i>
                                        <span>{{ filterTypes.find(t => t.value === slotProps.value)?.label }}</span>
                                    </div>
                                    <span v-else>{{ slotProps.placeholder }}</span>
                                </template>
                            </Select>
                        </div>
                    </div>
                    
                    <div class="w-full md:w-auto flex justify-end gap-2">
                        <Button label="Struktur Pohon" icon="pi pi-sitemap" severity="secondary" outlined rounded @click="showTreeSidebar = true" />
                        
                        <Button label="Buat Akun Baru" icon="pi pi-plus" @click="openCreateModal" raised rounded class="font-bold shadow-emerald-100 w-full md:w-auto" />
                    </div>
                </div>

                <div v-if="paginatedItems.length > 0">
                    
                    <div v-if="searchQuery" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div v-for="account in paginatedItems" :key="account.id" class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm hover:shadow-md transition-all relative overflow-hidden">
                             <i :class="getTheme(account.type).mainIcon" class="absolute -right-4 -bottom-4 text-6xl opacity-5 pointer-events-none"></i>
                             
                             <div class="flex justify-between items-start mb-2 relative z-10">
                                <span class="font-mono font-bold text-slate-700 bg-slate-100 px-2 py-0.5 rounded text-xs border border-slate-200">{{ account.code }}</span>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full uppercase" :class="getTheme(account.type).badge">{{ getTypeLabel(account.type) }}</span>
                            </div>
                            <h3 class="font-bold text-gray-800 text-lg mb-1 relative z-10">{{ account.name }}</h3>
                            <div class="flex justify-between items-center mt-3 pt-3 border-t border-gray-100 relative z-10">
                                <span v-if="account.parent" class="text-xs text-gray-400 flex items-center gap-1"><i class="pi pi-arrow-return-right"></i> {{ account.parent.name }}</span>
                                <Button icon="pi pi-pencil" text rounded size="small" @click="openEditModal(account)" />
                            </div>
                        </div>
                    </div>

                    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                        <div v-for="node in paginatedItems" :key="node.id" 
                             class="group relative flex flex-col rounded-2xl shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden border"
                             :class="getTheme(node.type).card">
                            
                            <i :class="[getTheme(node.type).mainIcon, getTheme(node.type).iconBg]" 
                               class="absolute -right-6 -top-6 text-[8rem] opacity-100 pointer-events-none transition-transform group-hover:scale-110"></i>

                            <div class="p-6 flex-1 relative z-10">
                                <div class="flex justify-between items-start mb-4">
                                    <div class="flex flex-col">
                                        <span class="font-mono font-black text-2xl tracking-tight opacity-90" :class="getTheme(node.type).text">
                                            {{ formatCodeDisplay(node.code) }}
                                        </span>
                                        <span class="text-[10px] uppercase font-bold tracking-wider mt-1 px-2 py-0.5 rounded-md w-fit bg-white/60 backdrop-blur-sm border border-white/50 shadow-sm" :class="getTheme(node.type).text">
                                            {{ getTypeLabel(node.type) }}
                                        </span>
                                    </div>
                                    
                                    <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity bg-white/80 rounded-full p-1 shadow-sm backdrop-blur-sm">
                                        <Button icon="pi pi-pencil" text rounded severity="secondary" @click.stop="openEditModal(node)" class="!w-8 !h-8" />
                                        <Button icon="pi pi-trash" text rounded severity="danger" @click.stop="handleDelete($event, node.id)" class="!w-8 !h-8" />
                                    </div>
                                </div>

                                <h3 class="text-xl font-bold text-gray-800 leading-tight mb-2">{{ node.name }}</h3>
                                <p class="text-xs text-gray-500 line-clamp-2" v-if="node.is_cash">
                                    <i class="pi pi-wallet mr-1 text-emerald-500"></i> Akun Kas / Bank
                                </p>
                            </div>

                            <div class="bg-white/50 border-t border-gray-100/50 backdrop-blur-sm relative z-10">
                                <button @click="toggleExpand(node.id)" class="w-full flex justify-between items-center px-6 py-3 text-sm font-bold text-slate-600 hover:bg-white/80 transition-colors">
                                    <span class="flex items-center gap-2">
                                        <Badge :value="node.children.length" size="small" :severity="node.children.length > 0 ? 'info' : 'secondary'"></Badge>
                                        Sub-Akun
                                    </span>
                                    <i class="pi transition-transform duration-300" :class="expandedCards.includes(node.id) ? 'pi-chevron-up' : 'pi-chevron-down'"></i>
                                </button>
                                
                                <div v-if="expandedCards.includes(node.id)" class="px-5 pb-4 space-y-2 border-t border-gray-100 pt-3 bg-white/80 animate-fade-in-down">
                                    
                                    <div v-for="child in node.children" :key="child.id" class="flex items-center justify-between p-2.5 rounded-xl bg-white border border-gray-200 shadow-sm hover:border-blue-400 hover:shadow-md transition-all group/child">
                                        <div class="flex flex-col">
                                            <span class="text-[10px] font-mono font-bold text-slate-400 group-hover/child:text-blue-500 transition-colors">{{ child.code }}</span>
                                            <span class="text-sm font-bold text-gray-700">{{ child.name }}</span>
                                        </div>
                                        <div class="flex gap-1 opacity-0 group-hover/child:opacity-100 transition-opacity">
                                            <Button icon="pi pi-pencil" text rounded size="small" severity="info" class="!w-7 !h-7" @click="openEditModal(child)" />
                                            <Button icon="pi pi-trash" text rounded size="small" severity="danger" class="!w-7 !h-7" @click="handleDelete($event, child.id)" />
                                        </div>
                                    </div>
                                    
                                    <div v-if="node.children.length === 0" class="text-xs text-gray-400 italic text-center py-2">Belum ada rincian.</div>
                                    
                                    <button @click="openCreateChildModal(node.id)" class="w-full py-2.5 mt-2 text-xs font-bold text-slate-600 border border-dashed border-slate-300 rounded-xl hover:bg-emerald-50 hover:text-emerald-600 hover:border-emerald-300 transition-all flex items-center justify-center gap-2 group/btn">
                                        <i class="pi pi-plus-circle group-hover/btn:scale-110 transition-transform"></i> Tambah Sub-Akun
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-center">
                         <Paginator :rows="rowsPerPage" :totalRecords="displayedData.length" @page="onPageChange" :first="currentPage * rowsPerPage"></Paginator>
                    </div>
                </div>

                <div v-else class="flex flex-col items-center justify-center py-20 bg-white rounded-2xl border border-dashed border-gray-300">
                    <div class="bg-gray-50 p-4 rounded-full mb-3">
                        <i class="pi pi-folder-open text-4xl text-gray-400"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-700">Data Akun Kosong</h3>
                    <p class="text-gray-500 text-sm mb-4">Belum ada akun yang sesuai dengan filter Anda.</p>
                    <Button label="Buat Akun Baru" icon="pi pi-plus" @click="openCreateModal" />
                </div>

            </div>
        </div>

        <Sidebar v-model:visible="showTreeSidebar" position="right" class="!w-full md:!w-[450px]" header="Struktur Akun (Tree View)">
            <div class="flex flex-col h-full">

                <div class="flex-1 overflow-y-auto pr-2">
                    <Tree :value="treeNodes" filter :filterValue="treeSearchQuery" selectionMode="single" class="w-full !border-0 !p-0">
                        <template #default="slotProps">
                            <div class="flex items-center justify-between w-full group py-1">
                                <div class="flex flex-col">
                                    <span class="font-mono text-xs font-bold text-slate-500">{{ slotProps.node.data.code }}</span>
                                    <span class="text-sm font-semibold text-gray-800">{{ slotProps.node.label }}</span>
                                </div>
                                
                                <div class="hidden group-hover:flex gap-1 ml-2">
                                    <Button icon="pi pi-plus" text rounded size="small" severity="success" class="!w-6 !h-6" v-tooltip.top="'Tambah Anak'" @click.stop="openCreateChildModal(slotProps.node.data.id)" />
                                    <Button icon="pi pi-pencil" text rounded size="small" severity="info" class="!w-6 !h-6" v-tooltip.top="'Edit'" @click.stop="openEditModal(slotProps.node.data)" />
                                    <Button icon="pi pi-trash" text rounded size="small" severity="danger" class="!w-6 !h-6" v-tooltip.top="'Hapus'" @click.stop="handleDelete($event, slotProps.node.data.id)" />
                                </div>
                            </div>
                        </template>
                    </Tree>
                </div>
                
                <div class="mt-4 pt-4 border-t border-gray-100 text-xs text-center text-gray-400">
                    Klik ikon (+) untuk menambah sub-akun secara cepat.
                </div>
            </div>
        </Sidebar>

        <Dialog v-model:visible="modalVisible" modal :header="isEdit ? 'Edit Akun' : 'Buat Akun Baru'" :style="{ width: '500px' }" class="p-fluid">
            <div class="flex flex-col gap-5 pt-2">
                
                <div class="flex flex-col gap-2">
                    <label class="font-bold text-xs text-slate-500 uppercase">Induk Akun</label>
                    <Select v-model="form.parent_id" :options="filteredParentOptions" optionLabel="name" optionValue="id" filter placeholder="-- Akun Utama (Root) --" showClear class="w-full" :disabled="isGeneratingCode">
                        <template #option="slotProps">
                            <div class="flex flex-col py-1">
                                <span class="font-bold text-xs text-gray-500">{{ slotProps.option.code }}</span>
                                <span class="text-sm font-medium">{{ slotProps.option.name }}</span>
                            </div>
                        </template>
                        <template #value="slotProps">
                            <div v-if="slotProps.value" class="flex items-center text-sm">
                                <span class="font-bold mr-2 text-indigo-600 bg-indigo-50 px-1.5 rounded text-xs">{{ parentOptions.find(p => p.id === slotProps.value)?.code }}</span>
                                {{ parentOptions.find(p => p.id === slotProps.value)?.name }}
                            </div>
                            <span v-else>{{ slotProps.placeholder }}</span>
                        </template>
                    </Select>
                    <small class="text-[10px] text-gray-400" v-if="!form.parent_id">Kosongkan untuk membuat Akun Induk (Level 1).</small>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-1 flex flex-col gap-2">
                        <label class="font-bold text-xs text-slate-500 uppercase">Kode <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <InputText v-model="form.code" 
                                       placeholder="Otomatis..." 
                                       class="font-mono font-bold w-full bg-gray-100 cursor-not-allowed opacity-80" 
                                       :class="{'p-invalid': form.errors.code}" 
                                       readonly />
                            <i v-if="isGeneratingCode" class="pi pi-spin pi-spinner absolute right-3 top-3 text-emerald-500"></i>
                            <i v-else class="pi pi-lock absolute right-3 top-3 text-gray-400 text-xs"></i>
                        </div>
                        <small class="text-red-500 text-xs font-bold" v-if="form.errors.code">{{ form.errors.code }}</small>
                    </div>

                    <div class="col-span-1 flex flex-col gap-2">
                        <label class="font-bold text-xs text-slate-500 uppercase">Tipe <span class="text-red-500">*</span></label>
                        <Select v-model="form.type" :options="accountTypes" optionLabel="label" optionValue="value" :class="{'p-invalid': form.errors.type}" class="w-full" />
                    </div>
                </div>

                <div class="flex flex-col gap-2">
                    <label class="font-bold text-xs text-slate-500 uppercase">Nama Akun <span class="text-red-500">*</span></label>
                    <InputText v-model="form.name" placeholder="Contoh: Kas Operasional" :class="{'p-invalid': form.errors.name}" />
                    <small class="text-red-500 text-xs font-bold" v-if="form.errors.name">{{ form.errors.name }}</small>
                </div>

                <div class="bg-amber-50 p-4 rounded-xl border border-amber-200 flex items-start gap-3">
                    <div class="pt-1"><Checkbox v-model="form.is_cash" :binary="true" inputId="is_cash_modal" /></div>
                    <label for="is_cash_modal" class="text-sm text-amber-900 cursor-pointer select-none">
                        <div class="font-bold mb-0.5">Akun Kas / Bank?</div>
                        <div class="text-xs opacity-80">Centang jika akun ini adalah dompet/rekening uang.</div>
                    </label>
                </div>
            </div>

            <template #footer>
                <div class="flex justify-end gap-2 pt-4">
                    <Button label="Batal" icon="pi pi-times" text severity="secondary" @click="modalVisible = false" />
                    <Button label="Simpan" icon="pi pi-check" @click="submitForm" 
                            :loading="form.processing || isGeneratingCode" 
                            :disabled="!form.code || isGeneratingCode" 
                            raised />
                </div>
            </template>
        </Dialog>

        <ConfirmPopup />
    </AppLayout>
</template>

<style scoped>
@keyframes fadeInDown {
    from { opacity: 0; transform: translateY(-5px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fade-in-down {
    animation: fadeInDown 0.2s ease-out forwards;
}
</style>