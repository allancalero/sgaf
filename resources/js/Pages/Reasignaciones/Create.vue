<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';

const props = defineProps({
    activos: Array,
    ubicaciones: Array,
    personal: Array,
    areas: Array,
});

const filtroArea = ref(null);
const filtroResponsable = ref(null);
const busquedaCodigo = ref('');
const activoBuscado = ref(null);

const form = useForm({
    activo_id: null,
    area_nueva_id: null,
    ubicacion_nueva_id: null,
    responsable_nuevo_id: null,
    motivo: '',
    observaciones: '',
    foto_reasignacion: null,
    fecha_reasignacion: new Date().toISOString().split('T')[0],
});

const fotoPreview = ref(null);

const activoSeleccionado = ref(null);
const activosFiltrados = ref(props.activos);

// Filtrar responsables por área seleccionada
const responsablesFiltrados = computed(() => {
    if (!filtroArea.value) {
        return props.personal;
    }
    
    // Obtener todos los activos del área seleccionada
    const activosDelArea = props.activos.filter(a => a.area_id === filtroArea.value);
    
    // Obtener los IDs únicos de responsables de esos activos
    const responsableIds = [...new Set(activosDelArea.map(a => a.responsable_actual_id).filter(id => id !== null))];
    
    // Filtrar el personal para mostrar solo los responsables del área
    return props.personal.filter(p => responsableIds.includes(p.id));
});

// Filtrar responsables para "Nuevo Responsable" según "Nueva Área"
const nuevoResponsableFiltrado = computed(() => {
    if (!form.area_nueva_id) {
        return props.personal;
    }
    
    // Filtrar personal por area_id
    return props.personal.filter(p => p.area_id === form.area_nueva_id);
});

// Watch para resetear responsable cuando cambie el área
watch(filtroArea, () => {
    filtroResponsable.value = null;
});

// Watch para resetear nuevo responsable cuando cambie nueva área
watch(() => form.area_nueva_id, () => {
    form.responsable_nuevo_id = null;
});

watch([filtroArea, filtroResponsable], ([newArea, newResponsable]) => {
    let filtered = props.activos;
    
    // Filtrar por área si está seleccionada
    if (newArea) {
        filtered = filtered.filter(a => a.area_id === newArea);
    }
    
    // Filtrar por responsable si está seleccionado
    if (newResponsable) {
        filtered = filtered.filter(a => a.responsable_actual_id === newResponsable);
    }
    
    activosFiltrados.value = filtered;
    form.activo_id = null;
});

// Watch para buscar por código de inventario
watch(busquedaCodigo, (codigo) => {
    if (!codigo) {
        activoBuscado.value = null;
        return;
    }
    
    const activo = props.activos.find(a => 
        a.codigo?.toLowerCase() === codigo.toLowerCase().trim()
    );
    
    if (activo) {
        activoBuscado.value = activo;
        // Auto-cargar filtros
        filtroArea.value = activo.area_id;
        filtroResponsable.value = activo.responsable_actual_id;
        // Auto-seleccionar el activo encontrado
        form.activo_id = activo.id;
    } else {
        activoBuscado.value = null;
    }
});

watch(() => form.activo_id, (newVal) => {
    activoSeleccionado.value = props.activos.find(a => a.id === newVal);
    if (activoSeleccionado.value) {
        form.area_nueva_id = activoSeleccionado.value.area_id;
        form.ubicacion_nueva_id = activoSeleccionado.value.ubicacion_actual_id;
        form.responsable_nuevo_id = activoSeleccionado.value.responsable_actual_id;
    }
});

const submit = () => {
    form.post(route('reasignaciones.store'));
};

const handleFotoChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.foto_reasignacion = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            fotoPreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const removeFoto = () => {
    form.foto_reasignacion = null;
    fotoPreview.value = null;
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
                        <!-- Filtros -->
                        <div class="rounded-lg bg-indigo-50 p-4 dark:bg-indigo-900/20">
                            <h3 class="text-sm font-semibold text-indigo-700 dark:text-indigo-300 mb-3">🔍 Búsqueda Rápida</h3>
                            
                            <!-- Búsqueda por Código -->
                            <div class="mb-4">
                                <label class="block text-xs font-medium text-indigo-600 dark:text-indigo-400 mb-1">Buscar por Código de Inventario</label>
                                <div class="relative">
                                    <input 
                                        v-model="busquedaCodigo" 
                                        type="text" 
                                        placeholder="Ej: ACT-2024-001"
                                        class="block w-full rounded-md border-indigo-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-indigo-600 dark:bg-gray-700 dark:text-gray-100 text-sm pr-10"
                                    />
                                    <svg v-if="activoBuscado" class="absolute right-3 top-2.5 h-5 w-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <svg v-else-if="busquedaCodigo && !activoBuscado" class="absolute right-3 top-2.5 h-5 w-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <p v-if="activoBuscado" class="mt-1 text-xs text-green-600 dark:text-green-400">
                                    ✓ Activo encontrado: {{ activoBuscado.descripcion }}
                                </p>
                                <p v-else-if="busquedaCodigo && !activoBuscado" class="mt-1 text-xs text-red-600 dark:text-red-400">
                                    ✗ No se encontró el código "{{ busquedaCodigo }}"
                                </p>
                                <p v-else class="mt-1 text-xs text-indigo-600 dark:text-indigo-400">
                                    Escribe el código exacto para carga automática
                                </p>
                            </div>

                            <div class="border-t border-indigo-200 dark:border-indigo-700 pt-4 mt-4">
                                <h4 class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 mb-3">O filtra manualmente:</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Filtro por Área -->
                                <div>
                                    <label class="block text-xs font-medium text-indigo-600 dark:text-indigo-400 mb-1">Filtrar por Área</label>
                                    <select v-model="filtroArea" class="block w-full rounded-md border-indigo-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-indigo-600 dark:bg-gray-700 dark:text-gray-100 text-sm">
                                        <option :value="null">Todas las áreas</option>
                                        <option v-for="area in areas" :key="area.id" :value="area.id">
                                            {{ String(area.id).padStart(2, '0') }} - {{ area.nombre }}
                                        </option>
                                    </select>
                                </div>
                                <!-- Filtro por Responsable -->
                                <div>
                                    <label class="block text-xs font-medium text-indigo-600 dark:text-indigo-400 mb-1">Filtrar por Asignación Actual</label>
                                    <select v-model="filtroResponsable" class="block w-full rounded-md border-indigo-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-indigo-600 dark:bg-gray-700 dark:text-gray-100 text-sm" :disabled="!filtroArea">
                                        <option :value="null">{{ filtroArea ? 'Todos del área' : 'Selecciona un área primero' }}</option>
                                        <option v-for="persona in responsablesFiltrados" :key="persona.id" :value="persona.id">
                                            {{ persona.nombre_completo }}
                                        </option>
                                    </select>
                                    <p v-if="!filtroArea" class="mt-1 text-xs text-amber-600 dark:text-amber-400">Primero selecciona un área</p>
                                    <p v-else-if="responsablesFiltrados.length === 0" class="mt-1 text-xs text-amber-600 dark:text-amber-400">No hay responsables en esta área</p>
                                </div>
                                </div>
                            </div>
                            <p class="mt-2 text-xs text-indigo-600 dark:text-indigo-400">Usa estos filtros para encontrar activos específicos más rápidamente</p>
                        </div>

                        <!-- Activo -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Activo a Reasignar *</label>
                            <select v-model="form.activo_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                <option :value="null">Seleccione un activo</option>
                                <option v-for="activo in activosFiltrados" :key="activo.id" :value="activo.id">
                                    {{ activo.codigo }} - {{ activo.descripcion }}
                                </option>
                            </select>
                            <p v-if="filtroArea || filtroResponsable" class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Mostrando {{ activosFiltrados.length }} activo(s) filtrado(s)
                            </p>
                            <p v-if="form.errors.activo_id" class="mt-1 text-sm text-red-600">{{ form.errors.activo_id }}</p>
                        </div>

                        <!-- Info actual del activo -->
                        <div v-if="activoSeleccionado" class="rounded-lg bg-gray-50 p-4 dark:bg-gray-700">
                            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Información Actual del Activo</h3>
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <!-- Foto del Activo -->
                                <div class="col-span-1 flex justify-center items-center">
                                    <div v-if="activoSeleccionado.foto" class="w-32 h-32 rounded-lg overflow-hidden border-2 border-gray-300 dark:border-gray-600 shadow-sm">
                                        <img :src="activoSeleccionado.foto" :alt="activoSeleccionado.descripcion" class="w-full h-full object-cover" />
                                    </div>
                                    <div v-else class="w-32 h-32 rounded-lg bg-gray-200 dark:bg-gray-600 flex items-center justify-center border-2 border-gray-300 dark:border-gray-500">
                                        <svg class="w-16 h-16 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                </div>
                                <!-- Datos del Activo -->
                                <div class="col-span-1 md:col-span-3 grid grid-cols-3 gap-4 text-sm">
                                    <div>
                                        <span class="text-gray-500 dark:text-gray-400">Área:</span>
                                        <span class="ml-2 text-gray-900 dark:text-gray-100">{{ areas.find(a => a.id === activoSeleccionado.area_id)?.nombre || 'Sin asignar' }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500 dark:text-gray-400">Ubicación:</span>
                                        <span class="ml-2 text-gray-900 dark:text-gray-100">{{ activoSeleccionado.ubicacion_actual || 'Sin asignar' }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500 dark:text-gray-400">Asignado a:</span>
                                        <span class="ml-2 text-gray-900 dark:text-gray-100">{{ activoSeleccionado.responsable_actual || 'Sin asignar' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Nueva Área -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nueva Área</label>
                            <select v-model="form.area_nueva_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                <option :value="null">Sin cambios</option>
                                <option v-for="area in areas" :key="area.id" :value="area.id">
                                    {{ String(area.id).padStart(2, '0') }} - {{ area.nombre }}
                                </option>
                            </select>
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
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nuevo Asignado</label>
                            <select v-model="form.responsable_nuevo_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" :disabled="!form.area_nueva_id">
                                <option :value="null">{{ form.area_nueva_id ? 'Sin cambios' : 'Selecciona un área primero' }}</option>
                                <option v-for="persona in nuevoResponsableFiltrado" :key="persona.id" :value="persona.id">
                                    {{ persona.nombre_completo }}
                                </option>
                            </select>
                            <p v-if="!form.area_nueva_id" class="mt-1 text-xs text-amber-600 dark:text-amber-400">Selecciona primero la nueva área</p>
                            <p v-else-if="nuevoResponsableFiltrado.length === 0" class="mt-1 text-xs text-amber-600 dark:text-amber-400">No hay personal asignado a esta área</p>
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

                        <!-- Foto del Estado Actual -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">📸 Foto del Estado Actual (Opcional)</label>
                            <div class="flex items-center gap-4">
                                <!-- Preview -->
                                <div v-if="fotoPreview" class="relative">
                                    <img :src="fotoPreview" alt="Preview" class="w-32 h-32 object-cover rounded-lg border-2 border-indigo-300 dark:border-indigo-600" />
                                    <button @click="removeFoto" type="button" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                                <!-- Upload Button -->
                                <div class="flex-1">
                                    <label class="flex items-center justify-center px-4 py-2 border-2 border-dashed border-indigo-300 dark:border-indigo-600 rounded-lg cursor-pointer hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition">
                                        <svg class="w-6 h-6 text-indigo-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ fotoPreview ? 'Cambiar foto' : 'Subir foto del estado actual' }}</span>
                                        <input type="file" @change="handleFotoChange" accept="image/*" class="hidden" />
                                    </label>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">PNG, JPG (max. 2MB)</p>
                                </div>
                            </div>
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
