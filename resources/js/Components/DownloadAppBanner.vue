<script setup lang="ts">
interface Props {
    appStoreUrl?: string;
    googlePlayUrl?: string;
    brandName?: string;
}

withDefaults(defineProps<Props>(), {
    appStoreUrl: "#",
    googlePlayUrl: "#",
    brandName: "ZEIVOR",
});
</script>

<template>
    <section
        aria-labelledby="download-app-title"
        class="relative w-full overflow-hidden bg-[#1e2d4a] px-4 py-12 sm:px-6 lg:px-8"
    >
        <div
            class="relative mx-auto flex max-w-6xl flex-col items-center justify-between gap-8 md:flex-row"
        >
            <!-- Texto principal -->
            <h2
                id="download-app-title"
                class="text-center text-3xl font-medium leading-tight tracking-tight text-white sm:text-4xl md:text-left lg:text-[42px]"
            >
                Descarga la APP
                <span class="block">
                    <span class="font-semibold text-[#ff4b63]">{{
                        brandName
                    }}</span>
                    ¡gratis!
                </span>
            </h2>

            <!-- Botones de tiendas -->
            <div
                class="flex flex-wrap items-center justify-center gap-4 sm:gap-5"
            >
                <!-- App Store -->
                <a
                    :href="appStoreUrl"
                    target="_blank"
                    rel="noopener noreferrer"
                    aria-label="Descárgalo en App Store"
                    class="inline-flex min-w-[205px] items-center gap-3.5 rounded-lg bg-white px-5 py-3 shadow-sm transition hover:-translate-y-0.5 hover:bg-neutral-50 focus:outline-none focus:ring-2 focus:ring-[#ff4b63] focus:ring-offset-2 focus:ring-offset-[#1e2d4a]"
                >
                    <svg
                        class="h-8 w-8 shrink-0 text-[#ff4b63]"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.6"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        aria-hidden="true"
                    >
                        <path
                            d="M16.5 12.6c0-2.3 1.9-3.4 2-3.5-1.1-1.6-2.8-1.8-3.4-1.9-1.4-.1-2.8.8-3.5.8-.7 0-1.9-.8-3.1-.8-1.6 0-3 .9-3.8 2.3-1.6 2.8-.4 7 1.1 9.3.8 1.1 1.7 2.4 2.9 2.3 1.2 0 1.6-.8 3-.8 1.4 0 1.8.8 3 .8 1.3 0 2.1-1.1 2.8-2.3.9-1.3 1.3-2.6 1.3-2.7-.1 0-2.3-.9-2.3-3.5Z"
                        />
                        <path
                            d="M14.2 5.3c.6-.8 1.1-1.9.9-3-.9 0-2.1.6-2.7 1.4-.6.7-1.1 1.8-.9 2.9 1 .1 2.1-.5 2.7-1.3Z"
                        />
                    </svg>

                    <span class="flex flex-col text-left leading-tight">
                        <span class="text-xs font-medium text-[#1e2d4a]"
                            >Descárgalo en</span
                        >
                        <span
                            class="mt-0.5 text-sm font-extrabold tracking-wide text-[#1e2d4a]"
                        >
                            APP STORE
                        </span>
                    </span>
                </a>

                <!-- Google Play -->
                <a
                    :href="googlePlayUrl"
                    target="_blank"
                    rel="noopener noreferrer"
                    aria-label="Descárgalo en Google Play"
                    class="inline-flex min-w-[205px] items-center gap-3.5 rounded-lg bg-white px-5 py-3 shadow-sm transition hover:-translate-y-0.5 hover:bg-neutral-50 focus:outline-none focus:ring-2 focus:ring-[#ff4b63] focus:ring-offset-2 focus:ring-offset-[#1e2d4a]"
                >
                    <svg
                        class="h-8 w-8 shrink-0 text-[#ff4b63]"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.6"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        aria-hidden="true"
                    >
                        <path
                            d="M4.5 3.5v17a1 1 0 0 0 1.5.86l14.2-8.5a1 1 0 0 0 0-1.72L6 2.64a1 1 0 0 0-1.5.86Z"
                        />
                        <path d="M5 3.2l11.2 11.2" />
                        <path d="M5 20.8l11.2-11.2" />
                    </svg>

                    <span class="flex flex-col text-left leading-tight">
                        <span class="text-xs font-medium text-[#1e2d4a]"
                            >Descárgalo en</span
                        >
                        <span
                            class="mt-0.5 text-sm font-extrabold tracking-wide text-[#1e2d4a]"
                        >
                            GOOGLE PLAY
                        </span>
                    </span>
                </a>
            </div>
        </div>
    </section>
</template>
