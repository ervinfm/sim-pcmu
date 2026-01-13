<script setup>
import { Head } from '@inertiajs/vue3';
import WebLayout from '@/Layouts/WebLayout.vue';
import Card from 'primevue/card';
import Divider from 'primevue/divider';
import Panel from 'primevue/panel';

// Menerima data dari PageController
const props = defineProps({
    pcm: Object,      // Data dari tabel organization_units
    settings: Object  // Data dari tabel site_settings (Visi, Misi, Sejarah)
});

// Format Tanggal Berdiri
const establishmentDate = new Date(props.pcm?.establishment_date || '1934-04-28').toLocaleDateString('id-ID', {
    day: 'numeric', month: 'long', year: 'numeric'
});
</script>

<template>
    <Head title="Profil & Sejarah" />

    <WebLayout>
        <div class="bg-emerald-50 py-12 border-b border-emerald-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h1 class="text-3xl md:text-4xl font-bold text-emerald-900 mb-2">Profil Organisasi</h1>
                <p class="text-gray-600">Mengenal lebih dekat Pimpinan Cabang Muhammadiyah Muara Aman</p>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <div class="lg:col-span-1">
                    <Card class="sticky top-24 shadow-sm border border-gray-100 bg-white">
                        <template #title>
                            <div class="flex items-center gap-2 text-xl font-bold text-gray-800">
                                <i class="pi pi-building text-emerald-600"></i> Identitas Cabang Muhammadiyah
                            </div>
                        </template>
                        <template #content>
                            <div class="space-y-4 text-sm text-gray-600 mt-2">
                                <div>
                                    <span class="block font-semibold text-gray-900">Nama Resmi:</span>
                                    {{ pcm?.name || 'PIMPINAN CABANG MUHAMMADIYAH MUARA AMAN' }}
                                </div>
                                <Divider />
                                <div>
                                    <span class="block font-semibold text-gray-900">SK Pendirian:</span>
                                    {{ pcm?.sk_number || 'Nomor : 468/1943' }}
                                </div>
                                <Divider />
                                <div>
                                    <span class="block font-semibold text-gray-900">Tanggal Berdiri:</span>
                                    <div class="flex items-center gap-2">
                                        <i class="pi pi-calendar"></i>
                                        <span>{{ establishmentDate }}</span>
                                    </div>
                                    <span class="text-xs text-gray-400">(13 Muharam 1353 H)</span>
                                </div>
                                <Divider />
                                <div>
                                    <span class="block font-semibold text-gray-900">Alamat Kantor:</span>
                                    <p class="leading-relaxed">
                                        {{ pcm?.address || 'Gedung Dakwah Muhammadiyah Muara Aman Lt.2, Jl. Kampung Jawa No. 123. Kel. Pasar Muara Aman.' }}
                                    </p>
                                </div>
                                <Divider />
                                <div class="flex flex-col gap-2">
                                    <a href="#" class="flex items-center gap-2 text-blue-600 hover:underline">
                                        <i class="pi pi-facebook"></i> {{ pcm?.facebook || 'masjidaljihadmuhammadiyah' }}
                                    </a>
                                    <a href="#" class="flex items-center gap-2 text-pink-600 hover:underline">
                                        <i class="pi pi-instagram"></i> {{ pcm?.instagram || 'pcmmuaraaman' }}
                                    </a>
                                </div>
                            </div>
                        </template>
                    </Card>
                </div>

                <div class="lg:col-span-2 space-y-8">
                    
                    <div class="bg-white rounded-xl p-8 shadow-sm border border-gray-100">
                        <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center gap-2">
                            <span class="w-1 h-8 bg-emerald-600 rounded-full"></span>
                            Sejarah Perjalanan
                        </h2>
                        
                        <div class="prose max-w-none text-gray-600 leading-loose text-justify">
                            <div v-if="settings?.sejarah" v-html="settings.sejarah"></div>
                            <div v-else>
                                <p class="mb-4">
                                    Pimpinan Cabang Muhammadiyah (PCM) Muara Aman merupakan salah satu cabang historis yang berdiri sejak masa pra-kemerdekaan. 
                                    Berdasarkan catatan resmi, PCM Muara Aman didirikan pada tanggal <strong>28 April 1934 (13 Muharam 1353 H)</strong>, 
                                    diperkuat dengan Surat Ketetapan (SK) Nomor <strong>468/1943</strong>.
                                </p>
                                <p>
                                    Selama puluhan tahun, Muhammadiyah Muara Aman terus berkiprah dalam memajukan umat melalui dakwah amar ma'ruf nahi mungkar 
                                    dan pengembangan amal usaha di bidang pendidikan, sosial, dan keagamaan di wilayah Kabupaten Lebong, Provinsi Bengkulu.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <Panel header="Visi" class="shadow-sm border-0">
                            <template #header>
                                <div class="flex items-center gap-2 font-bold text-emerald-800">
                                    <i class="pi pi-eye"></i> Visi Organisasi
                                </div>
                            </template>
                            <p class="text-gray-600 italic">
                                "{{ settings?.visi || 'Terwujudnya masyarakat Islam yang sebenar-benarnya.' }}"
                            </p>
                        </Panel>

                        <Panel header="Misi" class="shadow-sm border-0">
                            <template #header>
                                <div class="flex items-center gap-2 font-bold text-emerald-800">
                                    <i class="pi pi-list"></i> Misi Organisasi
                                </div>
                            </template>
                            <ul class="list-disc list-outside ml-4 text-gray-600 space-y-2 text-sm">
                                <template v-if="settings?.misi">
                                    <div v-html="settings.misi"></div>
                                </template>
                                <template v-else>
                                    <li>Menegakkan keyakinan tauhid yang murni.</li>
                                    <li>Menyebarluaskan ajaran Islam yang bersumber pada Al-Qur'an dan As-Sunnah.</li>
                                    <li>Mewujudkan amal usaha yang unggul dan berkemajuan.</li>
                                </template>
                            </ul>
                        </Panel>
                    </div>

                </div>
            </div>
        </div>
    </WebLayout>
</template>

<style scoped>
/* Custom Style untuk Panel PrimeVue agar lebih clean */
:deep(.p-panel .p-panel-header) {
    background: #ecfdf5; /* emerald-50 */
    border: none;
    padding: 1rem 1.25rem;
}
:deep(.p-panel .p-panel-content) {
    border: none;
    padding: 1.25rem;
}
</style>