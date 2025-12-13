<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const can = (permission) => page.props.auth.user?.permissions?.includes(permission);
const canRespaldo = computed(() => can('respaldos.download'));
const canSeguridad = computed(() => can('seguridad.manage'));
const canSistema = computed(() => can('sistema.manage'));
</script>

<template>
    <Head title="Sistema" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-100">Sistema</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Centro de administración: respaldo y seguridad.</p>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-5xl space-y-6 sm:px-6 lg:px-8">
                <div v-if="canRespaldo || canSeguridad || canSistema" class="grid gap-4 sm:grid-cols-2">
                    <Link
                        v-if="canRespaldo"
                        :href="route('sistema.respaldo')"
                        class="flex flex-col rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-indigo-300 hover:shadow dark:border-gray-700 dark:bg-gray-800 dark:hover:border-indigo-400"
                    >
                        <span class="text-sm font-semibold text-indigo-600 dark:text-indigo-400">Respaldos</span>
                        <span class="mt-2 text-lg font-semibold text-gray-900 dark:text-gray-100">Copia de seguridad</span>
                        <span class="mt-1 text-sm text-gray-500 dark:text-gray-400">Configura y descarga respaldos de la base y archivos.</span>
                    </Link>

                    <Link
                        v-if="canSeguridad"
                        :href="route('sistema.seguridad')"
                        class="flex flex-col rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-indigo-300 hover:shadow dark:border-gray-700 dark:bg-gray-800 dark:hover:border-indigo-400"
                    >
                        <span class="text-sm font-semibold text-emerald-600 dark:text-emerald-400">Seguridad</span>
                        <span class="mt-2 text-lg font-semibold text-gray-900 dark:text-gray-100">Roles y accesos</span>
                        <span class="mt-1 text-sm text-gray-500 dark:text-gray-400">Define roles, permisos y controles de sesión.</span>
                    </Link>

                    <Link
                        v-if="canSistema"
                        :href="route('sistema.parametros')"
                        class="flex flex-col rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-indigo-300 hover:shadow dark:border-gray-700 dark:bg-gray-800 dark:hover:border-indigo-400"
                    >
                        <span class="text-sm font-semibold text-amber-600 dark:text-amber-400">Parámetros</span>
                        <span class="mt-2 text-lg font-semibold text-gray-900 dark:text-gray-100">Identidad y moneda</span>
                        <span class="mt-1 text-sm text-gray-500 dark:text-gray-400">Configura logo, alcaldía, moneda y año fiscal.</span>
                    </Link>
                </div>
                <div v-else class="rounded-xl border border-dashed border-gray-300 bg-white p-6 text-sm text-gray-700 shadow-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Sin permisos para administrar</h3>
                    <p class="mt-2 dark:text-gray-400">Solicita acceso de respaldo o seguridad para ingresar a estas secciones.</p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
