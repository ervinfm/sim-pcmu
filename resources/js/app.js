import '../css/app.css';
import './bootstrap';
import 'primeicons/primeicons.css';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

// --- PRIMEVUE CONFIG ---
import PrimeVue from 'primevue/config';
import Aura from '@primevue/themes/aura';

// --- PRIMEVUE SERVICES (TAMBAHAN PENTING) ---
import ConfirmationService from 'primevue/confirmationservice';
import ToastService from 'primevue/toastservice';

// --- PRIMEVUE DIRECTIVES (TAMBAHAN PENTING) ---
import BadgeDirective from 'primevue/badgedirective';
import Tooltip from 'primevue/tooltip';


const appName = import.meta.env.VITE_APP_NAME || 'Organisasi Islam Berkemajuan';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(PrimeVue, {
                theme: {
                    preset: Aura, // Menggunakan tema Aura
                    options: {
                        darkModeSelector: '.dark', // Sinkron dengan Dark Mode Tailwind
                    }
                }
            })
            // 2. Install Services (Agar useConfirm & useToast jalan)
            .use(ConfirmationService)
            .use(ToastService)

            // 3. Register Directives (Agar v-badge & v-tooltip jalan)
            .directive('badge', BadgeDirective)
            .directive('tooltip', Tooltip)
            .mount(el);
    },
    progress: {
        color: '#008000',
    },
});
