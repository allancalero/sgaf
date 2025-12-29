<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    activos: Array,
    personalArea: Array,
    miArea: String,
});

const mostrarModalDelegar = ref(false);
const activoSeleccionado = ref(null);

const form = useForm({
    activo_id: null,
    nuevo_responsable_id: null,
    observaciones: '',
});

const abrirModalDelegar = (activo) => {
    activoSeleccionado.value = activo;
    form.activo_id = activo.id;
    form.nuevo_responsable_id = null;
    form.observaciones = '';
    mostrarModalDelegar.value = true;
};

const cerrarModal = () => {
    mostrarModalDelegar.value = false;
    activoSeleccionado.value = null;
    form.reset();
};

const delegarActivo = () => {
    form.post(route('mis-activos.delegar'), {
        preserveScroll: true,
        onSuccess: () => {
            cerrarModal();
        },
    });
};
</script>

<template>
    <Head title="Mis Activos" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Mis Activos Asignados
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Área: {{ miArea }} • {{ activos.length }} activo(s) bajo tu responsabilidad
                </p>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Información -->
                <div class="mb-6 rounded-lg bg-blue-50 p-4 dark:bg-blue-900/20">
                    <div class="flex items-start gap-3">
                        <svg class="mt-0.5 h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-blue-800 dark:text-blue-300">
                                Delega tus activos a otros miembros de tu área
                            </p>
                            <p class="mt-1 text-xs text-blue-700 dark:text-blue-400">
                                Puedes asignar temporalmente tus activos a otros empleados dentro de {{ miArea }}.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Lista de Activos -->
                <div v-if="activos.length > 0" class="space-y-4">
                    <div v-for="activo in activos" :key="activo.id" 
                         class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="p-6">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3">
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                            {{ activo.codigo }}
                                        </h3>
                                        <span class="rounded-full bg-indigo-100 px-3 py-1 text-xs font-medium text-indigo-800 dark:bg-indigo-900/50 dark:text-indigo-300">
                                            {{ activo.clasificacion?.nombre || 'Sin clasificar' }}
                                        </span>
                                    </div>
                                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                        {{ activo.descripcion }}
                                    </p>
                                    <div class="mt-4 grid gap-3 sm:grid-cols-3">
                                        <div>
                                            <span class="text-xs text-gray-500 dark:text-gray-400">Área:</span>
                                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ activo.area?.nombre || '-' }}
                                            </p>
                                        </div>
                                        <div>
                                            <span class="text-xs text-gray-500 dark:text-gray-400">Ubicación:</span>
                                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ activo.ubicacionRelacion?.nombre || '-' }}
                                            </p>
                                        </div>
                                        <div>
                                            <span class="text-xs text-gray-500 dark:text-gray-400">Estado:</span>
                                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ activo.estado }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <button
                                    @click="abrirModalDelegar(activo)"
                                    class="inline-flex items-center gap-2 rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none dark:bg-indigo-500"
                                >
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                    </svg>
                                    Delegar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sin activos -->
                <div v-else class="rounded-xl border border-gray-200 bg-white p-12 text-center shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                    <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-gray-100">No tienes activos asignados</h3>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Actualmente no tienes activos bajo tu responsabilidad.</p>
                </div>
            </div>
        </div>

        <!-- Modal Delegar -->
        <div v-if="mostrarModalDelegar" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-screen items-end justify-center px-4 pb-20 pt-4 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="cerrarModal"></div>
                <span class="hidden sm:inline-block sm:h-screen sm:align-middle">&#8203;</span>
                <div class="inline-block transform overflow-hidden rounded-lg bg-white text-left align-bottom shadow-xl transition-all dark:bg-gray-800 sm:my-8 sm:w-full sm:max-w-lg sm:align-middle">
                    <form @submit.prevent="delegarActivo">
                        <div class="bg-white px-4 pb-4 pt-5 dark:bg-gray-800 sm:p-6">
                            <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100">
                                Delegar Activo
                            </h3>
                            <div class="mt-4">
                                <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-700/50">
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ activoSeleccionado?.codigo }}
                                    </p>
                                    <p class="text-xs text-gray-600 dark:text-gray-400">
                                        {{ activoSeleccionado?.descripcion }}
                                    </p>
                                </div>
                            </div>

                            <div class="mt-4">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Delegar a *
                                </label>
                                <select
                                    v-model="form.nuevo_responsable_id"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                >
                                    <option :value="null">Selecciona un empleado</option>
                                    <option v-for="persona in personalArea" :key="persona.id" :value="persona.id">
                                        {{ persona.nombre_completo }} - {{ persona.cargo }}
                                    </option>
                                </select>
                                <p v-if="form.errors.nuevo_responsable_id" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.nuevo_responsable_id }}
                                </p>
                            </div>

                            <div class="mt-4">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Observaciones
                                </label>
                                <textarea
                                    v-model="form.observaciones"
                                    rows="3"
                                    placeholder="Motivo de la delegación..."
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                ></textarea>
                            </div>
                        </div>

                        <div class="bg-gray-50 px-4 py-3 dark:bg-gray-900 sm:flex sm:flex-row-reverse sm:px-6">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="inline-flex w-full justify-center rounded-md bg-indigo-600 px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-indigo-700 disabled:opacity-50 sm:ml-3 sm:w-auto sm:text-sm"
                            >
                                {{ form.processing ? 'Delegando...' : 'Delegar Activo' }}
                            </button>
                            <button
                                type="button"
                                @click="cerrarModal"
                                class="mt-3 inline-flex w-full justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-base font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 sm:mt-0 sm:w-auto sm:text-sm"
                            >
                                Cancelar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
