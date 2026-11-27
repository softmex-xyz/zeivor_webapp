<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import logoZeivor from '../../images/Logo-Zeivor.png';

interface Props {
    logoSrc?: string;
    appDownloadUrl?: string;
    socialLinks?: {
        facebook?: string;
        instagram?: string;
        youtube?: string;
        tiktok?: string;
    };
}

withDefaults(defineProps<Props>(), {
    logoSrc: logoZeivor,
    appDownloadUrl: '#descargar-app',
    socialLinks: () => ({
        facebook: 'https://facebook.com',
        instagram: 'https://instagram.com',
        youtube: 'https://youtube.com',
        tiktok: 'https://tiktok.com',
    }),
});
</script>

<template>
    <footer class="relative w-full bg-white border-t border-gray-100 text-[#222B3A]">
        <div class="max-w-7xl mx-auto px-6 pt-8 pb-20 md:py-8 flex flex-col md:grid md:grid-cols-[1fr_auto_1fr] items-center gap-6">
            
            <!-- 1. Izquierda: Logo -->
            <div class="shrink-0 md:justify-self-start">
                <Link href="/" class="inline-flex items-center">
                    <img :src="logoSrc" alt="Zeivor - Soluciones de Expertos" class="h-10 w-auto object-contain" />
                </Link>
            </div>

            <!-- 2. Centro: Copyright y enlaces legales -->
            <div class="text-center text-xs sm:text-sm text-[#374151] space-y-1.5 md:justify-self-center">
                <p>
                    © {{ new Date().getFullYear() }} <span class="text-[#E85C63] font-medium">ZEIVOR</span> - Soluciones de Expertos
                </p>
                <div class="flex flex-wrap items-center justify-center gap-2 text-gray-600">
                    <Link href="/aviso-de-privacidad" class="hover:text-[#E85C63] transition-colors">
                        Aviso de Privacidad
                    </Link>
                    <span class="text-gray-400" aria-hidden="true">|</span>
                    <Link href="/terminos-y-condiciones" class="hover:text-[#E85C63] transition-colors">
                        Términos y Condiciones
                    </Link>
                </div>
            </div>

            <!-- 3. Derecha: Redes sociales -->
            <div class="flex items-center gap-5 text-[#E85C63] md:justify-self-end md:pr-16">
                <a
                    v-if="socialLinks.facebook"
                    :href="socialLinks.facebook"
                    target="_blank"
                    rel="noopener noreferrer"
                    aria-label="Facebook"
                    class="hover:opacity-75 transition-opacity"
                >
                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                        <path d="M14 13.5h2.5l1-4H14v-2c0-1.03 0-2 2-2h1.5V2.14c-.326-.043-1.557-.14-2.857-.14C11.928 2 10 3.657 10 6.7v2.8H7v4h3V22h4v-8.5z"/>
                    </svg>
                </a>

                <a
                    v-if="socialLinks.instagram"
                    :href="socialLinks.instagram"
                    target="_blank"
                    rel="noopener noreferrer"
                    aria-label="Instagram"
                    class="hover:opacity-75 transition-opacity"
                >
                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                        <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                    </svg>
                </a>

                <a
                    v-if="socialLinks.youtube"
                    :href="socialLinks.youtube"
                    target="_blank"
                    rel="noopener noreferrer"
                    aria-label="YouTube"
                    class="hover:opacity-75 transition-opacity"
                >
                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                        <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                    </svg>
                </a>

                <a
                    v-if="socialLinks.tiktok"
                    :href="socialLinks.tiktok"
                    target="_blank"
                    rel="noopener noreferrer"
                    aria-label="TikTok"
                    class="hover:opacity-75 transition-opacity"
                >
                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                        <path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64 2.93 2.93 0 0 1 .88.13V9.4a6.84 6.84 0 0 0-1-.05A6.33 6.33 0 0 0 5 20.1a6.34 6.34 0 0 0 10.86-4.43v-7a8.16 8.16 0 0 0 4.77 1.52v-3.4a4.85 4.85 0 0 1-1-.1z"/>
                    </svg>
                </a>
            </div>
        </div>        
    </footer>
</template>
