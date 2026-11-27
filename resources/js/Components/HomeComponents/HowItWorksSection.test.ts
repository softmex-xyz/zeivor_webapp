import { afterEach, describe, expect, it } from "vitest";
import { createApp, type App } from "vue";
import HowItWorksSection from "./HowItWorksSection.vue";

let app: App<Element> | undefined;
let container: HTMLDivElement | undefined;

afterEach(() => {
    app?.unmount();
    container?.remove();
    app = undefined;
    container = undefined;
});

function mountHowItWorks() {
    container = document.createElement("div");
    document.body.append(container);
    app = createApp(HowItWorksSection);
    app.mount(container);
    return container;
}

describe("HowItWorksSection", () => {
    it("renders the main title", () => {
        const root = mountHowItWorks();

        const title = root.querySelector("h2");

        expect(title).not.toBeNull();
        expect(title?.textContent).toContain("¿Cómo funciona Zeivor?");
    });

    it("renders the two main images with their alt attributes", () => {
        const root = mountHowItWorks();

        const images = root.querySelectorAll("img");

        expect(images.length).toBe(5);

        expect(images[0].getAttribute("alt")).toBe(
            "Conoce a los profesionales de Zeivor",
        );

        expect(images[1].getAttribute("alt")).toBe(
            "Encuentra al experto ideal en Zeivor",
        );
    });

    it("renders the descriptive text about Zeivor", () => {
        const root = mountHowItWorks();

        expect(root.textContent).toContain("En Zeivor");
        expect(root.textContent).toContain("dar a conocer su experiencia");
        expect(root.textContent).toContain("¿Buscas una solución?");
        expect(root.textContent).toContain("conecta con quien puede ayudarte.");
    });

    it("renders the three process steps", () => {
        const root = mountHowItWorks();

        const articles = root.querySelectorAll("article");

        expect(articles.length).toBe(3);
    });

    it("renders the correct title for each step", () => {
        const root = mountHowItWorks();

        const titles = Array.from(root.querySelectorAll("article h3")).map(
            (title) => title.textContent?.trim(),
        );

        expect(titles).toEqual(["REGISTRA", "SOLICITA", "CONTRATA"]);
    });

    it("renders the correct description for each step", () => {
        const root = mountHowItWorks();

        const descriptions = Array.from(root.querySelectorAll("article p")).map(
            (description) => description.textContent?.trim(),
        );

        expect(descriptions).toEqual([
            "El experto en su oficio descarga la APP y se registra.",
            "El usuario solicita un experto desde la APP para realizar el trabajo.",
            "Una vez hecho el acuerdo entre experto y usuario, el pago es directo.",
        ]);
    });

    it("renders an icon for each process step", () => {
        const root = mountHowItWorks();

        const icons = root.querySelectorAll("article img");

        expect(icons.length).toBe(3);

        icons.forEach((icon) => {
            expect(icon.getAttribute("src")).toBeTruthy();
            expect(icon.getAttribute("aria-hidden")).toBe("true");
        });
    });

    it("renders the section with the correct accessibility attributes", () => {
        const root = mountHowItWorks();

        const section = root.querySelector("section");

        expect(section).not.toBeNull();
        expect(section?.getAttribute("aria-labelledby")).toBe(
            "how-it-works-title",
        );

        const title = root.querySelector("#how-it-works-title");

        expect(title).not.toBeNull();
    });

    it("renders all images with alt attributes", () => {
        const root = mountHowItWorks();

        const images = root.querySelectorAll("img");

        images.forEach((img) => {
            expect(img.hasAttribute("alt")).toBe(true);
        });
    });
});
