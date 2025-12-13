<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { Chart, ArcElement, Tooltip, Legend, CategoryScale, LinearScale, BarElement, PointElement, LineElement } from 'chart.js';
import { computed, onMounted, ref, watch } from 'vue';

Chart.register(ArcElement, Tooltip, Legend, CategoryScale, LinearScale, BarElement, PointElement, LineElement);

const props = defineProps({
    totals: {
        type: Object,
        default: () => ({}),
    },
    charts: {
        type: Object,
        default: () => ({}),
    },
});

const catalogs = [
    { href: route('activos.index'), label: 'Activos fijos' },
    { href: route('activos.resumen'), label: 'Resumen de activos' },
    { href: route('sistema.index'), label: 'Sistema' },
    { href: route('areas.index'), label: 'Áreas' },
    { href: route('cargos.index'), label: 'Cargos' },
    { href: route('ubicaciones.index'), label: 'Ubicaciones' },
    { href: route('clasificaciones.index'), label: 'Clasificaciones' },
    { href: route('fuentes.index'), label: 'Fuentes' },
    { href: route('tipos.index'), label: 'Tipos' },
    { href: route('proveedores.index'), label: 'Proveedores' },
    { href: route('personal.index'), label: 'Personal' },
    { href: route('responsables.index'), label: 'Responsables' },
];

const chartRefs = {
    estado: ref(null),
    area: ref(null),
    responsable: ref(null),
    asignaciones: ref(null),
    clasificacion: ref(null),
    valorArea: ref(null),
};

const chartInstances = {};

const page = usePage();
const currencySymbol = computed(() => page.props.system?.moneda || 'C$');
const formatCurrency = (value) => `${currencySymbol.value}${Number(value || 0).toLocaleString('es-ES', { minimumFractionDigits: 2 })}`;

const palette = ['#6366F1', '#0EA5E9', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#22D3EE', '#A855F7'];

const buildChart = (key, type, labels, data, overrides = {}) => {
    const ctx = chartRefs[key]?.value?.getContext('2d');
    if (!ctx) return;

    if (chartInstances[key]) {
        chartInstances[key].destroy();
    }

    const baseDataset = {
        data,
        backgroundColor: type === 'line' ? 'rgba(99, 102, 241, 0.15)' : palette,
        borderColor: type === 'line' ? '#6366F1' : palette,
        borderWidth: type === 'line' ? 2 : 1,
        tension: 0.35,
        fill: type === 'line',
    };

    chartInstances[key] = new Chart(ctx, {
        type,
        data: {
            labels,
            datasets: [
                {
                    label: overrides.label || '',
                    ...baseDataset,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: overrides.legend ?? true,
                    position: 'bottom',
                },
                tooltip: { enabled: true },
            },
            scales: overrides.scales,
        },
    });
};

const renderCharts = () => {
    const estado = props.charts?.activosPorEstado || [];
    if (estado.length) {
        buildChart('estado', 'doughnut', estado.map((i) => i.label), estado.map((i) => i.value), {
            label: 'Activos por estado',
        });
    }

    const area = props.charts?.activosPorArea || [];
    if (area.length) {
        buildChart('area', 'bar', area.map((i) => i.label), area.map((i) => i.value), {
            label: 'Activos por área',
            legend: false,
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } },
                x: { grid: { display: false } },
            },
        });
    }

    const responsables = props.charts?.responsablesPorArea || [];
    if (responsables.length) {
        buildChart('responsable', 'bar', responsables.map((i) => i.label), responsables.map((i) => i.value), {
            label: 'Responsables por área',
            legend: false,
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } },
                x: { grid: { display: false } },
            },
        });
    }

    const asignaciones = props.charts?.asignacionesPorMes || [];
    if (asignaciones.length) {
        buildChart('asignaciones', 'line', asignaciones.map((i) => i.label), asignaciones.map((i) => i.value), {
            label: 'Asignaciones por mes',
            legend: false,
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } },
                x: { grid: { display: false } },
            },
        });
    }

    const clasificacion = props.charts?.activosPorClasificacion || [];
    if (clasificacion.length) {
        buildChart('clasificacion', 'doughnut', clasificacion.map((i) => i.label), clasificacion.map((i) => i.value), {
            label: 'Activos por clasificación',
        });
    }

    const valorArea = props.charts?.valorPorArea || [];
    if (valorArea.length) {
        buildChart('valorArea', 'bar', valorArea.map((i) => i.label), valorArea.map((i) => i.value), {
            label: 'Valor por área',
            legend: false,
            scales: {
                y: { beginAtZero: true },
                x: { grid: { display: false } },
            },
        });
    }
};

onMounted(renderCharts);
watch(() => props.charts, renderCharts, { deep: true });

const hasData = (items) => Array.isArray(items) && items.length > 0;
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-100">Dashboard</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Visión rápida de activos, responsables y asignaciones.</p>
                </div>
                <div class="flex flex-wrap gap-2 text-sm text-indigo-700">
                    <Link :href="route('activos.index')" class="inline-flex items-center gap-2 rounded-full bg-indigo-50 px-3 py-1 font-semibold hover:bg-indigo-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 5h18M9 3v2m6-2v2M5 9h14v10H5z" />
                        </svg>
                        Gestionar activos
                    </Link>
                    <Link :href="route('responsables.index')" class="inline-flex items-center gap-2 rounded-full bg-sky-50 px-3 py-1 font-semibold text-sky-700 hover:bg-sky-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-sky-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v5m0 0h5m-5 0h-5" />
                        </svg>
                        Responsables
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-7xl space-y-8 sm:px-6 lg:px-8">
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="rounded-lg border border-indigo-100 bg-white px-4 py-5 shadow-sm dark:border-indigo-900/50 dark:bg-gray-800">
                        <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-indigo-500 dark:text-indigo-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h10m-6 6h12" />
                            </svg>
                            <span>Activos</span>
                        </div>
                        <p class="mt-2 text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ totals.activos ?? 0 }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Total registrados</p>
                    </div>
                    <div class="rounded-lg border border-sky-100 bg-white px-4 py-5 shadow-sm dark:border-sky-900/50 dark:bg-gray-800">
                        <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-sky-500 dark:text-sky-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v5m0 0h5m-5 0h-5" />
                            </svg>
                            <span>Responsables</span>
                        </div>
                        <p class="mt-2 text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ totals.responsables ?? 0 }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Con estado activo/inactivo</p>
                    </div>
                    <div class="rounded-lg border border-emerald-100 bg-white px-4 py-5 shadow-sm dark:border-emerald-900/50 dark:bg-gray-800">
                        <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-emerald-500 dark:text-emerald-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 5h12M9 3v2m-4 5h16M9 21v-7m6-7v12" />
                            </svg>
                            <span>Asignaciones</span>
                        </div>
                        <p class="mt-2 text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ totals.asignaciones ?? 0 }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Movimientos registrados</p>
                    </div>
                    <div class="rounded-lg border border-amber-100 bg-white px-4 py-5 shadow-sm dark:border-amber-900/50 dark:bg-gray-800">
                        <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-amber-500 dark:text-amber-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 12a4 4 0 100-8 4 4 0 000 8zm-6 8a6 6 0 1112 0H6z" />
                            </svg>
                            <span>Sin responsable</span>
                        </div>
                        <p class="mt-2 text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ totals.sin_responsable ?? 0 }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Activos sin asignar</p>
                    </div>
                    <div class="rounded-lg border border-rose-100 bg-white px-4 py-5 shadow-sm lg:col-span-2 dark:border-rose-900/50 dark:bg-gray-800">
                        <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-rose-500 dark:text-rose-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m0 0l-4-4m4 4l4-4M4 10h16" />
                            </svg>
                            <span>Costo total</span>
                        </div>
                        <p class="mt-2 text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ formatCurrency(totals.costo_total) }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Suma de precio de adquisición</p>
                    </div>
                </div>

                <div class="grid gap-6 lg:grid-cols-2">
                    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-center justify-between pb-3">
                            <div class="flex items-start gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-5 w-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5h10M11 9h7M11 13h4M4 7h.01M4 11h.01M4 15h.01" />
                                </svg>
                                <div>
                                    <h3 class="text-base font-semibold text-gray-800 dark:text-gray-100">Activos por estado</h3>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Distribución por condición actual.</p>
                                </div>
                            </div>
                        </div>
                        <div class="h-72">
                            <canvas v-if="hasData(charts.activosPorEstado)" :ref="chartRefs.estado"></canvas>
                            <p v-else class="flex h-full items-center justify-center text-sm text-gray-500 dark:text-gray-400">Sin datos de activos aún.</p>
                        </div>
                    </div>

                    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-center justify-between pb-3">
                            <div class="flex items-start gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-5 w-5 text-sky-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h10m-6 4h12m-8 4h8" />
                                </svg>
                                <div>
                                    <h3 class="text-base font-semibold text-gray-800 dark:text-gray-100">Activos por área</h3>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Concentración por área solicitante.</p>
                                </div>
                            </div>
                        </div>
                        <div class="h-72">
                            <canvas v-if="hasData(charts.activosPorArea)" :ref="chartRefs.area"></canvas>
                            <p v-else class="flex h-full items-center justify-center text-sm text-gray-500 dark:text-gray-400">Sin datos de áreas aún.</p>
                        </div>
                    </div>

                    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-center justify-between pb-3">
                            <div class="flex items-start gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-5 w-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20v-2a4 4 0 00-4-4H7a4 4 0 00-4 4v2m14-8a4 4 0 100-8 4 4 0 000 8z" />
                                </svg>
                                <div>
                                    <h3 class="text-base font-semibold text-gray-800 dark:text-gray-100">Responsables por área</h3>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Personal responsable por área.</p>
                                </div>
                            </div>
                        </div>
                        <div class="h-72">
                            <canvas v-if="hasData(charts.responsablesPorArea)" :ref="chartRefs.responsable"></canvas>
                            <p v-else class="flex h-full items-center justify-center text-sm text-gray-500 dark:text-gray-400">Sin responsables cargados.</p>
                        </div>
                    </div>

                    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-center justify-between pb-3">
                            <div class="flex items-start gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-5 w-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 5h18M8 5v14m8-14v14M5 10h14M5 15h14" />
                                </svg>
                                <div>
                                    <h3 class="text-base font-semibold text-gray-800 dark:text-gray-100">Asignaciones por mes</h3>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Histórico de movimientos de activos.</p>
                                </div>
                            </div>
                        </div>
                        <div class="h-72">
                            <canvas v-if="hasData(charts.asignacionesPorMes)" :ref="chartRefs.asignaciones"></canvas>
                            <p v-else class="flex h-full items-center justify-center text-sm text-gray-500 dark:text-gray-400">Aún no hay movimientos registrados.</p>
                        </div>
                    </div>
                </div>

                <div class="grid gap-6 lg:grid-cols-2">
                    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-center justify-between pb-3">
                            <div>
                                <h3 class="text-base font-semibold text-gray-800 dark:text-gray-100">Activos por clasificación</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Distribución por categoría.</p>
                            </div>
                        </div>
                        <div class="h-72">
                            <canvas v-if="hasData(charts.activosPorClasificacion)" :ref="chartRefs.clasificacion"></canvas>
                            <p v-else class="flex h-full items-center justify-center text-sm text-gray-500 dark:text-gray-400">Sin datos de clasificación.</p>
                        </div>
                    </div>

                    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-center justify-between pb-3">
                            <div>
                                <h3 class="text-base font-semibold text-gray-800 dark:text-gray-100">Valor por área</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Suma de costo por área.</p>
                            </div>
                        </div>
                        <div class="h-72">
                            <canvas v-if="hasData(charts.valorPorArea)" :ref="chartRefs.valorArea"></canvas>
                            <p v-else class="flex h-full items-center justify-center text-sm text-gray-500 dark:text-gray-400">Sin costos registrados.</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-semibold text-gray-800 dark:text-gray-100">Módulos</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Accesos rápidos a catálogos y activos.</p>
                        </div>
                    </div>
                    <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        <Link
                            v-for="catalog in catalogs"
                            :key="catalog.href"
                            :href="catalog.href"
                            class="flex items-center justify-between rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-gray-800 transition hover:-translate-y-0.5 hover:border-indigo-300 hover:bg-white hover:shadow dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:border-indigo-400 dark:hover:bg-gray-800"
                        >
                            <span class="font-semibold">{{ catalog.label }}</span>
                            <span aria-hidden="true" class="text-indigo-400">→</span>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
