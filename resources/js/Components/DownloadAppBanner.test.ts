import { afterEach, describe, expect, it } from "vitest";
import { createApp, type App } from "vue";
import DownloadAppBanner from "./DownloadAppBanner.vue";

let app: App<Element> | undefined;
let container: HTMLDivElement | undefined;

afterEach(() => {
    app?.unmount();
    container?.remove();
    app = undefined;
    container = undefined;
});

describe("DownloadAppBanner", () => {
    it("renders heading and App Store / Google Play links", () => {
        container = document.createElement("div");
        document.body.append(container);
        app = createApp(DownloadAppBanner, {
            appStoreUrl: "https://apps.apple.com/app/zeivor",
            googlePlayUrl:
                "https://play.google.com/store/apps/details?id=mx.posibilidades.seivor",
        });
        app.mount(container);

        expect(container.textContent).toContain("Descarga la APP");
        expect(container.textContent).toContain("ZEIVOR");
        expect(container.textContent).toContain("¡gratis!");

        const appStoreLink = container.querySelector(
            'a[aria-label="Descárgalo en App Store"]',
        );
        const googlePlayLink = container.querySelector(
            'a[aria-label="Descárgalo en Google Play"]',
        );

        expect(appStoreLink?.getAttribute("href")).toBe(
            "https://apps.apple.com/app/zeivor",
        );
        expect(googlePlayLink?.getAttribute("href")).toBe(
            "https://play.google.com/store/apps/details?id=mx.posibilidades.seivor",
        );
    });
});
