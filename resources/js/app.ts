import '../css/app.css';

import { createApp, h } from 'vue';
import type { DefineComponent } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';

const pages = import.meta.glob<{ default: DefineComponent }>('./Pages/**/*.vue');

createInertiaApp({
    resolve: async (name) => {
        const path = `./Pages/${name}.vue`;
        const page = pages[path];

        if (!page) {
            throw new Error(`Page not found: ${path}`);
        }

        return (await page()).default;
    },
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);
    },
});