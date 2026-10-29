import { createApp, h } from 'vue';
import { createInertiaApp, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
// (Bootstrap's and Remix Icon's styles come with resources/css/app.css, which
// every page loads as well: imported here too, every page downloaded them twice.)
import Swal from 'sweetalert2';
// import { ZiggyVue } from '../../vendor/tightenco/ziggy/dist/vue.m';

const appName = 'CRMS';

// What the CSF form sends its answers to.
const CSF_SUBMISSION = '/csf_submission';

// A 419 (session/CSRF expired) isn't a valid Inertia response, so Inertia's
// default behavior is to hard-navigate the browser to Laravel's raw "Page
// Expired" HTML, discarding whatever the user had filled in. Intercept it and
// explain what happened before reloading, instead of showing a broken page.
router.on('invalid', (event) => {
    if (event.detail.response?.status === 419) {
        event.preventDefault();
        // The CSF form keeps its answers: a reload would throw them away. Its
        // session is started again, and the respondent sends the form again.
        if ((event.detail.response.config?.url || '').includes(CSF_SUBMISSION)) {
            fetch('/services/csf/keep-alive', { credentials: 'same-origin', cache: 'no-store' }).catch(() => {});
            Swal.fire({
                title: 'Session Expired',
                icon: 'warning',
                text: 'Your session timed out, so the form was not sent. Your answers are still here: please press Submit again.',
                confirmButtonText: 'OK',
            });
            return;
        }
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
        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            // .use(ZiggyVue)
            .mount(el);
        // Bootstrap's own script works the dropdowns and the like of the
        // signed-in pages (their data-bs-* attributes). Fetched once the first
        // page is up, it no longer holds that page back.
        import('bootstrap/dist/js/bootstrap.bundle.min.js');
        return app;
    },
    progress: {
        color: '#de2f14',
    },

});

