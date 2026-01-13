<script setup>
import { Link, usePage } from '@inertiajs/vue3';
const page = usePage();
const settings = page.props.site_settings;
</script>

<template>
    <div class="min-h-screen flex bg-gray-50">
        
        <div class="hidden lg:flex lg:w-1/2 bg-emerald-900 relative items-center justify-center overflow-hidden">
            <div class="absolute inset-0 opacity-10 bg-[url('/images/pattern.svg')]"></div>
            
            <div class="absolute -top-24 -left-24 w-96 h-96 bg-emerald-800 rounded-full blur-3xl opacity-50"></div>
            <div class="absolute bottom-0 right-0 w-80 h-80 bg-emerald-600 rounded-full blur-3xl opacity-30"></div>

            <div class="relative z-10 text-center px-10">
                <Link href="/">
                    <img src="/images/logo-pcmu-dark.png" class="h-32 w-auto mx-auto mb-8 drop-shadow-lg" alt="Logo PCM">
                </Link>
                <h2 class="text-3xl font-bold text-white mb-2">Sistem Informasi Manajemen Terpadu</h2>
                <h2 class="text-white text-xl">Pimpinan Cabang Muhammadiyah Muara Aman</h2>
                <p class="text-emerald-400 text-sm mt-4 max-w-md mx-auto">
                    Kelola Anggota, Keuangan, dan Aset Persyarikatan dalam satu pintu yang terintegrasi dan transparan.
                </p>
            </div>
        </div>

        <div class="w-full lg:w-1/2 flex flex-col justify-center items-center p-8 bg-white">
            <div class="w-full max-w-md space-y-8">
                <div class="text-center lg:hidden">
                    <Link href="/">
                        <img src="/images/logo.png" class="h-16 w-auto mx-auto mb-4" alt="Logo">
                    </Link>
                    <h2 class="text-2xl font-bold text-gray-900">Login System</h2>
                    <p class="text-sm text-gray-500">Silakan masuk untuk melanjutkan</p>
                </div>

                <div class="mt-8">
                    <slot />
                </div>

                <p class="text-center text-xs text-gray-400 mt-10">
                    &copy; 2026 PCM Muara Aman. All rights reserved.
                </p>
            </div>
        </div>
    </div>
</template>