import { afterEach, describe, expect, it } from 'vitest';
import { createApp, type App } from 'vue';
import Home from './Home.vue';

let app: App<Element> | undefined;
let container: HTMLDivElement | undefined;

afterEach(() => {
    app?.unmount();
    container?.remove();
    app = undefined;
    container = undefined;
});

describe('Home', () => {
    it('renders the supplied message', () => {
        container = document.createElement('div');
        document.body.append(container);
        app = createApp(Home, { mensaje: 'Welcome to Zeivor' });
        app.mount(container);

        expect(container.querySelector('h1')?.textContent).toBe(
            'Laravel + Inertia + Vue',
        );
        expect(container.querySelector('p')?.textContent).toBe(
            'Welcome to Zeivor',
        );
    });
});
