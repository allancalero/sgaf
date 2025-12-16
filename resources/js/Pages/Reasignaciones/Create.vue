<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    activos: Array,
    ubicaciones: Array,
    personal: Array,
});

const form = useForm({
    activo_id: null,
    ubicacion_nueva_id: null,
    responsable_nuevo_id: null,
    motivo: '',
    observaciones: '',
    fecha_reasignacion: new Date().toISOString().split('T')[0],
});

const activoSeleccionado = ref(null);

watch(() => form.activo_id, (newVal) => {
    activoSeleccionado.value = props.activos.find(a => a.id === newVal);
    if (activoSeleccionado.value) {
        form.ubicacion_nueva_id = activoSeleccionado.value.ubicacion_actual_id;
        form.responsable_nuevo_id = activoSeleccionado.value.responsable_actual_id;
    }
});

const submit = () => {
    form.post(route('reasignaciones.store'));
};
</script>

<template>
    <Head title="Nueva Reasignación" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Nueva Reasignación de Activo
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
                    <form @submit.prevent="submit" class="p-6 space-y-6">
                        <!-- Activo -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Activo a Reasignar *</label>
                            <select v-model="form.activo_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                <option :value="null">Seleccione un activo</option>
                                <option v-for="activo in activos" :key="activo.id" :value="activo.id">
                                    {{ activo.codigo }} - {{ activo.descripcion }}
                                </option>
                            </select>
                            <p v-if="form.errors.activo_id" class="mt-1 text-sm text-red-600">{{ form.errors.activo_id }}</p>
                        </div>

                        <!-- Info actual del activo -->
                        <div v-if="activoSeleccionado" class="rounded-lg bg-gray-50 p-4 dark:bg-gray-700">
                            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Ubicación y Responsable Actuales</h3>
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400">Ubicación:</span>
                                    <span class="ml-2 text-gray-900 dark:text-gray-100">{{ activoSeleccionado.ubicacion_actual || 'Sin asignar' }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400">Responsable:</span>
                                    <span class="ml-2 text-gray-900 dark:text-gray-100">{{ activoSeleccionado.responsable_actual || 'Sin asignar' }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Nueva Ubicación -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nueva Ubicación</label>
                            <select v-model="form.ubicacion_nueva_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                <option :value="null">Sin cambios</option>
                                <option v-for="ubicacion in ubicaciones" :key="ubicacion.id" :value="ubicacion.id">
                                    {{ ubicacion.nombre }}
                                </option>
                            </select>
                        </div>

                        <!-- Nuevo Responsable -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nuevo Responsable</label>
                            <select v-model="form.responsable_nuevo_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                <option :value="null">Sin cambios</option>
                                <option v-for="persona in personal" :key="persona.id" :value="persona.id">
                                    {{ persona.nombre_completo }}
                                </option>
                            </select>
                        </div>

                        <!-- Fecha -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Fecha de Reasignación *</label>
                            <input type="date" v-model="form.fecha_reasignacion" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
                        </div>

                        <!-- Motivo -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Motivo de Reasignación *</label>
                            <textarea v-model="form.motivo" required rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"></textarea>
                            <p v-if="form.errors.motivo" class="mt-1 text-sm text-red-600">{{ form.errors.motivo }}</p>
                        </div>

                        <!-- Observaciones -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Observaciones</label>
                            <textarea v-model="form.observaciones" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"></textarea>
                        </div>

                        <!-- Botones -->
                        <div class="flex justify-end gap-3">
                            <a :href="route('reasignaciones.index')" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
                                Cancelar
                            </a>
                            <button type="submit" :disabled="form.processing" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 disabled:opacity-50">
                                {{ form.processing ? 'Guardando...' : 'Guardar Reasignación' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
