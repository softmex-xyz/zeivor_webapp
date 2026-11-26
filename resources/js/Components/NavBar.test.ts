import { afterEach, describe, expect, it, vi } from "vitest";
import { createApp, nextTick, type App } from "vue";
import NavBar from "./NavBar.vue";

vi.mock("@inertiajs/vue3", async () => {
    const { defineComponent, h, mergeProps } = await import("vue");

    return {
        Link: defineComponent({
            props: {
                href: {
                    type: String,
                    default: "/",
                },
            },
            setup(props, { slots, attrs }) {
                return () =>
                    h(
                        "a",
                        mergeProps(attrs, {
                            href: props.href,
                            onClick: (event: MouseEvent) => {
                                event.preventDefault();
                            },
                        }),
                        slots.default?.(),
                    );
            },
        }),
    };
});

let app: App<Element> | undefined;
let container: HTMLDivElement | undefined;

afterEach(() => {
    app?.unmount();
    container?.remove();

    app = undefined;
    container = undefined;
});

function mountNavBar(props: Record<string, unknown> = {}) {
    container = document.createElement("div");
    document.body.append(container);

    app = createApp(NavBar, props);
    app.mount(container);

    return container;
}

describe("NavBar", () => {
    it("renders logo and default navigation links", () => {
        const root = mountNavBar();

        const logo = root.querySelector("img");

        expect(logo).not.toBeNull();
        expect(logo?.getAttribute("alt")).toBe(
            "Zeivor - Soluciones de Expertos",
        );
        expect(logo?.getAttribute("src")).toBeTruthy();

        expect(root.textContent).toContain("Inicio");
        expect(root.textContent).toContain("Seguridad");
        expect(root.textContent).toContain("Contacto");
        expect(root.textContent).toContain("Usuarios");
        expect(root.textContent).toContain("¿Eres un experto?");
    });

    it("renders custom logo and navigation links", () => {
        const root = mountNavBar({
            logoSrc: "/images/custom-logo.png",
            links: [
                { label: "Principal", href: "/" },
                { label: "Servicios", href: "/servicios" },
            ],
        });

        const logo = root.querySelector("img");

        expect(logo?.getAttribute("src")).toBe("/images/custom-logo.png");

        expect(root.textContent).toContain("Principal");
        expect(root.textContent).toContain("Servicios");
        expect(root.textContent).not.toContain("Seguridad");
    });

    it("renders navigation links with correct href attributes", () => {
        const root = mountNavBar();

        const links = root.querySelectorAll("nav ul a");

        expect(links.length).toBe(5);

        expect(links[0].getAttribute("href")).toBe("/");
        expect(links[1].getAttribute("href")).toBe("/seguridad");
        expect(links[2].getAttribute("href")).toBe("/contacto");
        expect(links[3].getAttribute("href")).toBe("/usuarios");
        expect(links[4].getAttribute("href")).toBe("/experto");
    });

    it("marks the current page link as active", () => {
        const root = mountNavBar({
            currentPath: "/experto",
        });

        const expertLink = root.querySelector('nav ul a[href="/experto"]');

        expect(expertLink).not.toBeNull();
        expect(expertLink?.className).toContain("text-[#E85C63]");
        expect(expertLink?.className).toContain("font-semibold");
    });

    it("does not mark hash links as active", () => {
        const root = mountNavBar({
            links: [
                { label: "Inicio", href: "/" },
                { label: "Seguridad", href: "#seguridad" },
            ],
            currentPath: "#seguridad",
        });

        const seguridadLink = root.querySelector('nav ul a[href="#seguridad"]');

        expect(seguridadLink).not.toBeNull();
        expect(seguridadLink?.className).toContain("text-[#222B3A]");
        expect(seguridadLink?.className).not.toContain("font-semibold");
    });

    it("renders the mobile menu button with correct accessibility attributes", () => {
        const root = mountNavBar();

        const toggleButton = root.querySelector(
            'button[aria-controls="mobile-nav-menu"]',
        );

        expect(toggleButton).not.toBeNull();
        expect(toggleButton?.getAttribute("type")).toBe("button");
        expect(toggleButton?.getAttribute("aria-expanded")).toBe("false");
        expect(toggleButton?.getAttribute("aria-label")).toBe("Abrir menú");
    });

    it("toggles mobile menu open and closed", async () => {
        const root = mountNavBar();

        const toggleButton = root.querySelector(
            'button[aria-controls="mobile-nav-menu"]',
        ) as HTMLButtonElement | null;

        expect(toggleButton).not.toBeNull();
        expect(toggleButton?.getAttribute("aria-expanded")).toBe("false");
        expect(root.querySelector("#mobile-nav-menu")).toBeNull();

        toggleButton?.click();
        await nextTick();

        expect(toggleButton?.getAttribute("aria-expanded")).toBe("true");
        expect(toggleButton?.getAttribute("aria-label")).toBe("Cerrar menú");
        expect(root.querySelector("#mobile-nav-menu")).not.toBeNull();

        toggleButton?.click();
        await nextTick();

        expect(toggleButton?.getAttribute("aria-expanded")).toBe("false");
        expect(root.querySelector("#mobile-nav-menu")).toBeNull();
    });

    it("renders all navigation links inside the mobile menu", async () => {
        const root = mountNavBar();

        const toggleButton = root.querySelector(
            'button[aria-controls="mobile-nav-menu"]',
        ) as HTMLButtonElement;

        toggleButton.click();
        await nextTick();

        const mobileLinks = root.querySelectorAll("#mobile-nav-menu ul a");

        expect(mobileLinks.length).toBe(5);
    });

    it("closes mobile menu when a navigation link is clicked", async () => {
        const root = mountNavBar();

        const toggleButton = root.querySelector(
            'button[aria-controls="mobile-nav-menu"]',
        ) as HTMLButtonElement;

        toggleButton.click();
        await nextTick();

        expect(root.querySelector("#mobile-nav-menu")).not.toBeNull();

        const mobileLink = root.querySelector(
            '#mobile-nav-menu a[href="/seguridad"]',
        ) as HTMLAnchorElement;

        expect(mobileLink).not.toBeNull();

        mobileLink.click();
        await nextTick();

        expect(toggleButton.getAttribute("aria-expanded")).toBe("false");
        expect(root.querySelector("#mobile-nav-menu")).toBeNull();
    });

    it("closes mobile menu when Escape key is pressed", async () => {
        const root = mountNavBar();

        const toggleButton = root.querySelector(
            'button[aria-controls="mobile-nav-menu"]',
        ) as HTMLButtonElement;

        toggleButton.click();
        await nextTick();

        expect(root.querySelector("#mobile-nav-menu")).not.toBeNull();

        window.dispatchEvent(
            new KeyboardEvent("keydown", {
                key: "Escape",
            }),
        );

        await nextTick();

        expect(root.querySelector("#mobile-nav-menu")).toBeNull();
        expect(toggleButton.getAttribute("aria-expanded")).toBe("false");
    });

    it("does not close the menu when another key is pressed", async () => {
        const root = mountNavBar();

        const toggleButton = root.querySelector(
            'button[aria-controls="mobile-nav-menu"]',
        ) as HTMLButtonElement;

        toggleButton.click();
        await nextTick();

        window.dispatchEvent(
            new KeyboardEvent("keydown", {
                key: "Enter",
            }),
        );

        await nextTick();

        expect(root.querySelector("#mobile-nav-menu")).not.toBeNull();
    });

    it("closes mobile menu when logo is clicked", async () => {
        const root = mountNavBar();

        const toggleButton = root.querySelector(
            'button[aria-controls="mobile-nav-menu"]',
        ) as HTMLButtonElement;

        toggleButton.click();
        await nextTick();

        const logoLink = root.querySelector(
            'nav a[href="/"]',
        ) as HTMLAnchorElement;

        logoLink.click();
        await nextTick();

        expect(root.querySelector("#mobile-nav-menu")).toBeNull();
    });

    it("renders the navigation with the correct accessibility label", () => {
        const root = mountNavBar();

        const nav = root.querySelector("nav");

        expect(nav).not.toBeNull();
        expect(nav?.getAttribute("aria-label")).toBe("Navegación principal");
    });

    it("removes keyboard event listener when component is unmounted", () => {
        const removeEventListenerSpy = vi.spyOn(window, "removeEventListener");

        mountNavBar();

        app?.unmount();
        app = undefined;

        expect(removeEventListenerSpy).toHaveBeenCalledWith(
            "keydown",
            expect.any(Function),
        );

        removeEventListenerSpy.mockRestore();
    });
});
