<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    backups: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();
const can = (permission) => page.props.auth?.user?.permissions?.includes(permission);

const showRestoreModal = ref(false);
const selectedBackup = ref(null);

const restoreForm = useForm({
    password: '',
});

function crearRespaldo() {
    router.post(route('sistema.respaldo.crear'), {}, {
        preserveScroll: true,
    });
}

function descargarRespaldo(backup) {
    window.location.href = route('sistema.respaldo.descargar-sql', backup.id);
}

function abrirModalRestaurar(backup) {
    selectedBackup.value = backup;
    showRestoreModal.value = true;
    restoreForm.reset();
}

function cerrarModalRestaurar() {
    showRestoreModal.value = false;
    selectedBackup.value = null;
    restoreForm.reset();
}

function confirmarRestaurar() {
    if (!selectedBackup.value) return;
    
    restoreForm.post(route('sistema.respaldo.restaurar', selectedBackup.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            cerrarModalRestaurar();
        },
    });
}

function eliminarRespaldo(backup) {
    if (confirm('¿Estás seguro de eliminar este respaldo? Esta acción no se puede deshacer.')) {
        router.delete(route('sistema.respaldo.eliminar', backup.id), {
            preserveScroll: true,
        });
    }
}
</script>

<template>
    <Head title="Respaldos" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-100">Respaldos</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Gestión de respaldos y restauración de base de datos.</p>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <!-- Success/Error Messages -->
                <div v-if="$page.props.flash?.success" class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-900/30 dark:text-emerald-200">
                    {{ $page.props.flash.success }}
                </div>
                <div v-if="$page.props.flash?.error" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-800 dark:border-red-900/50 dark:bg-red-900/30 dark:text-red-200">
                    {{ $page.props.flash.error }}
                </div>

                <!-- Manual Backup Section -->
                <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Crear respaldo manual</h3>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Genera un respaldo completo de la base de datos en formato SQL.</p>
                        </div>
                        <button
                            v-if="can('respaldos.download')"
                            @click="crearRespaldo"
                            class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                            </svg>
                            Crear respaldo ahora
                        </button>
                    </div>

                    <div class="mt-4 flex items-center gap-4">
                        <a
                            v-if="can('respaldos.download')"
                            :href="route('sistema.respaldo.descargar')"
                            class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Descargar respaldo JSON (catálogos)
                        </a>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Exporta catálogos y activos en formato JSON.</p>
                    </div>
                </div>

                <!-- Backup History -->
                <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Historial de respaldos</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ backups.length }} respaldos disponibles</p>
                    </div>

                    <div v-if="backups.length === 0" class="px-6 py-12 text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                        </svg>
                        <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">No hay respaldos disponibles</p>
                        <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Crea tu primer respaldo usando el botón de arriba</p>
                    </div>

                    <div v-else class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Fecha</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Archivo</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Tamaño</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Tipo</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Estado</th>
                                    <th class="px-6 py-3 text-right text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                                <tr v-for="backup in backups" :key="backup.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                        {{ backup.created_at }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                                        {{ backup.filename }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                                        {{ backup.human_size }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold capitalize" :class="backup.type === 'manual' ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400' : 'bg-sky-50 text-sky-600 dark:bg-sky-900/30 dark:text-sky-400'">
                                            {{ backup.type }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold" :class="backup.status === 'completed' ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400' : backup.status === 'failed' ? 'bg-red-50 text-red-600 dark:bg-red-900/30 dark:text-red-400' : 'bg-amber-50 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400'">
                                            {{ backup.status === 'completed' ? 'Completado' : backup.status === 'failed' ? 'Fallido' : 'En progreso' }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                        <div class="flex items-center justify-end gap-2">
                                            <button
                                                v-if="backup.status === 'completed' && can('respaldos.download')"
                                                @click="descargarRespaldo(backup)"
                                                class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
                                                title="Descargar"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                                </svg>
                                            </button>
                                            <button
                                                v-if="backup.status === 'completed' && can('respaldos.download')"
                                                @click="abrirModalRestaurar(backup)"
                                                class="text-emerald-600 hover:text-emerald-900 dark:text-emerald-400 dark:hover:text-emerald-300"
                                                title="Restaurar"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                                </svg>
                                            </button>
                                            <button
                                                v-if="can('respaldos.download')"
                                                @click="eliminarRespaldo(backup)"
                                                class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                                title="Eliminar"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Restore Confirmation Modal -->
        <div v-if="showRestoreModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="w-full max-w-md rounded-xl border border-gray-200 bg-white p-6 shadow-xl dark:border-gray-700 dark:bg-gray-800">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0 rounded-full bg-amber-100 p-3 dark:bg-amber-900/30">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">¿Restaurar base de datos?</h3>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                            Esta acción <strong>sobrescribirá todos los datos actuales</strong> de la base de datos con el respaldo seleccionado. Esta operación no se puede deshacer.
                        </p>
                        <p v-if="selectedBackup" class="mt-2 text-sm font-semibold text-gray-700 dark:text-gray-300">
                            Respaldo: {{ selectedBackup.filename }}
                        </p>

                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Confirma tu contraseña</label>
                            <input
                                v-model="restoreForm.password"
                                type="password"
                                class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                placeholder="Ingresa tu contraseña"
                                @keyup.enter="confirmarRestaurar"
                            />
                            <p v-if="restoreForm.errors.password" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ restoreForm.errors.password }}</p>
                        </div>

                        <div class="mt-6 flex gap-3">
                            <button
                                @click="cerrarModalRestaurar"
                                type="button"
                                class="flex-1 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600"
                            >
                                Cancelar
                            </button>
                            <button
                                @click="confirmarRestaurar"
                                :disabled="restoreForm.processing || !restoreForm.password"
                                class="flex-1 rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-amber-500 disabled:opacity-50"
                            >
                                {{ restoreForm.processing ? 'Restaurando...' : 'Restaurar' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
