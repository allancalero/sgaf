<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed, onMounted, ref, watch } from 'vue';
import { Chart } from 'chart.js/auto';

const page = usePage();
const municipalityName = computed(() => page.props.system?.nombre_alcaldia || 'SGAF');

const props = defineProps({
    activos: { type: Array, default: () => [] },
    totales: { type: Object, default: () => ({ cantidad: 0, valor: 0 }) },
    areas: { type: Array, default: () => [] },
    ubicaciones: { type: Array, default: () => [] },
    clasificaciones: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
    activosList: { type: Array, default: () => [] },
    personal: { type: Array, default: () => [] },
    areaLocationMap: { type: Object, default: () => ({}) },
});

const filtroInventario = useForm({
    area_id: props.filters?.area_id || '',
    ubicacion_id: props.filters?.ubicacion_id || '',
    personal_id: props.filters?.personal_id || '',
    clasificacion_id: props.filters?.clasificacion_id || '',
    estado: props.filters?.estado || '',
});

// Computed Filters
const filteredPersonal = computed(() => {
    if (!filtroInventario.area_id) {
        return props.personal;
    }
    // Convert to numbers for strict comparison
    const areaIdNumber = Number(filtroInventario.area_id);
    const filtered = props.personal.filter(p => {
        const personalAreaId = Number(p.area_id);
        return personalAreaId === areaIdNumber;
    });
    return filtered;
});

const filteredUbicaciones = computed(() => {
    if (!filtroInventario.area_id) return props.ubicaciones;
    const allowedIds = props.areaLocationMap[filtroInventario.area_id] || [];
    return props.ubicaciones.filter(u => allowedIds.includes(u.id));
});

// Watch Area Change
watch(() => filtroInventario.area_id, (newVal, oldVal) => {
    // Reset dependant filters if area changes
    if (newVal !== oldVal && newVal !== props.filters?.area_id) { 
        filtroInventario.ubicacion_id = '';
        filtroInventario.personal_id = '';
        // Optional: Log only on actual changes
        if (newVal) {
            const filtered = props.personal.filter(p => Number(p.area_id) === Number(newVal));
            console.log(`🔍 Área seleccionada: ${newVal} → ${filtered.length} responsable(s) disponible(s)`);
        }
    }
});

const currencySymbol = computed(() => page.props.system?.moneda || 'C$');
const formatCurrency = (value) => {
    if (value === null || value === undefined || value === '') return '-';
    return `${currencySymbol.value}${Number(value).toLocaleString('es-ES', { minimumFractionDigits: 2 })}`;
};

const filtroTrazabilidad = useForm({
    desde: '',
    hasta: '',
    activo_id: '',
    area_id: '',
    personal_id: '',
});

const estados = [
    { value: 'BUENO', label: 'Bueno' },
    { value: 'REGULAR', label: 'Regular' },
    { value: 'MALO', label: 'Malo' },
];

const aplicarInventario = () => {
    filtroInventario.get(route('activos.reportes'), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const urlInventario = computed(() => route('activos.reportes.inventario.export', filtroInventario.data()));
const urlInventarioPdf = computed(() => route('activos.reportes.inventario.pdf', filtroInventario.data()));
const urlTrazabilidad = computed(() => route('activos.reportes.trazabilidad.export', filtroTrazabilidad.data()));
const urlTrazabilidadPdf = computed(() => route('activos.reportes.trazabilidad.pdf', filtroTrazabilidad.data()));

// Chart instances  
let chartStatusInstance = null;
let chartAreaInstance = null;

const renderCharts = () => {
    // Destroy existing charts
    if (chartStatusInstance) chartStatusInstance.destroy();
    if (chartAreaInstance) chartAreaInstance.destroy();

    // Assets by Status
    const statusCounts = props.activos.reduce((acc, curr) => {
        acc[curr.estado] = (acc[curr.estado] || 0) + 1;
        return acc;
    }, {});
    
    const statusCanvas = document.getElementById('chart-status');
    if (statusCanvas) {
        chartStatusInstance = new Chart(statusCanvas, {
            type: 'doughnut',
            data: {
                labels: Object.keys(statusCounts),
                datasets: [{
                    data: Object.values(statusCounts),
                    backgroundColor: ['#10B981', '#F59E0B', '#EF4444', '#6366F1'],
                }]
            },
            options: { 
                responsive: true, 
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });
    }

    // Assets by Classification
    const clasificacionCounts = props.activos.reduce((acc, curr) => {
        const clasificacion = curr.clasificacion || 'Sin Clasificación';
        acc[clasificacion] = (acc[clasificacion] || 0) + 1;
        return acc;
    }, {});
    
    const areaCanvas = document.getElementById('chart-area');
    if (areaCanvas) {
        chartAreaInstance = new Chart(areaCanvas, {
            type: 'doughnut',
            data: {
                labels: Object.keys(clasificacionCounts),
                datasets: [{
                    data: Object.values(clasificacionCounts),
                    backgroundColor: ['#6366F1', '#0EA5E9', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#22D3EE', '#A855F7'],
                }]
            },
            options: { 
                responsive: true, 
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });
    }
};

onMounted(renderCharts);

// Watch para actualizar los gráficos cuando cambian los datos filtrados
watch(() => props.activos, () => {
    renderCharts();
}, { deep: true });
</script>

<template>
    <Head title="Reportes de activos" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="flex items-center gap-2 text-xl font-semibold leading-tight text-gray-800 dark:text-gray-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h10m-6 6h12" />
                        </svg>
                        <span>Reportes de activos</span>
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Inventario filtrable y exportaciones.</p>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl space-y-8 sm:px-6 lg:px-8">
                <div class="grid gap-4 sm:grid-cols-3">
<<<<<<< HEAD
                    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
=======
                    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                        <div class="flex items-center gap-2 text-sm text-gray-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
>>>>>>> 8f3e0761afe5c74474f514ac2afef3e6d88db82c
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M5 12l4-4m-4 4l4 4" />
                            </svg>
                            <span>Activos filtrados</span>
                        </div>
<<<<<<< HEAD
                        <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ totales.cantidad }}</p>
                    </div>
                    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
=======
                        <p class="text-2xl font-semibold text-gray-900">{{ totales.cantidad }}</p>
                    </div>
                    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                        <div class="flex items-center gap-2 text-sm text-gray-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
>>>>>>> 8f3e0761afe5c74474f514ac2afef3e6d88db82c
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 1.12-3 2.5S10.343 13 12 13s3 1.12 3 2.5S13.657 18 12 18m0-10v-2m0 12v2" />
                            </svg>
                            <span>Valor total</span>
                        </div>
<<<<<<< HEAD
                        <p class="text-2xl font-semibold text-indigo-700 dark:text-indigo-400">{{ formatCurrency(totales.valor || 0) }}</p>
                    </div>
                    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
=======
                        <p class="text-2xl font-semibold text-indigo-700">{{ formatCurrency(totales.valor || 0) }}</p>
                    </div>
                    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                        <div class="flex items-center gap-2 text-sm text-gray-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
>>>>>>> 8f3e0761afe5c74474f514ac2afef3e6d88db82c
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 12h16M4 12l4-4m-4 4l4 4" />
                            </svg>
                            <span>Promedio</span>
                        </div>
<<<<<<< HEAD
                        <p class="text-2xl font-semibold text-amber-600 dark:text-amber-400">{{ formatCurrency(totales.cantidad ? (totales.valor || 0) / totales.cantidad : 0) }}</p>
=======
                        <p class="text-2xl font-semibold text-amber-600">{{ formatCurrency(totales.cantidad ? (totales.valor || 0) / totales.cantidad : 0) }}</p>
>>>>>>> 8f3e0761afe5c74474f514ac2afef3e6d88db82c
                    </div>
                </div>

                <!-- Charts Section -->
                <div class="grid gap-6 md:grid-cols-2">
<<<<<<< HEAD
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 dark:bg-gray-800 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">Estado de Activos</h3>
=======
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Estado de Activos</h3>
>>>>>>> 8f3e0761afe5c74474f514ac2afef3e6d88db82c
                        <div class="h-64">
                            <canvas id="chart-status"></canvas>
                        </div>
                    </div>
<<<<<<< HEAD
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 dark:bg-gray-800 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">Activos por Clasificación</h3>
=======
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Activos por Clasificación</h3>
>>>>>>> 8f3e0761afe5c74474f514ac2afef3e6d88db82c
                        <div class="h-64">
                            <canvas id="chart-area"></canvas>
                        </div>
                    </div>
                </div>

<<<<<<< HEAD
                <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Reporte de inventario</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Filtra y exporta en CSV o PDF.</p>
=======
                <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Reporte de inventario</h3>
                            <p class="text-sm text-gray-500">Filtra y exporta en CSV o PDF.</p>
>>>>>>> 8f3e0761afe5c74474f514ac2afef3e6d88db82c
                        </div>
                        <div class="flex gap-2 text-sm">
                            <a :href="urlInventarioPdf" class="inline-flex items-center gap-2 rounded-md bg-red-600 px-3 py-2 font-semibold text-white shadow-sm transition hover:bg-red-700">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v12m0 0l-4-4m4 4l4-4M4 20h16" />
                                </svg>
                                PDF
                            </a>
                            <a :href="urlInventario" class="inline-flex items-center gap-2 rounded-md bg-indigo-600 px-3 py-2 font-semibold text-white shadow-sm transition hover:bg-indigo-700">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v12m0 0l-4-4m4 4l4-4M4 20h16" />
                                </svg>
                                CSV
                            </a>
                        </div>
                    </div>

                    <form class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-5" @submit.prevent="aplicarInventario">
                        <div>
                            <label class="text-xs font-semibold text-gray-700">Área</label>
                            <select v-model="filtroInventario.area_id" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Todas</option>
                                <option v-for="area in areas" :key="area.id" :value="area.id">{{ String(area.id).padStart(2, '0') }} - {{ area.nombre }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-700">Ubicación</label>
                            <select v-model="filtroInventario.ubicacion_id" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Todas</option>
                                <option v-for="ub in filteredUbicaciones" :key="ub.id" :value="ub.id">{{ ub.nombre }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-700">Responsable</label>
                            <select v-model="filtroInventario.personal_id" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Todos</option>
                                <option v-if="filteredPersonal.length === 0 && filtroInventario.area_id" disabled>No hay personal en esta área</option>
                                <option v-for="per in filteredPersonal" :key="per.id" :value="per.id">{{ per.nombre }} {{ per.apellido }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-700">Clasificación</label>
                            <select v-model="filtroInventario.clasificacion_id" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Todas</option>
                                <option v-for="clas in clasificaciones" :key="clas.id" :value="clas.id">{{ String(clas.id).padStart(3, '0') }} - {{ clas.nombre }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-700">Estado</label>
                            <select v-model="filtroInventario.estado" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
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

                    <div class="mt-6 overflow-auto">
                        <table class="min-w-full divide-y divide-gray-100 text-left text-sm text-gray-700">
                            <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                                <tr>
                                    <th class="px-4 py-3">#</th>
                                    <th class="px-4 py-3">Código</th>
                                    <th class="px-4 py-3">Activo</th>
                                    <th class="px-4 py-3">Área</th>
                                    <th class="px-4 py-3">Clasificación</th>
                                    <th class="px-4 py-3">Ubicación</th>
                                    <th class="px-4 py-3">Responsable</th>
                                    <th class="px-4 py-3">Estado</th>
                                    <th class="px-4 py-3">Precio</th>
                                    <th class="px-4 py-3">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(a, index) in props.activos" :key="a.id" class="border-b border-gray-100 hover:bg-gray-50">
                                    <td class="px-4 py-3 font-mono text-xs text-gray-500">{{ index + 1 }}</td>
                                    <td class="px-4 py-3 font-mono text-xs text-gray-600">{{ a.codigo_inventario }}</td>
                                    <td class="px-4 py-3 font-medium text-gray-900">{{ a.nombre_activo }}</td>
                                    <td class="px-4 py-3 text-gray-700">{{ a.area }}</td>
                                    <td class="px-4 py-3 text-gray-700">{{ a.clasificacion }}</td>
                                    <td class="px-4 py-3 text-gray-700">{{ a.ubicacion }}</td>
                                    <td class="px-4 py-3 text-gray-700">{{ a.responsable || 'No asignado' }}</td>
                                    <td class="px-4 py-3 text-gray-700">{{ a.estado }}</td>
                                    <td class="px-4 py-3 text-gray-900">{{ formatCurrency(a.precio_adquisicion) }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex gap-2">
                                            <a 
                                                :href="`/activos/${a.id}/acta-asignacion`" 
                                                target="_blank"
                                                class="inline-flex items-center gap-1 rounded-md bg-blue-600 px-2 py-1 text-xs font-medium text-white shadow-sm transition hover:bg-blue-700"
                                                title="Descargar Acta de Asignación"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                                Acta
                                            </a>
                                            <a 
                                                :href="`/activos/${a.id}/qr`" 
                                                target="_blank"
                                                class="inline-flex items-center gap-1 rounded-md bg-purple-600 px-2 py-1 text-xs font-medium text-white shadow-sm transition hover:bg-purple-700"
                                                title="Ver Código QR"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                                </svg>
                                                QR
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="!props.activos.length">
                                    <td colspan="10" class="px-4 py-4 text-center text-gray-500">Sin registros con los filtros aplicados</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Reporte de trazabilidad</h3>
                            <p class="text-sm text-gray-500">Exporta movimientos con filtros en CSV o PDF.</p>
                        </div>
                        <div class="flex gap-2 text-sm">
                            <a :href="urlTrazabilidadPdf" class="inline-flex items-center gap-2 rounded-md bg-red-600 px-3 py-2 font-semibold text-white shadow-sm transition hover:bg-red-700">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v12m0 0l-4-4m4 4l4-4M4 20h16" />
                                </svg>
                                PDF
                            </a>
                            <a :href="urlTrazabilidad" class="inline-flex items-center gap-2 rounded-md bg-indigo-600 px-3 py-2 font-semibold text-white shadow-sm transition hover:bg-indigo-700">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v12m0 0l-4-4m4 4l4-4M4 20h16" />
                                </svg>
                                CSV
                            </a>
                        </div>
                    </div>

                    <div class="mt-4 grid gap-4 sm:grid-cols-5">
                        <div>
                            <label class="text-xs font-semibold text-gray-700">Desde</label>
                            <input v-model="filtroTrazabilidad.desde" type="date" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-700">Hasta</label>
                            <input v-model="filtroTrazabilidad.hasta" type="date" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-700">Activo</label>
                            <select v-model="filtroTrazabilidad.activo_id" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Todos</option>
                                <option v-for="ac in activosList" :key="ac.id" :value="ac.id">{{ ac.codigo_inventario }} - {{ ac.nombre_activo }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-700">Área destino</label>
                            <select v-model="filtroTrazabilidad.area_id" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Todas</option>
                                <option v-for="area in areas" :key="area.id" :value="area.id">{{ area.nombre }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-700">Responsable destino</label>
                            <select v-model="filtroTrazabilidad.personal_id" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Todos</option>
                                <option v-for="per in personal" :key="per.id" :value="per.id">{{ per.nombre }} {{ per.apellido }}</option>
                            </select>
                        </div>
                    </div>

                    <p class="mt-3 text-xs text-gray-500">El botón descarga con los filtros actuales.</p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
