import { afterEach, describe, expect, it } from "vitest";
import { createApp, type App } from "vue";
import ExpertBanner from "./ExpertBanner.vue";

let app: App<Element> | undefined;
let container: HTMLDivElement | undefined;

afterEach(() => {
    app?.unmount();
    container?.remove();
    app = undefined;
    container = undefined;
});

function mountBanner(
    props: Record<string, unknown> = {},
    onClick?: () => void,
) {
    container = document.createElement("div");
    document.body.append(container);

    app = createApp(ExpertBanner, props);

    if (onClick) {
        app.config.globalProperties.$onClick = onClick;
    }

    app.mount(container);

    return container;
}

describe("ExpertBanner", () => {
    it("renders default title, subtitle, and CTA link", () => {
        const root = mountBanner();

        expect(root.textContent).toContain("Soluciones de expertos");

        expect(root.textContent).toContain(
            "Solicita un experto en su área de trabajo para arreglar un problema de casa, trabajo u oficina.",
        );

        const link = root.querySelector("a");

        expect(link).not.toBeNull();
        expect(link?.textContent).toContain("Conviertete en experto");
        expect(link?.getAttribute("href")).toBe("/experto");
    });

    it("renders custom props correctly", () => {
        const root = mountBanner({
            title: "Título de prueba",
            subtitle: "Subtítulo de prueba",
            ctaLabel: "Registrarme",
            href: "/registro",
        });

        expect(root.textContent).toContain("Título de prueba");
        expect(root.textContent).toContain("Subtítulo de prueba");

        const link = root.querySelector("a");

        expect(link).not.toBeNull();
        expect(link?.textContent).toContain("Registrarme");
        expect(link?.getAttribute("href")).toBe("/registro");
    });

    it("renders a button when href is empty", () => {
        const root = mountBanner({
            href: "",
        });

        const button = root.querySelector("button");

        expect(button).not.toBeNull();
        expect(button?.textContent).toContain("Conviertete en experto");
        expect(root.querySelector("a")).toBeNull();
    });

    it("emits click event when CTA link is clicked", () => {
        let clicked = 0;

        container = document.createElement("div");
        document.body.append(container);

        app = createApp(ExpertBanner, {
            onClick: () => {
                clicked += 1;
            },
        });

        app.mount(container);

        const link = container.querySelector("a");

        expect(link).not.toBeNull();

        link?.dispatchEvent(
            new MouseEvent("click", {
                bubbles: true,
                cancelable: true,
            }),
        );

        expect(clicked).toBe(1);
    });

    it("emits click event when CTA button is clicked", () => {
        let clicked = 0;

        container = document.createElement("div");
        document.body.append(container);

        app = createApp(ExpertBanner, {
            href: "",
            onClick: () => {
                clicked += 1;
            },
        });

        app.mount(container);

        const button = container.querySelector("button");

        expect(button).not.toBeNull();

        button?.dispatchEvent(
            new MouseEvent("click", {
                bubbles: true,
                cancelable: true,
            }),
        );

        expect(clicked).toBe(1);
    });

    it("renders the correct accessibility attributes", () => {
        const root = mountBanner();

        const section = root.querySelector("section");

        expect(section).not.toBeNull();
        expect(section?.getAttribute("aria-labelledby")).toBe(
            "expert-banner-title",
        );

        const title = root.querySelector("#expert-banner-title");

        expect(title).not.toBeNull();
    });
});
