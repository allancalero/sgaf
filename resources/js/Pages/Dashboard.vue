<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { Chart, ArcElement, Tooltip, Legend, CategoryScale, LinearScale, BarElement, PointElement, LineElement, DoughnutController, BarController, LineController } from 'chart.js';
import { computed, onMounted, ref, watch } from 'vue';

Chart.register(ArcElement, Tooltip, Legend, CategoryScale, LinearScale, BarElement, PointElement, LineElement, DoughnutController, BarController, LineController);

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



// Referencias individuales para cada canvas
const estadoCanvas = ref(null);
const areaCanvas = ref(null);
const responsableCanvas = ref(null);
const asignacionesCanvas = ref(null);
const clasificacionCanvas = ref(null);
const valorAreaCanvas = ref(null);

const chartInstances = {};

const page = usePage();
const currencySymbol = computed(() => page.props.system?.moneda || 'C$');
const municipalityName = computed(() => page.props.system?.nombre_alcaldia || 'SGAF');
const formatCurrency = (value) => `${currencySymbol.value}${Number(value || 0).toLocaleString('es-ES', { minimumFractionDigits: 2 })}`;

const palette = ['#6366F1', '#0EA5E9', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#22D3EE', '#A855F7'];

const buildChart = (canvasRef, key, type, labels, data, overrides = {}) => {
    const ctx = canvasRef?.value?.getContext('2d');
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
        buildChart(estadoCanvas, 'estado', 'doughnut', estado.map((i) => i.label), estado.map((i) => i.value), {
            label: 'Activos por estado',
        });
    }

    const area = props.charts?.activosPorArea || [];
    if (area.length) {
        buildChart(areaCanvas, 'area', 'bar', area.map((i) => i.label), area.map((i) => i.value), {
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
        buildChart(responsableCanvas, 'responsable', 'bar', responsables.map((i) => i.label), responsables.map((i) => i.value), {
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
        buildChart(asignacionesCanvas, 'asignaciones', 'line', asignaciones.map((i) => i.label), asignaciones.map((i) => i.value), {
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
        buildChart(clasificacionCanvas, 'clasificacion', 'doughnut', clasificacion.map((i) => i.label), clasificacion.map((i) => i.value), {
            label: 'Activos por clasificación',
        });
    }

    const valorArea = props.charts?.valorPorArea || [];
    if (valorArea.length) {
        buildChart(valorAreaCanvas, 'valorArea', 'bar', valorArea.map((i) => i.label), valorArea.map((i) => i.value), {
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
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <!-- Stat Card: Activos -->
                    <div class="group relative overflow-hidden rounded-2xl border border-indigo-100 bg-white p-6 shadow-sm transition-all hover:shadow-md dark:border-indigo-500/20 dark:bg-gray-800">
                        <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-indigo-50 transition-colors group-hover:bg-indigo-100 dark:bg-indigo-900/20 dark:group-hover:bg-indigo-900/30"></div>
                        <div class="relative flex items-center gap-4">
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-500 text-white shadow-lg shadow-indigo-500/20">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wider text-indigo-500 dark:text-indigo-400">Total Activos</p>
                                <p class="text-3xl font-black text-gray-900 dark:text-gray-100">{{ totals.activos ?? 0 }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Stat Card: Responsables -->
                    <div class="group relative overflow-hidden rounded-2xl border border-sky-100 bg-white p-6 shadow-sm transition-all hover:shadow-md dark:border-sky-500/20 dark:bg-gray-800">
                        <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-sky-50 transition-colors group-hover:bg-sky-100 dark:bg-sky-900/20 dark:group-hover:bg-sky-900/30"></div>
                        <div class="relative flex items-center gap-4">
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-sky-500 text-white shadow-lg shadow-sky-500/20">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wider text-sky-500 dark:text-sky-400">Personal</p>
                                <p class="text-3xl font-black text-gray-900 dark:text-gray-100">{{ totals.responsables ?? 0 }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Stat Card: Asignaciones -->
                    <div class="group relative overflow-hidden rounded-2xl border border-emerald-100 bg-white p-6 shadow-sm transition-all hover:shadow-md dark:border-emerald-500/20 dark:bg-gray-800">
                        <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-emerald-50 transition-colors group-hover:bg-emerald-100 dark:bg-emerald-900/20 dark:group-hover:bg-emerald-900/30"></div>
                        <div class="relative flex items-center gap-4">
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-500 text-white shadow-lg shadow-emerald-500/20">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wider text-emerald-500 dark:text-emerald-400">Movimientos</p>
                                <p class="text-3xl font-black text-gray-900 dark:text-gray-100">{{ totals.asignaciones ?? 0 }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Stat Card: Sin Responsable -->
                    <div class="group relative overflow-hidden rounded-2xl border border-rose-100 bg-white p-6 shadow-sm transition-all hover:shadow-md dark:border-rose-500/20 dark:bg-gray-800">
                        <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-rose-50 transition-colors group-hover:bg-rose-100 dark:bg-rose-900/20 dark:group-hover:bg-rose-900/30"></div>
                        <div class="relative flex items-center gap-4">
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-rose-500 text-white shadow-lg shadow-rose-500/20">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wider text-rose-500 dark:text-rose-400">Sin Asignar</p>
                                <p class="text-3xl font-black text-gray-900 dark:text-gray-100">{{ totals.sin_responsable ?? 0 }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Costo Total Card -->
                    <div class="group relative overflow-hidden rounded-2xl border border-amber-100 bg-gradient-to-br from-white to-amber-50/30 p-8 shadow-sm transition-all hover:shadow-md sm:col-span-2 lg:col-span-4 dark:border-amber-500/20 dark:from-gray-800 dark:to-amber-900/10">
                        <div class="absolute -right-12 -top-12 h-64 w-64 rounded-full bg-amber-100/30 blur-3xl transition-colors group-hover:bg-amber-100/50 dark:bg-amber-900/10"></div>
                        <div class="relative flex flex-col items-center justify-between gap-6 sm:flex-row">
                            <div class="flex items-center gap-6">
                                <div class="flex h-20 w-20 items-center justify-center rounded-2xl bg-amber-500 text-white shadow-2xl shadow-amber-500/40">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200">Valor Total del Inventario</h3>
                                    <p class="text-xs font-semibold uppercase tracking-widest text-amber-500 dark:text-amber-400">Patrimonio en Activos Fijos</p>
                                </div>
                            </div>
                            <div class="text-center sm:text-right">
                                <p class="text-5xl font-black tracking-tight text-gray-900 dark:text-gray-100">{{ formatCurrency(totals.costo_total) }}</p>
                                <p class="mt-1 text-sm font-medium text-gray-500 dark:text-gray-400">Basado en el precio de adquisición registrado</p>
                            </div>
                        </div>
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
                            <canvas v-if="hasData(charts.activosPorEstado)" ref="estadoCanvas"></canvas>
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
                            <canvas v-if="hasData(charts.activosPorArea)" ref="areaCanvas"></canvas>
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
                            <canvas v-if="hasData(charts.responsablesPorArea)" ref="responsableCanvas"></canvas>
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
                            <canvas v-if="hasData(charts.asignacionesPorMes)" ref="asignacionesCanvas"></canvas>
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
                            <canvas v-if="hasData(charts.activosPorClasificacion)" ref="clasificacionCanvas"></canvas>
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
                            <canvas v-if="hasData(charts.valorPorArea)" ref="valorAreaCanvas"></canvas>
                            <p v-else class="flex h-full items-center justify-center text-sm text-gray-500 dark:text-gray-400">Sin costos registrados.</p>
                        </div>
                    </div>
                </div>


            </div>
        </div>
    </AuthenticatedLayout>
</template>
