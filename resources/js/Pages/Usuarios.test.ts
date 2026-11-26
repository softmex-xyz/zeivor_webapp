import { afterEach, describe, expect, it, vi } from "vitest";
import { createApp, type App } from "vue";
import Usuarios from "./Usuarios.vue";

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

function mountUsuarios(): HTMLDivElement {
    container = document.createElement("div");
    document.body.append(container);

    app = createApp(Usuarios);
    app.mount(container);

    return container;
}

describe("Usuarios", () => {
    it("renders within AppLayout and marks the nav link active", () => {
        const root = mountUsuarios();

        expect(root.querySelector("nav")).not.toBeNull();
        expect(root.querySelector("main")).not.toBeNull();
        expect(root.querySelector("footer")).not.toBeNull();

        const usuariosLink = root.querySelector('nav ul a[href="/usuarios"]');
        expect(usuariosLink?.className).toContain("text-[#E85C63]");
        expect(usuariosLink?.className).toContain("font-semibold");
    });

    it("renders the page heading", () => {
        const root = mountUsuarios();

        expect(
            root.querySelector("h1#app-usage-title")?.textContent?.trim(),
        ).toBe("Uso de la aplicación");
    });

    it("renders each usage step with its own illustration, title, and description", () => {
        const root = mountUsuarios();

        const articles = root.querySelectorAll("main article");
        expect(articles.length).toBe(4);

        const expectedSteps = [
            {
                title: "Descarga y regístrate",
                description:
                    "Baja la app gratis en tu celular, crea tu perfil en minutos y activa tu ubicación",
            },
            {
                title: "Busca lo que necesitas:",
                description:
                    "Busca el oficio o profesión que requieres y explora a los expertos verificados más cercanos a ti",
            },
            {
                title: "Conecta y acuerda:",
                description:
                    "Envía tu solicitud o espera que el experto te contacte, revisa las calificaciones del profesional y define directamente con él los detalles, horario y costo del servicio.",
            },
            {
                title: "Resuelve y califica:",
                description:
                    "Recibe al experto en tu hogar u oficina, realiza el pago directo al finalizar y califica tu experiencia para ayudar a la comunidad.",
            },
        ];

        articles.forEach((article, index) => {
            const img = article.querySelector("img");
            const title = article.querySelector("h2");
            const description = article.querySelector("p");

            expect(img).not.toBeNull();
            expect(img?.getAttribute("src")).toBeTruthy();
            expect(img?.getAttribute("alt")).toBe(expectedSteps[index].title);
            expect(title?.textContent?.trim()).toBe(expectedSteps[index].title);
            expect(description?.textContent?.trim()).toBe(
                expectedSteps[index].description,
            );
        });
    });

    it("alternates image and text order on odd-indexed steps", () => {
        const root = mountUsuarios();

        const articles = root.querySelectorAll("main article");
        const firstImageContainer = articles[0].children[0];
        const secondImageContainer = articles[1].children[0];
        const secondTextContainer = articles[1].children[1];

        expect(firstImageContainer.className).not.toContain("md:order-2");
        expect(secondImageContainer.className).toContain("md:order-2");
        expect(secondTextContainer.className).toContain("md:order-1");
    });
});

