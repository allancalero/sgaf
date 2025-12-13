<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const system = page.props.system || {};
const displayName = system.nombre_alcaldia || 'SGAF';
const displayLogo = system.logo_url || null;
</script>

<template>
    <div class="relative flex min-h-screen items-center justify-center overflow-hidden bg-gradient-to-br from-blue-600 via-cyan-500 to-sky-400 px-4 py-12 sm:px-6 lg:px-8">
        <!-- Animated background elements -->
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute -left-4 top-0 h-72 w-72 animate-blob rounded-full bg-blue-300 opacity-70 mix-blend-multiply blur-xl filter"></div>
            <div class="animation-delay-2000 absolute -right-4 top-0 h-72 w-72 animate-blob rounded-full bg-cyan-300 opacity-70 mix-blend-multiply blur-xl filter"></div>
            <div class="animation-delay-4000 absolute -bottom-8 left-20 h-72 w-72 animate-blob rounded-full bg-sky-300 opacity-70 mix-blend-multiply blur-xl filter"></div>
        </div>

        <!-- Login card -->
        <div class="relative w-full max-w-md">
            <!-- Logo and title -->
            <div class="mb-8 text-center">
                <Link href="/" class="inline-flex flex-col items-center gap-3">
                    <div class="rounded-2xl bg-white p-4 shadow-2xl">
                        <ApplicationLogo v-if="!displayLogo" class="h-16 w-16 fill-current text-indigo-600" />
                        <img v-else :src="displayLogo" alt="Logo" class="h-16 w-16 object-contain" />
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-white drop-shadow-lg">{{ displayName }}</h1>
                        <p class="mt-1 text-sm text-cyan-50">Sistema de Gestión de Activos Fijos</p>
                    </div>
                </Link>
            </div>

            <!-- Main card -->
            <div class="rounded-2xl bg-white/95 p-8 shadow-2xl backdrop-blur-sm">
                <slot />
            </div>

            <!-- Footer text -->
            <p class="mt-6 text-center text-sm text-white/80">
                © {{ new Date().getFullYear() }} {{ displayName }}. Todos los derechos reservados.
            </p>
        </div>
    </div>
</template>

<style scoped>
@keyframes blob {
    0% {
        transform: translate(0px, 0px) scale(1);
    }
    33% {
        transform: translate(30px, -50px) scale(1.1);
    }
    66% {
        transform: translate(-20px, 20px) scale(0.9);
    }
    100% {
        transform: translate(0px, 0px) scale(1);
    }
}

.animate-blob {
    animation: blob 7s infinite;
}

.animation-delay-2000 {
    animation-delay: 2s;
}

.animation-delay-4000 {
    animation-delay: 4s;
}
</style>
