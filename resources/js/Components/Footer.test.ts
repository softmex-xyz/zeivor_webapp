import { afterEach, describe, expect, it } from 'vitest';
import { createApp, type App } from 'vue';
import Footer from './Footer.vue';

let app: App<Element> | undefined;
let container: HTMLDivElement | undefined;

afterEach(() => {
    app?.unmount();
    container?.remove();
    app = undefined;
    container = undefined;
});

describe('Footer', () => {
    it('renders logo, legal links, social links, and download button', () => {
        container = document.createElement('div');
        document.body.append(container);
        app = createApp(Footer);
        app.mount(container);

        const logo = container.querySelector('img');
        expect(logo?.getAttribute('alt')).toBe('Zeivor - Soluciones de Expertos');
        expect(logo?.getAttribute('src')).toBeTruthy();

        expect(container.textContent).toContain('Aviso de Privacidad');
        expect(container.textContent).toContain('Términos y Condiciones');

        expect(container.querySelector('a[aria-label="Facebook"]')).not.toBeNull();
        expect(container.querySelector('a[aria-label="Instagram"]')).not.toBeNull();
        expect(container.querySelector('a[aria-label="YouTube"]')).not.toBeNull();
        expect(container.querySelector('a[aria-label="TikTok"]')).not.toBeNull();
    });
});

