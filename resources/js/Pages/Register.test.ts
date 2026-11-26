import { afterEach, describe, expect, it, vi } from "vitest";
import { createApp, type App } from "vue";
import Register from "./Register.vue";

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

describe("Register", () => {
    it("renders the empty registration page within AppLayout", () => {
        container = document.createElement("div");
        document.body.append(container);

        app = createApp(Register);
        app.mount(container);

        expect(container.querySelector("nav")).not.toBeNull();
        expect(container.querySelector("main")).not.toBeNull();
        expect(container.querySelector("footer")).not.toBeNull();

        const expertLink = container.querySelector('nav ul a[href="/experto"]');
        expect(expertLink?.className).toContain("text-[#E85C63]");
    });
});

