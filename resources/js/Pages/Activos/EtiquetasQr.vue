<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';

const props = defineProps({
    activos: { type: Array, default: () => [] },
    areas: { type: Array, default: () => [] },
    ubicaciones: { type: Array, default: () => [] },
    clasificaciones: { type: Array, default: () => [] },
    personal: { type: Array, default: () => [] },  
    filters: { type: Object, default: () => ({}) },
    areaLocationMap: { type: Object, default: () => ({}) },
});

const filtros = useForm({
    area_id: props.filters?.area_id || '',
    ubicacion_id: props.filters?.ubicacion_id || '',
    personal_id: props.filters?.personal_id || '',
    clasificacion_id: props.filters?.clasificacion_id || '',
    estado: props.filters?.estado || '',
});

const estados = [
    { value: 'BUENO', label: 'Bueno' },
    { value: 'REGULAR', label: 'Regular' },
    { value: 'MALO', label: 'Malo' },
];

// Computed Filters
const filteredPersonal = computed(() => {
    if (!filtros.area_id) return props.personal;
    return props.personal.filter(p => p.area_id == filtros.area_id);
});

const filteredUbicaciones = computed(() => {
    if (!filtros.area_id) return props.ubicaciones;
    const allowedIds = props.areaLocationMap[filtros.area_id] || [];
    return props.ubicaciones.filter(u => allowedIds.includes(u.id));
});

// Watch Area Change
watch(() => filtros.area_id, (newVal) => {
    if (newVal !== props.filters?.area_id) {
        filtros.ubicacion_id = '';
        filtros.personal_id = '';
    }
});

const aplicarFiltros = () => {
    filtros.get(route('activos.etiquetas-qr'), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const urlPdf = computed(() => route('activos.etiquetas-qr.pdf', filtros.data()));
</script>

<template>
    <Head title="Imprimir Etiquetas QR" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="flex items-center gap-2 text-xl font-semibold leading-tight text-gray-800">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                        </svg>
                        <span>Imprimir Etiquetas QR</span>
                    </div>
                    <p class="text-sm text-gray-500">Genera etiquetas con códigos QR para tus activos.</p>
                </div>
                <div class="flex gap-2 text-sm">
                    <a :href="route('activos.reportes')" class="inline-flex items-center gap-2 rounded-md border border-gray-200 px-3 py-2 text-gray-700 shadow-sm transition hover:border-indigo-300 hover:text-indigo-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h10m-6 6h12" />
                        </svg>
                        Reportes
                    </a>
                    <a :href="route('activos.index')" class="inline-flex items-center gap-2 rounded-md border border-gray-200 px-3 py-2 text-gray-700 shadow-sm transition hover:border-indigo-300 hover:text-indigo-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 5h18M9 3v2m6-2v2M5 9h14v10H5z" />
                        </svg>
                        Inventario
                    </a>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <!-- Información -->
                <div class="rounded-2xl border border-purple-100 bg-purple-50 p-4 shadow-sm">
                    <div class="flex items-start gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <h4 class="font-semibold text-purple-900">Instrucciones</h4>
                            <p class="text-sm text-purple-700 mt-1">
                                Selecciona los activos usando los filtros y genera un PDF con etiquetas QR para impresión. 
                                <strong>Layout: 6 etiquetas por página (2x3)</strong> optimizado para hojas de etiquetas adhesivas tamaño carta.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Contador -->
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex items-center gap-2 text-sm text-gray-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                        </svg>
                        <span>Etiquetas a generar</span>
                    </div>
                    <p class="text-3xl font-semibold text-purple-700 mt-1">{{ activos.length }}</p>
                    <p class="text-xs text-gray-500 mt-1">
                        {{ Math.ceil(activos.length / 6) }} {{ activos.length === 1 ? 'página' : 'páginas' }}
                    </p>
                </div>

                <!-- Filtros y Generación -->
                <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Filtrar activos</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Selecciona qué activos deseas incluir en las etiquetas.</p>
                        </div>
                        <a 
                            :href="urlPdf" 
                            class="inline-flex items-center gap-2 rounded-md bg-purple-600 px-4 py-2.5 font-semibold text-white shadow-sm transition hover:bg-purple-700"
                            :class="{ 'opacity-50 pointer-events-none': activos.length === 0 }"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Generar Etiquetas PDF
                        </a>
                    </div>

                    <form class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5" @submit.prevent="aplicarFiltros">
                        <div>
                            <label class="text-xs font-semibold text-gray-700">Área</label>
                            <select v-model="filtros.area_id" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-sm shadow-sm focus:border-purple-500 focus:ring-purple-500">
                                <option value="">Todas</option>
                                <option v-for="area in areas" :key="area.id" :value="area.id">{{ String(area.id).padStart(2, '0') }} - {{ area.nombre }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-700">Ubicación</label>
                            <select v-model="filtros.ubicacion_id" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-sm shadow-sm focus:border-purple-500 focus:ring-purple-500">
                                <option value="">Todas</option>
                                <option v-for="ub in filteredUbicaciones" :key="ub.id" :value="ub.id">{{ ub.nombre }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-700">Responsable</label>
                            <select v-model="filtros.personal_id" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-sm shadow-sm focus:border-purple-500 focus:ring-purple-500">
                                <option value="">Todos</option>
                                <option v-for="per in filteredPersonal" :key="per.id" :value="per.id">{{ per.nombre }} {{ per.apellido }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-700">Clasificación</label>
                            <select v-model="filtros.clasificacion_id" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-sm shadow-sm focus:border-purple-500 focus:ring-purple-500">
                                <option value="">Todas</option>
                                <option v-for="clas in clasificaciones" :key="clas.id" :value="clas.id">{{ clas.nombre }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-700">Estado</label>
                            <select v-model="filtros.estado" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-sm shadow-sm focus:border-purple-500 focus:ring-purple-500">
                                <option value="">Todos</option>
                                <option v-for="opt in estados" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                            </select>
                        </div>
                        <div class="sm:col-span-2 lg:col-span-5 flex justify-end">
                            <button
                                type="submit"
                                class="inline-flex items-center rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-700 focus:ring-offset-1"
                            >
                                Aplicar filtros
                            </button>
                        </div>
                    </form>

                    <!-- Preview de activos -->
                    <div class="mt-6 overflow-auto" v-if="activos.length > 0">
                        <h4 class="text-sm font-semibold text-gray-700 mb-2">Vista previa de activos seleccionados:</h4>
                        <table class="min-w-full divide-y divide-gray-100 text-left text-sm text-gray-700">
                            <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                                <tr>
                                    <th class="px-4 py-3">#</th>
                                    <th class="px-4 py-3">Código</th>
                                    <th class="px-4 py-3">Activo</th>
                                    <th class="px-4 py-3">Área</th>
                                    <th class="px-4 py-3">Ubicación</th>
                                    <th class="px-4 py-3">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(a, index) in activos.slice(0, 10)" :key="a.id" class="border-b border-gray-100 hover:bg-gray-50">
                                    <td class="px-4 py-3 font-mono text-xs text-gray-500">{{ index + 1 }}</td>
                                    <td class="px-4 py-3 font-mono text-xs text-gray-600">{{ a.codigo_inventario }}</td>
                                    <td class="px-4 py-3 font-medium text-gray-900">{{ a.nombre_activo }}</td>
                                    <td class="px-4 py-3 text-gray-700">{{ a.area }}</td>
                                    <td class="px-4 py-3 text-gray-700">{{ a.ubicacion }}</td>
                                    <td class="px-4 py-3 text-gray-700">{{ a.estado }}</td>
                                </tr>
                                <tr v-if="activos.length > 10">
                                    <td colspan="6" class="px-4 py-3 text-center text-gray-500 bg-gray-50 font-medium">
                                        ... y {{ activos.length - 10 }} activos más
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-else class="mt-6 rounded-lg bg-gray-50 p-8 text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                        </svg>
                        <p class="mt-2 text-sm text-gray-600">No hay activos con los filtros seleccionados</p>
                        <p class="text-xs text-gray-500">Ajusta los filtros para ver activos</p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
