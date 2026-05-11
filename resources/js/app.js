/**
 * app.js — Vue + Inertia.js Bootstrap File
 * ─────────────────────────────────────────
 * এই file-টি পুরো frontend-এর entry point।
 * এখান থেকে Vue এবং Inertia initialize হয়।
 *
 * Inertia.js কী করে?
 * ───────────────────
 * সাধারণত SPA বানাতে Vue + Laravel দুটো আলাদা project লাগে,
 * এবং REST API দিয়ে data exchange হয়।
 *
 * Inertia একটি "adapter" যা:
 *   - Laravel controller থেকে Vue page component render করতে দেয়
 *   - Props হিসেবে data pass করতে দেয় (API ছাড়াই)
 *   - Page reload ছাড়াই navigation handle করে
 *
 * এটি ব্যবহার করে:
 *   - Backend: inertia-laravel package
 *   - Frontend: @inertiajs/vue3 package
 *
 * ফলাফল: Traditional Laravel এর সহজতা + Vue-এর reactivity!
 */

import './bootstrap';      // Axios এবং অন্যান্য setup
import '../css/app.css';   // Tailwind CSS import

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

// APP_NAME: .env থেকে আসে, browser tab-এ দেখায়
const appName = import.meta.env.VITE_APP_NAME || 'Digital Blood Connect';

createInertiaApp({
    /**
     * Browser tab title format নির্ধারণ করে।
     * <Head title="Find Donors"> লিখলে tab-এ দেখাবে:
     *   "Find Donors — Digital Blood Connect"
     */
    title: (title) => `${title} — ${appName}`,

    /**
     * Page component resolve করে।
     * Inertia যখন 'Donors/Index' page render করতে চায়,
     * তখন এই function resources/js/Pages/Donors/Index.vue file খুঁজে দেয়।
     *
     * import.meta.glob() = Vite-এর feature, সব Vue files lazily import করে।
     * Lazy loading মানে: page প্রথমবার open হলেই শুধু সেই file download হবে।
     * এতে initial load time কমে।
     */
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue')
        ),

    /**
     * Vue app setup।
     * el = DOM element যেখানে Vue mount হবে (app.blade.php-এর #app div)
     * App = Inertia-এর root component
     * props = server থেকে আসা initial data
     */
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)       // Inertia plugin register করো
            .use(ZiggyVue)     // Ziggy: PHP route() function কে JS-এ ব্যবহার দেয়
            .mount(el);        // #app div-এ Vue mount করো
    },

    /**
     * Progress bar: Page navigate করার সময় top-এ একটি
     * progress bar দেখায় (loading indicator)।
     * color: আমাদের brand red
     */
    progress: {
        color: '#BC0000',
        showSpinner: false,
    },
});