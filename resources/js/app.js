import '../css/app.css';
import './bootstrap';

import { createInertiaApp, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import Swal from 'sweetalert2';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

// SweetAlert2 Toast Configuration
const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    didOpen: (toast) => {
        toast.onmouseenter = Swal.stopTimer;
        toast.onmouseleave = Swal.resumeTimer;
    }
});

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue);

        // Make Swal and Toast available globally
        app.config.globalProperties.$swal = Swal;
        app.config.globalProperties.$toast = Toast;

        app.mount(el);

        // Check for Laravel flash messages and show SweetAlert2
        const page = props.initialPage;
        if (page.props.flash) {
            const flash = page.props.flash;

            if (flash.success) {
                Toast.fire({
                    icon: 'success',
                    title: flash.success
                });
            }

            if (flash.error) {
                Toast.fire({
                    icon: 'error',
                    title: flash.error
                });
            }

            if (flash.warning) {
                Toast.fire({
                    icon: 'warning',
                    title: flash.warning
                });
            }

            if (flash.info) {
                Toast.fire({
                    icon: 'info',
                    title: flash.info
                });
            }
        }

        return app;
    },
    progress: {
        color: '#4B5563',
    },
});

// Listen for Inertia navigation events to show flash messages
router.on('success', (event) => {
    const flash = event.detail.page.props.flash;
    if (flash) {
        if (flash.success) {
            Toast.fire({
                icon: 'success',
                title: flash.success
            });
        }
        if (flash.error) {
            Toast.fire({
                icon: 'error',
                title: flash.error
            });
        }
        if (flash.warning) {
            Toast.fire({
                icon: 'warning',
                title: flash.warning
            });
        }
        if (flash.info) {
            Toast.fire({
                icon: 'info',
                title: flash.info
            });
        }
    }
});
