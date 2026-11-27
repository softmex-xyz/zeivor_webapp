<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';

const form = useForm({
    email: '',
    password: '',
});

function submit() {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    });
}
</script>

<template>
    <main>
        <h1>Iniciar sesión</h1>
        <form @submit.prevent="submit">
            <label for="email">Correo electrónico</label>
            <input id="email" v-model="form.email" type="email" autocomplete="username" required>
            <p v-if="form.errors.email" role="alert">{{ form.errors.email }}</p>

            <label for="password">Contraseña</label>
            <input id="password" v-model="form.password" type="password" autocomplete="current-password" required>
            <p v-if="form.errors.password" role="alert">{{ form.errors.password }}</p>

            <button type="submit" :disabled="form.processing">Ingresar</button>
        </form>
    </main>
</template>
