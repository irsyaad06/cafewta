import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

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
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

let tabId = sessionStorage.getItem('tab_id');
if (!tabId) {
    tabId = Math.random().toString(36).substring(2, 15);
    sessionStorage.setItem('tab_id', tabId);
}

// Redirect if tab_id is missing from URL (for hard refreshes)
const currentUrl = new URL(window.location.href);
if (!currentUrl.searchParams.has('tab_id')) {
    currentUrl.searchParams.set('tab_id', tabId);
    window.location.replace(currentUrl.toString());
}

import { router } from '@inertiajs/vue3';

router.on('before', (event) => {
    event.detail.visit.headers['X-Tab-Id'] = tabId;
    
    // Read CSRF token from page props to avoid 419 Page Expired errors
    if (router.page && router.page.props && router.page.props.csrf_token) {
        event.detail.visit.headers['X-CSRF-TOKEN'] = router.page.props.csrf_token;
    }
    
    // Also append to URL for Inertia visits if not already there
    if (event.detail.visit.url) {
        try {
            if (event.detail.visit.url instanceof URL) {
                if (!event.detail.visit.url.searchParams.has('tab_id')) {
                    event.detail.visit.url.searchParams.set('tab_id', tabId);
                }
            } else {
                const urlObj = new URL(event.detail.visit.url, window.location.origin);
                if (!urlObj.searchParams.has('tab_id')) {
                    urlObj.searchParams.set('tab_id', tabId);
                    event.detail.visit.url = urlObj; // set back as URL object, which Inertia often prefers
                }
            }
        } catch(e) {
            console.error('URL Error:', e);
        }
    }
});
