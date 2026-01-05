<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    reasignaciones: Array,
});
</script>

<template>
    <Head title="Reasignación de Activos" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Reasignación de Activos
                </h2>
                <Link
                    :href="route('reasignaciones.create')"
                    class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500"
                >
                    Nueva Reasignación
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
                    <div class="p-6">
                        <!-- Tabla de Reasignaciones -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">ID</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Activo</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Ubicación</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Asignación</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Fecha</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Usuario</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                                    <tr v-for="reasignacion in reasignaciones" :key="reasignacion.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">
                                            #{{ reasignacion.id }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4">
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ reasignacion.activo?.codigo }}
                                            </div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ reasignacion.activo?.descripcion }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm text-gray-900 dark:text-gray-100">
                                                <span class="text-gray-500 dark:text-gray-400">De:</span> {{ reasignacion.ubicacion_anterior || 'N/A' }}
                                            </div>
                                            <div class="text-sm text-green-600 dark:text-green-400">
                                                <span class="text-gray-500 dark:text-gray-400">A:</span> {{ reasignacion.ubicacion_nueva || 'N/A' }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm text-gray-900 dark:text-gray-100">
                                                <span class="text-gray-500 dark:text-gray-400">De:</span> {{ reasignacion.responsable_anterior || 'N/A' }}
                                            </div>
                                            <div class="text-sm text-green-600 dark:text-green-400">
                                                <span class="text-gray-500 dark:text-gray-400">A:</span> {{ reasignacion.responsable_nuevo || 'N/A' }}
                                            </div>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                            {{ reasignacion.fecha }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                            {{ reasignacion.usuario }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm font-medium space-x-3">
                                            <Link
                                                :href="route('reasignaciones.show', reasignacion.id)"
                                                class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
                                            >
                                                Ver
                                            </Link>
                                            <Link
                                                v-if="reasignacion.activo?.id"
                                                :href="route('activos.trazabilidad') + '?activo_id=' + reasignacion.activo.id"
                                                class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300"
                                                title="Ver trazabilidad del activo"
                                            >
                                                Trazabilidad
                                            </Link>
                                            <a
                                                :href="route('reasignaciones.acta-pdf', reasignacion.id)"
                                                target="_blank"
                                                class="text-purple-600 hover:text-purple-900 dark:text-purple-400 dark:hover:text-purple-300"
                                                title="Descargar Acta de Reasignación"
                                            >
                                                Acta PDF
                                            </a>
                                        </td>
                                    </tr>
                                    <tr v-if="!reasignaciones || reasignaciones.length === 0">
                                        <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                            No hay reasignaciones registradas
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
