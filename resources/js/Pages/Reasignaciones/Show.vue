<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    reasignacion: Object,
});
</script>

<template>
    <Head title="Detalle de Reasignación" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Detalle de Reasignación
                </h2>
                <Link :href="route('reasignaciones.index')" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                    Volver
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
                    <div class="p-6 space-y-6">
                        <!-- Información del Activo -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Activo</h3>
                            <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-700">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Código:</span>
                                        <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ reasignacion.activo?.codigo }}</p>
                                    </div>
                                    <div>
                                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Descripción:</span>
                                        <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ reasignacion.activo?.descripcion }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Comparación Antes/Después -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Cambios Realizados</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Campo</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Anterior</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Nuevo</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                                        <tr>
                                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">Ubicación</td>
                                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ reasignacion.ubicacion_anterior?.nombre || 'Sin asignar' }}</td>
                                            <td class="px-6 py-4 text-sm text-green-600 dark:text-green-400">{{ reasignacion.ubicacion_nueva?.nombre || 'Sin asignar' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">Responsable</td>
                                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                                {{ reasignacion.responsable_anterior ? `${reasignacion.responsable_anterior.nombre} ${reasignacion.responsable_anterior.apellido}` : 'Sin asignar' }}
                                            </td>
                                            <td class="px-6 py-4 text-sm text-green-600 dark:text-green-400">
                                                {{ reasignacion.responsable_nuevo ? `${reasignacion.responsable_nuevo.nombre} ${reasignacion.responsable_nuevo.apellido}` : 'Sin asignar' }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Detalles de la Reasignación -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Detalles</h3>
                            <div class="space-y-4">
                                <div>
                                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Fecha de Reasignación:</span>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ new Date(reasignacion.fecha_reasignacion).toLocaleDateString() }}</p>
                                </div>
                                <div>
                                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Motivo:</span>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ reasignacion.motivo }}</p>
                                </div>
                                <div v-if="reasignacion.observaciones">
                                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Observaciones:</span>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ reasignacion.observaciones }}</p>
                                </div>
                                <div>
                                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Realizado por:</span>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ reasignacion.usuario?.nombre }} {{ reasignacion.usuario?.apellido }}</p>
                                </div>
                                <div>
                                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Fecha de registro:</span>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ new Date(reasignacion.created_at).toLocaleString() }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
