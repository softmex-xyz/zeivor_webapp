import { afterEach, describe, expect, it } from "vitest";
import { createApp, type App } from "vue";
import PricingSection from "./PricingSection.vue";

let app: App<Element> | undefined;
let container: HTMLDivElement | undefined;

afterEach(() => {
    app?.unmount();
    container?.remove();
    app = undefined;
    container = undefined;
});

function mountPricing(props: Record<string, unknown> = {}) {
    container = document.createElement("div");
    document.body.append(container);
    app = createApp(PricingSection, props);
    app.mount(container);
    return container;
}

describe("PricingSection", () => {
    it("renders the main title", () => {
        const root = mountPricing();

        const title = root.querySelector("h2");

        expect(title).not.toBeNull();
        expect(title?.textContent).toContain("Precios");
    });

    it("renders the Nosotros section", () => {
        const root = mountPricing();

        expect(root.textContent).toContain("Nosotros");
        expect(root.textContent).toContain(
            "Con la visión de conectar el talento local",
        );
        expect(root.textContent).toContain("eliminando intermediarios");
    });

    it("renders the Nosotros image with alt attribute", () => {
        const root = mountPricing();

        const image = root.querySelector("img");

        expect(image).not.toBeNull();
        expect(image?.getAttribute("src")).toBeTruthy();
        expect(image?.getAttribute("alt")).toBe("Plataforma Zeivor");
    });

    it("renders the two default pricing plans", () => {
        const root = mountPricing();

        const articles = root.querySelectorAll("article");

        expect(articles.length).toBe(2);
        expect(root.textContent).toContain("Zeivor Básico");
        expect(root.textContent).toContain("Zeivor Plus");
    });

    it("renders the correct prices and periods", () => {
        const root = mountPricing();

        expect(root.textContent).toContain("$2");
        expect(root.textContent).toContain("$5");
        expect(root.textContent).toContain("/ mensual");
    });

    it("renders the default plan features", () => {
        const root = mountPricing();

        const features = Array.from(root.querySelectorAll("article li")).map(
            (feature) => feature.textContent?.trim(),
        );

        expect(features).toHaveLength(6);

        expect(features).toContain("Acceso a miles de clientes");

        expect(features).toContain("Soporte las 24 horas");

        expect(features).toContain("Las opciones más cercanas a tu ubicación");
    });

    it("renders the registration buttons", () => {
        const root = mountPricing();

        const buttons = root.querySelectorAll("article button");

        expect(buttons.length).toBe(2);

        buttons.forEach((button) => {
            expect(button.textContent).toContain("Registrarse");
            expect(button.getAttribute("type")).toBe("button");
        });
    });

    it("renders custom pricing plans when plans prop is provided", () => {
        const root = mountPricing({
            plans: [
                {
                    id: "premium",
                    name: "Zeivor Premium",
                    price: "$10",
                    period: "/ anual",
                    features: ["Atención personalizada", "Acceso prioritario"],
                    ctaLabel: "Contratar ahora",
                    featured: true,
                    disabled: false,
                },
            ],
        });

        const articles = root.querySelectorAll("article");

        expect(articles.length).toBe(1);
        expect(root.textContent).toContain("Zeivor Premium");
        expect(root.textContent).toContain("$10");
        expect(root.textContent).toContain("/ anual");
        expect(root.textContent).toContain("Atención personalizada");
        expect(root.textContent).toContain("Acceso prioritario");
        expect(root.textContent).toContain("Contratar ahora");
    });

    it("renders the button as disabled when plan is disabled", () => {
        const root = mountPricing({
            plans: [
                {
                    id: "disabled-plan",
                    name: "Plan Deshabilitado",
                    price: "$0",
                    period: "/ mensual",
                    features: ["Acceso limitado"],
                    ctaLabel: "No disponible",
                    featured: false,
                    disabled: true,
                },
            ],
        });

        const button = root.querySelector("article button");

        expect(button).not.toBeNull();
        expect((button as HTMLButtonElement).disabled).toBe(true);
        expect(button?.textContent).toContain("No disponible");
    });

    it("renders an enabled button when plan is not disabled", () => {
        const root = mountPricing({
            plans: [
                {
                    id: "active-plan",
                    name: "Plan Activo",
                    price: "$15",
                    period: "/ mensual",
                    features: ["Acceso completo"],
                    ctaLabel: "Registrarse",
                    featured: false,
                    disabled: false,
                },
            ],
        });

        const button = root.querySelector(
            "article button",
        ) as HTMLButtonElement;

        expect(button).not.toBeNull();
        expect(button.disabled).toBe(false);
    });

    it("renders featured and non-featured plans", () => {
        const root = mountPricing({
            plans: [
                {
                    id: "featured",
                    name: "Plan Destacado",
                    price: "$20",
                    period: "/ mensual",
                    features: ["Característica destacada"],
                    ctaLabel: "Elegir plan",
                    featured: true,
                },
                {
                    id: "normal",
                    name: "Plan Normal",
                    price: "$10",
                    period: "/ mensual",
                    features: ["Característica normal"],
                    ctaLabel: "Elegir plan",
                    featured: false,
                },
            ],
        });

        const articles = root.querySelectorAll("article");

        expect(articles.length).toBe(2);

        expect(articles[0].className).toContain("border-[#ff5a6e]");

        expect(articles[1].className).toContain("border-neutral-200");
    });

    it("renders the correct number of features for each plan", () => {
        const root = mountPricing({
            plans: [
                {
                    id: "custom",
                    name: "Plan Personalizado",
                    price: "$25",
                    period: "/ mensual",
                    features: [
                        "Beneficio uno",
                        "Beneficio dos",
                        "Beneficio tres",
                        "Beneficio cuatro",
                    ],
                    ctaLabel: "Contratar",
                },
            ],
        });

        const features = root.querySelectorAll("article li");

        expect(features.length).toBe(4);
    });

    it("renders check icons for each feature", () => {
        const root = mountPricing();

        const icons = root.querySelectorAll("article li svg");

        expect(icons.length).toBe(6);

        icons.forEach((icon) => {
            expect(icon.getAttribute("aria-hidden")).toBe("true");
        });
    });

    it("renders the section with the correct accessibility attributes", () => {
        const root = mountPricing();

        const section = root.querySelector("section");

        expect(section).not.toBeNull();
        expect(section?.getAttribute("aria-labelledby")).toBe("pricing-title");

        const title = root.querySelector("#pricing-title");

        expect(title).not.toBeNull();
    });

    it("renders an empty list when plans is an empty array", () => {
        const root = mountPricing({
            plans: [],
        });

        const articles = root.querySelectorAll("article");

        expect(articles.length).toBe(0);
    });
});
