import { createApp, h } from 'vue';
import { createInertiaApp, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import 'bootstrap/dist/css/bootstrap.min.css'
import 'bootstrap/dist/js/bootstrap.bundle.min.js'
import 'remixicon/fonts/remixicon.css'
import Swal from 'sweetalert2';
// import { ZiggyVue } from '../../vendor/tightenco/ziggy/dist/vue.m';

const appName = 'CRMS';

// A 419 (session/CSRF expired) isn't a valid Inertia response, so Inertia's
// default behavior is to hard-navigate the browser to Laravel's raw "Page
// Expired" HTML, discarding whatever the user had filled in. Intercept it and
// explain what happened before reloading, instead of showing a broken page.
router.on('invalid', (event) => {
    if (event.detail.response?.status === 419) {
        event.preventDefault();
        Swal.fire({
            title: 'Session Expired',
            icon: 'warning',
            text: 'Your session timed out due to inactivity. The page will now reload — please fill out and submit the form again.',
            confirmButtonText: 'Reload',
            allowOutsideClick: false,
            allowEscapeKey: false,
        }).then(() => window.location.reload());
    }
});

createInertiaApp({
    title: (title) => title ? `${appName} | ${title}` : appName,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            // .use(ZiggyVue)
            .mount(el);
    },
    progress: {
        color: '#de2f14',
    },

});

