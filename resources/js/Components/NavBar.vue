<script setup lang="ts">
import { Link } from "@inertiajs/vue3";
import { onBeforeUnmount, onMounted, ref } from "vue";
import logoZeivor from "../../images/Logo-Zeivor.png";

export interface NavLinkItem {
    label: string;
    href: string;
}

interface Props {
    logoSrc?: string;
    links?: NavLinkItem[];
    ctaLabel?: string;
    ctaHref?: string;
    secondaryCtaLabel?: string;
    secondaryCtaHref?: string;
    currentPath?: string;
}

const props = withDefaults(defineProps<Props>(), {
    logoSrc: logoZeivor,
    links: () => [
        { label: "Inicio", href: "/" },
        { label: "Seguridad", href: "#seguridad" },
        { label: "Contacto", href: "#contacto" },
        { label: "Usuarios", href: "#usuarios" },
        { label: "¿Eres un experto?", href: "/experto" },
    ],
    currentPath: "/",
});

const isMenuOpen = ref(false);

const toggleMenu = (): void => {
    isMenuOpen.value = !isMenuOpen.value;
};

const closeMenu = (): void => {
    isMenuOpen.value = false;
};

const isHashLink = (href: string): boolean => href.startsWith("#");

const isActiveLink = (href: string): boolean => {
    if (isHashLink(href)) {
        return false;
    }
    return props.currentPath === href;
};

const handleKeydown = (event: KeyboardEvent): void => {
    if (event.key === "Escape" && isMenuOpen.value) {
        closeMenu();
    }
};

onMounted(() => {
    window.addEventListener("keydown", handleKeydown);
});

onBeforeUnmount(() => {
    window.removeEventListener("keydown", handleKeydown);
});
</script>

<template>
    <header
        class="sticky top-0 z-40 w-full bg-white/95 backdrop-blur-sm border-b border-gray-100 text-[#222B3A]"
    >
        <nav
            aria-label="Navegación principal"
            class="max-w-7xl mx-auto px-6 h-16 md:h-20 flex items-center justify-between gap-6"
        >
            <!-- Logo -->
            <Link
                href="/"
                class="inline-flex items-center shrink-0 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#E85C63] rounded-md"
                @click="closeMenu"
            >
                <img
                    :src="logoSrc"
                    alt="Zeivor - Soluciones de Expertos"
                    class="h-9 md:h-10 w-auto object-contain"
                />
            </Link>

            <!-- Enlaces de navegación (Desktop) -->
            <ul class="hidden md:flex items-center gap-8 text-sm font-medium">
                <li v-for="item in links" :key="item.href + item.label">
                    <component
                        :is="isHashLink(item.href) ? 'a' : Link"
                        :href="item.href"
                        :class="[
                            'transition-colors hover:text-[#E85C63] py-1',
                            isActiveLink(item.href)
                                ? 'text-[#E85C63] font-semibold'
                                : 'text-[#222B3A]',
                        ]"
                    >
                        {{ item.label }}
                    </component>
                </li>
            </ul>

            <button
                type="button"
                class="md:hidden inline-flex items-center justify-center p-2 rounded-lg text-[#222B3A] hover:text-[#E85C63] hover:bg-gray-100 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-[#E85C63]"
                :aria-expanded="isMenuOpen"
                aria-controls="mobile-nav-menu"
                :aria-label="isMenuOpen ? 'Cerrar menú' : 'Abrir menú'"
                @click="toggleMenu"
            >
                <svg
                    v-if="!isMenuOpen"
                    class="w-6 h-6"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    viewBox="0 0 24 24"
                    aria-hidden="true"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M4 6h16M4 12h16M4 18h16"
                    />
                </svg>
                <svg
                    v-else
                    class="w-6 h-6"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    viewBox="0 0 24 24"
                    aria-hidden="true"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M6 18L18 6M6 6l12 12"
                    />
                </svg>
            </button>
        </nav>

        <!-- Menú desplegable (Móvil) -->
        <div
            v-if="isMenuOpen"
            id="mobile-nav-menu"
            class="md:hidden border-t border-gray-100 bg-white px-6 py-4 space-y-4 shadow-sm"
        >
            <ul class="space-y-1">
                <li
                    v-for="item in links"
                    :key="'mobile-' + item.href + item.label"
                >
                    <component
                        :is="isHashLink(item.href) ? 'a' : Link"
                        :href="item.href"
                        :class="[
                            'block rounded-lg px-3 py-2.5 text-base font-medium transition-colors hover:bg-gray-50 hover:text-[#E85C63]',
                            isActiveLink(item.href)
                                ? 'text-[#E85C63] bg-[#E85C63]/5 font-semibold'
                                : 'text-[#222B3A]',
                        ]"
                        @click="closeMenu"
                    >
                        {{ item.label }}
                    </component>
                </li>
            </ul>
        </div>
    </header>
</template>
