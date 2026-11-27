import { afterEach, describe, expect, it, vi } from "vitest";
import { createApp, type App } from "vue";
import { router } from "@inertiajs/vue3";
import Home from "./Home.vue";

vi.mock("@inertiajs/vue3", async () => {
    const { defineComponent, h, mergeProps } = await import("vue");

    return {
        router: {
            visit: vi.fn(),
        },
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
    vi.clearAllMocks();
});

function mountHome() {
    container = document.createElement("div");
    document.body.append(container);

    app = createApp(Home);
    app.mount(container);

    return container;
}

describe("Home", () => {
    it("renders the main page", () => {
        const root = mountHome();

        expect(root.querySelector("main")).not.toBeNull();
    });

    it("renders the ExpertBanner component", () => {
        const root = mountHome();

        expect(root.textContent).toContain("Soluciones de expertos");

        expect(root.textContent).toContain("Conviertete en experto");
    });

    it("renders the HowItWorksSection component", () => {
        const root = mountHome();

        expect(root.textContent).toContain("¿Cómo funciona Zeivor?");

        expect(root.textContent).toContain("REGISTRA");
        expect(root.textContent).toContain("SOLICITA");
        expect(root.textContent).toContain("CONTRATA");
    });

    it("renders the PricingSection component", () => {
        const root = mountHome();

        expect(root.textContent).toContain("Precios");
        expect(root.textContent).toContain("Nosotros");
        expect(root.textContent).toContain("Zeivor Básico");
        expect(root.textContent).toContain("Zeivor Plus");
    });

    it("renders the DownloadAppBanner component", () => {
        const root = mountHome();

        expect(root.querySelector("main")).not.toBeNull();

        expect(root.querySelectorAll("main").length).toBe(1);
    });

    it("calls router.visit when ExpertBanner emits click", () => {
        const root = mountHome();

        const link = root.querySelector('main a[href="/experto"]');

        expect(link).not.toBeNull();

        link?.dispatchEvent(
            new MouseEvent("click", {
                bubbles: true,
                cancelable: true,
            }),
        );

        expect(router.visit).toHaveBeenCalledWith("/experto");
    });

    it("renders the main sections in the correct order", () => {
        const root = mountHome();

        const main = root.querySelector("main");

        expect(main).not.toBeNull();

        const sections = main?.querySelectorAll("section");

        expect(sections!.length).toBeGreaterThanOrEqual(3);
    });
});
