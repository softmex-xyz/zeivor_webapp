import { afterEach, describe, expect, it, vi } from "vitest";
import { createApp, type App } from "vue";
import Seguridad from "./Seguridad.vue";

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

function mountSeguridad(): HTMLDivElement {
    container = document.createElement("div");
    document.body.append(container);

    app = createApp(Seguridad);
    app.mount(container);

    return container;
}

describe("Seguridad", () => {
    it("renders the security page within AppLayout and marks the nav link active", () => {
        const root = mountSeguridad();

        expect(root.querySelector("nav")).not.toBeNull();
        expect(root.querySelector("main")).not.toBeNull();
        expect(root.querySelector("footer")).not.toBeNull();

        const seguridadLink = root.querySelector('nav ul a[href="/seguridad"]');
        expect(seguridadLink?.className).toContain("text-[#E85C63]");
        expect(seguridadLink?.className).toContain("font-semibold");
    });

    it("renders the experts security section with title, image, and all 4 cards", () => {
        const root = mountSeguridad();

        expect(
            root
                .querySelector("h1#security-experts-title")
                ?.textContent?.trim(),
        ).toBe("Seguridad para los expertos");

        const expertsImage = root.querySelector(
            'img[alt="Seguridad para los expertos en Zeivor"]',
        );
        expect(expertsImage).not.toBeNull();
        expect(expertsImage?.getAttribute("src")).toBeTruthy();

        expect(root.textContent).toContain("Clientes verificados");
        expect(root.textContent).toContain("Calificaciones mutuas");
        expect(root.textContent).toContain("Registro geolocalizado");
        expect(root.textContent).toContain("Trato y pago directo");
    });

    it("renders the users security section with title, image, and all 4 cards", () => {
        const root = mountSeguridad();

        expect(
            root.querySelector("h2#security-users-title")?.textContent?.trim(),
        ).toBe("Seguridad para los usuarios");

        const usersImage = root.querySelector(
            'img[alt="Seguridad para los usuarios en Zeivor"]',
        );
        expect(usersImage).not.toBeNull();
        expect(usersImage?.getAttribute("src")).toBeTruthy();

        const cards = root.querySelectorAll("article");
        expect(cards.length).toBe(8);

        expect(root.textContent).toContain("Expertos verificados");
        expect(root.textContent).toContain("Reputación transparente");
        expect(root.textContent).toContain("Acuerdo claro y sin sorpresas");
        expect(root.textContent).toContain("Respaldo y soporte activo");
    });

    it("renders the FAQ section with all 9 numbered questions and alternating alignment", () => {
        const root = mountSeguridad();

        const faqSection = root.querySelector("#preguntas-frecuentes");
        expect(faqSection).not.toBeNull();
        expect(faqSection?.querySelector("h2")?.textContent?.trim()).toBe(
            "Preguntas frecuentes",
        );
        expect(faqSection?.textContent).toContain(
            "Conoce más sobre Zeivor y resuelve tus dudas sobre nuestra plataforma.",
        );

        const faqRows = faqSection?.querySelectorAll(".space-y-6 > div");
        expect(faqRows?.length).toBe(9);

        expect(faqRows?.[0].className).toContain("justify-start");
        expect(faqRows?.[1].className).toContain("justify-end");

        const faqTitles = faqSection?.querySelectorAll("h3");
        expect(faqTitles?.length).toBe(9);
        expect(faqTitles?.[0].textContent?.trim()).toBe(
            "¿Cómo puedo descargar la aplicación?",
        );
        expect(faqTitles?.[8].textContent?.trim()).toBe(
            "¿Cómo puedo realizar pagos dentro de la aplicación?",
        );

        expect(faqSection?.textContent).toContain(
            "Por medio de las tiendas App Store y Google Play Store.",
        );
        expect(faqSection?.textContent).toContain(
            "Puedes realizar tus pagos a través de PayPal y Stripe.",
        );
    });
});
