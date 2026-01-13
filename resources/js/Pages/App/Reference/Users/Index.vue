<script setup>
import { ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { FilterMatchMode } from '@primevue/core/api';

import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import Tag from 'primevue/tag';
import Dialog from 'primevue/dialog';
import Dropdown from 'primevue/dropdown';
import InputSwitch from 'primevue/inputswitch'; // Toggle Switch
import ConfirmPopup from 'primevue/confirmpopup';
import { useConfirm } from "primevue/useconfirm";
import { useToast } from "primevue/usetoast";

const props = defineProps({
    users: Array,
    availableMembers: Array
});

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
});

const confirm = useConfirm();
const toast = useToast();

// Logic Toggle Status
const toggleStatus = (event, user) => {
    confirm.require({
        target: event.currentTarget,
        message: user.is_active ? `Nonaktifkan user ${user.name}?` : `Aktifkan user ${user.name}?`,
        icon: user.is_active ? 'pi pi-ban' : 'pi pi-check-circle',
        acceptClass: user.is_active ? 'p-button-danger' : 'p-button-success',
        accept: () => {
            router.patch(route('users.toggle_status', user.id), {}, {
                onSuccess: () => toast.add({ severity: 'success', summary: 'Sukses', detail: 'Status user diperbarui.', life: 3000 })
            });
        }
    });
};

// ... (Logic Link Member & Delete tetap sama) ...
const linkDialog = ref(false);
const selectedUser = ref(null);
const linkForm = useForm({ member_id: null });

const openLinkModal = (user) => { selectedUser.value = user; linkForm.reset(); linkDialog.value = true; };
const submitLink = () => { if (!linkForm.member_id) return; linkForm.post(route('users.link', selectedUser.value.id), { onSuccess: () => { linkDialog.value = false; } }); };
const confirmUnlink = (event, user) => { confirm.require({ target: event.currentTarget, message: `Lepas tautan user ${user.name}?`, icon: 'pi pi-unlink', acceptClass: 'p-button-danger', accept: () => router.post(route('users.unlink', user.id)) }); };
const handleDelete = (event, id) => { confirm.require({ target: event.currentTarget, message: 'Hapus user ini permanent?', icon: 'pi pi-trash', acceptClass: 'p-button-danger', accept: () => router.delete(route('users.destroy', id)) }); };

// Helper Role
const getRoleLabel = (role) => {
    const map = { 'super_admin': 'Super Admin', 'admin_prm': 'Admin Ranting', 'admin_aum': 'Admin AUM', 'member': 'Member' };
    return map[role] || role;
};
const getRoleSeverity = (role) => {
    const map = { 'super_admin': 'danger', 'admin_prm': 'warning', 'admin_aum': 'info', 'member': 'secondary' };
    return map[role] || 'info';
};
</script>

<template>
    <Head title="Manajemen User" />
    <AppLayout>
        <div class="max-w-7xl mx-auto space-y-6">
            
            <div class="flex flex-col md:flex-row justify-between items-end gap-4 bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
                <div>
                    <h1 class="text-2xl font-black text-gray-800 tracking-tight">Access Control</h1>
                    <p class="text-gray-500 text-sm mt-1">Kelola akun login dan hak akses sistem.</p>
                </div>
                
                <div class="flex gap-3 w-full md:w-auto">
                    <IconField iconPosition="left" class="w-full md:w-64">
                        <InputIcon class="pi pi-search text-gray-400" />
                        <InputText v-model="filters['global'].value" placeholder="Cari nama / email..." class="w-full !rounded-xl !border-gray-200 focus:!border-emerald-500 !bg-gray-50" />
                    </IconField>
                    
                    <Link :href="route('users.create')">
                        <Button label="Baru" icon="pi pi-plus" class="!bg-gray-900 !border-gray-900 !rounded-xl !font-bold shadow-lg shadow-gray-200" />
                    </Link>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <DataTable v-model:filters="filters" :value="users" paginator :rows="10" 
                           :globalFilterFields="['name', 'email', 'organization_unit.name']" stripedRows
                           tableStyle="min-width: 50rem">
                    
                    <Column field="name" header="Pengguna" style="width: 30%">
                        <template #body="{ data }">
                            <div class="flex items-center gap-3 py-2">
                                <div class="w-10 h-10 rounded-md bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center font-bold text-gray-600 border border-white shadow-sm">
                                    {{ data.name.charAt(0) }}
                                </div>
                                <div>
                                    <div class="font-bold text-gray-800 text-sm">{{ data.name }}</div>
                                    <div class="text-xs text-gray-400">{{ data.email }}</div>
                                </div>
                            </div>
                        </template>
                    </Column>

                    <Column field="role" header="Role" style="width: 15%">
                        <template #body="{ data }">
                            <Tag :value="getRoleLabel(data.role)" :severity="getRoleSeverity(data.role)" class="!text-[10px] !px-2 uppercase tracking-wide" rounded />
                        </template>
                    </Column>

                    <Column field="organization_unit.name" header="Unit Kerja" style="width: 30%">
                        <template #body="{ data }">
                            <div v-if="data.organization_unit" class="flex items-center gap-2 text-sm text-gray-600">
                                <i class="pi pi-building text-gray-300"></i>
                                <span class="font-medium">{{ data.organization_unit.name }}</span>
                            </div>
                            <span v-else-if="data.role === 'super_admin'" class="text-xs text-emerald-600 font-bold italic flex items-center gap-1">
                                <i class="pi pi-globe"></i> Global Access
                            </span>
                            <span v-else class="text-xs text-gray-300 italic">-</span>
                        </template>
                    </Column>

                    <Column header="Status" style="width: 15%; text-align: center">
                        <template #body="{ data }">
                            <div class="flex justify-center" v-tooltip="data.is_active ? 'Klik untuk Nonaktifkan' : 'Klik untuk Aktifkan'">
                                <InputSwitch :modelValue="data.is_active" @click="toggleStatus($event, data)" 
                                             :disabled="data.id === $page.props.auth.user.id" />
                            </div>
                        </template>
                    </Column>

                    <Column header="Link Member" style="width: 15%; text-align: center">
                        <template #body="{ data }">
                            <div v-if="data.member" class="group relative inline-block">
                                <Button icon="pi pi-link" rounded severity="success" size="small" outlined class="!w-8 !h-8" v-tooltip="data.member.full_name" />
                                <i class="pi pi-times-circle text-red-500 absolute -top-1 -right-1 bg-white rounded-full cursor-pointer hover:scale-110 transition opacity-0 group-hover:opacity-100" 
                                   @click="confirmUnlink($event, data)"></i>
                            </div>
                            <Button v-else icon="pi pi-link" rounded severity="secondary" text size="small" 
                                    class="!w-8 !h-8 opacity-50 hover:opacity-100" 
                                    @click="openLinkModal(data)" v-tooltip="'Hubungkan Data'" />
                        </template>
                    </Column>

                    <Column header="Aksi" style="width: 10%; text-align: center">
                        <template #body="{ data }">
                            <div class="flex justify-center gap-1">
                                
                                <Button v-if="data.id === $page.props.auth.user.id" 
                                        icon="pi pi-pencil" text rounded severity="secondary" size="small" disabled 
                                        v-tooltip="'Tidak dapat mengedit akun sendiri di sini'" />
                                
                                <Link v-else :href="route('users.edit', data.id)">
                                    <Button icon="pi pi-pencil" text rounded severity="info" size="small" class="hover:bg-blue-50" v-tooltip="'Edit User'" />
                                </Link>

                                <Button icon="pi pi-trash" text rounded severity="danger" size="small" 
                                        :class="data.id === $page.props.auth.user.id ? '' : 'hover:bg-red-50'"
                                        @click="handleDelete($event, data.id)" 
                                        :disabled="data.id === $page.props.auth.user.id"
                                        v-tooltip="data.id === $page.props.auth.user.id ? 'Tidak dapat menghapus akun sendiri' : 'Hapus User'" />
                            </div>
                        </template>
                    </Column>
                </DataTable>
            </div>
        </div>

        <Dialog v-model:visible="linkDialog" modal header="Hubungkan Akun" :style="{ width: '25rem' }" :breakpoints="{ '960px': '75vw', '641px': '90vw' }">
            <div class="space-y-4">
                <p class="text-sm text-gray-500">Pilih anggota yang akan menggunakan akun ini.</p>
                <div class="flex flex-col gap-1">
                    <Dropdown v-model="linkForm.member_id" :options="availableMembers" optionLabel="label" optionValue="id" filter placeholder="Cari Nama..." class="w-full">
                        <template #option="slotProps">
                            <div class="flex flex-col py-1 border-b border-gray-50 last:border-0">
                                <span class="font-bold text-sm text-gray-800">{{ slotProps.option.label }}</span>
                                <span class="text-xs text-emerald-600">{{ slotProps.option.sub_label }}</span>
                            </div>
                        </template>
                    </Dropdown>
                </div>
            </div>
            <template #footer>
                <Button label="Batal" text @click="linkDialog = false" size="small" />
                <Button label="Simpan" icon="pi pi-check" @click="submitLink" :loading="linkForm.processing" size="small" severity="success" />
            </template>
        </Dialog>

        <ConfirmPopup />
    </AppLayout>
</template>