import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/app.js',
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    // --- KONFIGURASI PENTING UNTUK DOCKER WINDOWS ---
    server: {
        host: '0.0.0.0',
        hmr: {
            host: 'localhost',
        },
        watch: {
            usePolling: true,
            // UBAH DARI 100 KE 1000 (1 Detik)
            // Ini akan sangat meringankan beban CPU komputer Anda
            interval: 1000, 
            
            // TAMBAHAN: Jangan buang tenaga mengecek folder sistem
            ignored: ['**/node_modules/**', '**/vendor/**', '**/.git/**', '**/storage/**'],
        },
    },
    // --- OPTIMASI AGAR TIDAK SERING RELOAD ---
    optimizeDeps: {
        include: [
            'primevue/config',
            'primevue/button',
            'primevue/card',
            'primevue/panel',
            'primevue/divider',
            'primevue/menubar', // Tambahan
            'primevue/badge',   // Tambahan
            'primevue/avatar',  // Tambahan
            'primevue/menu',    // Tambahan
            'primeicons/primeicons.css',
            '@inertiajs/vue3',
            'axios',
            'vue'
        ]
    }
});