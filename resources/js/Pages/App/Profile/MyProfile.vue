<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from 'primevue/card';
import Tag from 'primevue/tag';
import Avatar from 'primevue/avatar';
import Button from 'primevue/button';
import ProgressBar from 'primevue/progressbar';
import Divider from 'primevue/divider';

const props = defineProps({ 
    user: Object, 
    member: Object,
    completion: Number 
});

const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
};
</script>

<template>
    <Head title="Profil Saya" />
    <AppLayout>
        <div class="max-w-6xl mx-auto">
            
            <div class="mb-8">
                <h1 class="text-3xl font-black text-gray-800 tracking-tight">Identity Hub</h1>
                <p class="text-gray-500 mt-1">Pusat data diri dan keanggotaan digital Anda.</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mt-6">
                
                <div class="lg:col-span-4 space-y-6">
                    
                    <div class="relative group perspective">
                        <div class="absolute inset-0 bg-gradient-to-r from-emerald-400 to-teal-600 rounded-2xl blur-xl opacity-40 group-hover:opacity-60 transition duration-500"></div>
                        
                        <div class="relative bg-gradient-to-br from-emerald-900 to-teal-800 rounded-2xl p-5 text-white shadow-2xl overflow-hidden border border-emerald-700/50 min-h-[220px] flex flex-col justify-between">
                            
                            <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle, #fff 1px, transparent 1px); background-size: 20px 20px;"></div>
                            
                            <div class="relative z-10 flex justify-between items-start">
                                <div class="flex items-center gap-3">
                                    <img src="/images/logo.png" class="w-10 h-10 drop-shadow-md brightness-200 grayscale-0"> 
                                    <div>
                                        <h3 class="text-xs font-bold tracking-[0.2em] text-emerald-200">KARTU TANDA ANGGOTA</h3>
                                        <p class="text-[10px] text-emerald-400">MUHAMMADIYAH</p>
                                    </div>
                                </div>
                                <i class="pi pi-wifi text-emerald-500/50 text-2xl"></i>
                            </div>

                            <div class="relative z-10 flex items-center gap-4 mt-4">
                                <div class="w-10 h-8 bg-yellow-200/80 rounded-md border border-yellow-400/50 shadow-inner flex items-center justify-center">
                                    <div class="w-6 h-5 border border-yellow-600/30 rounded-sm grid grid-cols-2 gap-px"></div>
                                </div>
                                <div>
                                    <p class="text-[10px] text-emerald-300 uppercase">Nomor Baku (NBM)</p>
                                    <p class="text-lg font-mono font-bold tracking-widest text-white text-shadow">
                                        {{ member?.nbm || '---- ----' }}
                                    </p>
                                </div>
                            </div>

                            <div class="relative z-10 flex justify-between items-end mt-4">
                                <div>
                                    <p class="text-[9px] text-emerald-300 uppercase">Nama Anggota</p>
                                    <p class="font-bold text-sm uppercase tracking-wide truncate max-w-[180px]">
                                        {{ member?.full_name || user.name }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-[9px] text-emerald-300 uppercase">Berlaku Hingga</p>
                                    <p class="font-bold text-xs">SEUMUR HIDUP</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm font-bold text-gray-700">Kualitas Profil</span>
                            <span class="text-xs font-bold text-emerald-600">{{ completion }}%</span>
                        </div>
                        <ProgressBar :value="completion" :showValue="false" class="h-2 bg-gray-100" 
                                     :pt="{ value: { class: 'bg-emerald-500' } }" />
                        <p class="text-xs text-gray-400 mt-3">
                            Lengkapi data diri Anda (Foto, NIK, Alamat) untuk mendapatkan akses penuh ke fitur sistem.
                        </p>
                        <Link :href="route('members.edit', member?.id || '#')" v-if="member">
                            <Button label="Lengkapi Data" size="small" outlined class="w-full mt-4 !text-xs !border-emerald-200 !text-emerald-700 hover:!bg-emerald-50" icon="pi pi-pencil" />
                        </Link>
                    </div>

                </div>

                <div class="lg:col-span-8">
                    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
                        
                        <div class="h-32 bg-gradient-to-r from-gray-50 to-gray-100 relative">
                            <div class="absolute bottom-0 left-0 w-full h-16 bg-gradient-to-t from-white to-transparent"></div>
                        </div>

                        <div class="px-8 pb-8 relative">
                            <div class="flex flex-col md:flex-row md:items-end gap-6 -mt-12 mb-6">
                                <div class="relative">
                                    <div class="w-24 h-24 rounded-2xl border-4 border-white shadow-lg overflow-hidden bg-white">
                                        <img v-if="member?.photo_path" :src="'/storage/' + member.photo_path" class="w-full h-full object-cover">
                                        <div v-else class="w-full h-full bg-emerald-50 flex items-center justify-center text-emerald-300">
                                            <i class="pi pi-user text-4xl"></i>
                                        </div>
                                    </div>
                                    <div class="absolute -bottom-2 -right-2 bg-white p-1 rounded-full shadow-sm">
                                        <i class="pi pi-check-circle text-blue-500 text-xl"></i>
                                    </div>
                                </div>
                                <div class="flex-1 pb-2">
                                    <h2 class="text-2xl font-bold text-gray-800">{{ member?.full_name || user.name }}</h2>
                                    <div class="flex items-center gap-2 text-sm text-gray-500 mt-1">
                                        <i class="pi pi-building text-gray-400"></i>
                                        <span>{{ member?.organization_unit?.name || 'Belum terafiliasi' }}</span>
                                        <span class="mx-1">•</span>
                                        <span class="text-emerald-600 font-medium">{{ member?.muhammadiyah_position || 'Anggota' }}</span>
                                    </div>
                                </div>
                            </div>

                            <Divider />

                            <div v-if="member" class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-8 mt-6">
                                
                                <div>
                                    <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                                        <i class="pi pi-id-card"></i> Informasi Pribadi
                                    </h4>
                                    <ul class="space-y-4">
                                        <li class="flex justify-between border-b border-gray-50 pb-2">
                                            <span class="text-sm text-gray-500">NIK (KTP)</span>
                                            <span class="text-sm font-medium text-gray-800">{{ member.nik || '-' }}</span>
                                        </li>
                                        <li class="flex justify-between border-b border-gray-50 pb-2">
                                            <span class="text-sm text-gray-500">Jenis Kelamin</span>
                                            <span class="text-sm font-medium text-gray-800">{{ member.gender == 'L' ? 'Laki-laki' : 'Perempuan' }}</span>
                                        </li>
                                        <li class="flex justify-between border-b border-gray-50 pb-2">
                                            <span class="text-sm text-gray-500">Tempat Lahir</span>
                                            <span class="text-sm font-medium text-gray-800">{{ member.birth_place }}</span>
                                        </li>
                                        <li class="flex justify-between border-b border-gray-50 pb-2">
                                            <span class="text-sm text-gray-500">Tanggal Lahir</span>
                                            <span class="text-sm font-medium text-gray-800">{{ formatDate(member.birth_date) }}</span>
                                        </li>
                                    </ul>
                                </div>

                                <div>
                                    <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                                        <i class="pi pi-briefcase"></i> Kontak & Profesi
                                    </h4>
                                    <ul class="space-y-4">
                                        <li class="flex justify-between border-b border-gray-50 pb-2">
                                            <span class="text-sm text-gray-500">No. WhatsApp</span>
                                            <span class="text-sm font-medium text-emerald-600">{{ member.phone_number || '-' }}</span>
                                        </li>
                                        <li class="flex justify-between border-b border-gray-50 pb-2">
                                            <span class="text-sm text-gray-500">Pekerjaan</span>
                                            <span class="text-sm font-medium text-gray-800">{{ member.job || '-' }}</span>
                                        </li>
                                        <li class="flex justify-between border-b border-gray-50 pb-2">
                                            <span class="text-sm text-gray-500">Pendidikan Terakhir</span>
                                            <span class="text-sm font-medium text-gray-800">{{ member.last_education }}</span>
                                        </li>
                                        <li class="flex flex-col gap-1 border-b border-gray-50 pb-2">
                                            <span class="text-sm text-gray-500">Alamat Rumah</span>
                                            <span class="text-sm font-medium text-gray-800 leading-snug">{{ member.address }} <span class="text-gray-400 text-xs">({{ member.village }}, {{ member.district }})</span></span>
                                        </li>
                                    </ul>
                                </div>

                            </div>

                            <div v-else class="text-center py-16 bg-gray-50 rounded-2xl border border-dashed border-gray-200 mt-6">
                                <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center mx-auto mb-4 shadow-sm">
                                    <i class="pi pi-user-plus text-2xl text-gray-300"></i>
                                </div>
                                <h3 class="text-gray-800 font-bold mb-1">Data Anggota Belum Terhubung</h3>
                                <p class="text-gray-500 text-sm max-w-md mx-auto mb-6">
                                    Akun Anda belum terhubung dengan database keanggotaan. Silakan hubungi Administrator untuk sinkronisasi data.
                                </p>
                                <Button label="Hubungi Admin" icon="pi pi-whatsapp" severity="success" size="small" />
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.text-shadow {
    text-shadow: 0 1px 2px rgba(0,0,0,0.3);
}
.perspective {
    perspective: 1000px;
}
</style>