<script setup lang="ts">
import Footer from '../Components/Footer.vue';
import NavBar from '../Components/NavBar.vue';

interface Props {
    currentPath?: string;
}

withDefaults(defineProps<Props>(), {
    currentPath: '/',
});
</script>

<template>
    <div class="min-h-screen flex flex-col bg-gray-50 text-[#222B3A]">
        <NavBar :current-path="currentPath" />

        <!-- Contenido dinámico de cada página -->
        <main class="flex-1">
            <slot />
        </main>

        <!-- Tu Footer importado -->
        <Footer />
    </div>
</template>
