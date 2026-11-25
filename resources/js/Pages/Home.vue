<script setup lang="ts">
defineProps<{
    mensaje: string;
}>();
</script>

<template>
    <div style="padding: 2rem; font-family: sans-serif;">
        <h1>Laravel + Inertia + Vue</h1>
        <p>{{ mensaje }}</p>
    </div>
</template>