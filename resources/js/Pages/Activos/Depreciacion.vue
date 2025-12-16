<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const municipalityName = computed(() => page.props.system?.nombre_alcaldia || 'SGAF');
const currencySymbol = computed(() => page.props.system?.moneda || 'C$');

const props = defineProps({
    activos: { type: Array, default: () => [] },
    totales: { type: Object, default: () => ({}) },
    porClasificacion: { type: Array, default: () => [] },
    areas: { type: Array, default:() => [] },
    clasificaciones: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
});

const filtros = useForm({
    area_id: props.filters?.area_id || '',
    clasificacion_id: props.filters?.clasificacion_id || '',
    search: props.filters?.search || '',
});

const aplicarFiltros = () => {
    filtros.get(route('activos.depreciacion'), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const calcularDepreciacion = () => {
    if (confirm('¿Calcular la depreciación de todos los activos?')) {
        filtros.post(route('activos.depreciacion.calcular'), {
            preserveState: true,
            onSuccess: () => {
                filtros.get(route('activos.depreciacion'));
            },
        });
    }
};

const formatCurrency = (value) => {
    if (value === null || value === undefined) return '-';
    return `${currencySymbol.value}${Number(value).toLocaleString('es-ES', { minimumFractionDigits: 2 })}`;
};

const calcularPorcentaje = (activo) => {
    if (!activo.precio_adquisicion || !activo.depreciacion_acumulada) return 0;
    const valorDepreciable = activo.precio_adquisicion - (activo.valor_residual || 0);
    if (valorDepreciable <= 0) return 0;
    return ((activo.depreciacion_acumulada / valorDepreciable) * 100).toFixed(1);
};

const urlPdf = computed(() => route('activos.depreciacion.pdf', filtros.data()));
</script>

<template>
    <Head title="Depreciación de Activos" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="flex items-center gap-2 text-xl font-semibold leading-tight text-gray-800">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                        <span>Depreciación de Activos</span>
                    </div>
                    <p class="text-sm text-gray-500">Gestión y cálculo de depreciación de activos fijos</p>
                </div>

                <div class="flex gap-2 text-sm">
                    <button @click="calcularDepreciacion" class="inline-flex items-center gap-2 rounded-md bg-emerald-600 px-3 py-2 text-white shadow-sm transition hover:bg-emerald-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Calcular Depreciación
                    </button>
                    <a :href="urlPdf" class="inline-flex items-center gap-2 rounded-md bg-red-600 px-3 py-2 text-white shadow-sm transition hover:bg-red-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v12m0 0l-4-4m4 4l4-4M4 20h16" />
                        </svg>
                        Exportar PDF
                    </a>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl space-y-8 sm:px-6 lg:px-8">
                
                <!-- Stats Cards -->
                <div class="grid gap-4 sm:grid-cols-4">
                    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                        <div class="flex items-center gap-2 text-sm text-gray-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                            <span>Total Activos</span>
                        </div>
                        <p class="text-2xl font-semibold text-gray-900">{{ totales.total_activos }}</p>
                    </div>

                    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                        <div class="flex items-center gap-2 text-sm text-gray-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 1.12-3 2.5S10.343 13 12 13s3 1.12 3 2.5S13.657 18 12 18m0-10v-2m0 12v2" />
                            </svg>
                            <span>Valor Original</span>
                        </div>
                        <p class="text-2xl font-semibold text-blue-700">{{ formatCurrency(totales.valor_original) }}</p>
                    </div>

                    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                        <div class="flex items-center gap-2 text-sm text-gray-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" />
                            </svg>
                            <span>Depreciación Acumulada</span>
                        </div>
                        <p class="text-2xl font-semibold text-orange-600">{{ formatCurrency(totales.depreciacion_acumulada) }}</p>
                    </div>

                    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                        <div class="flex items-center gap-2 text-sm text-gray-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Valor en Libros</span>
                        </div>
                        <p class="text-2xl font-semibold text-emerald-700">{{ formatCurrency(totales.valor_libros) }}</p>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Filtros</h3>
                    <form @submit.prevent="aplicarFiltros" class="grid gap-4 sm:grid-cols-4">
                        <div>
                            <label class="text-xs font-semibold text-gray-700">Área</label>
                            <select v-model="filtros.area_id" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Todas</option>
                                <option v-for="area in areas" :key="area.id" :value="area.id">{{ area.nombre }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-700">Clasificación</label>
                            <select v-model="filtros.clasificacion_id" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Todas</option>
                                <option v-for="clas in clasificaciones" :key="clas.id" :value="clas.id">{{ clas.nombre }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-700">Búsqueda</label>
                            <input v-model="filtros.search" type="text" placeholder="Código o nombre..." class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                        </div>
                        <div class="flex items-end">
                            <button type="submit" class="w-full rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-gray-800">
                                Aplicar Filtros
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Tabla -->
                <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100 text-left text-sm text-gray-700">
                            <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                                <tr>
                                    <th class="px-4 py-3">Código</th>
                                    <th class="px-4 py-3">Activo</th>
                                    <th class="px-4 py-3">Fecha Adq.</th>
                                    <th class="px-4 py-3">Precio Original</th>
                                    <th class="px-4 py-3">Vida Útil</th>
                                    <th class="px-4 py-3">Depr. Anual</th>
                                    <th class="px-4 py-3">Depr. Acumulada</th>
                                    <th class="px-4 py-3">Valor Libros</th>
                                    <th class="px-4 py-3">% Depr.</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="activo in activos" :key="activo.id" class="border-b border-gray-100 hover:bg-gray-50">
                                    <td class="px-4 py-3 font-mono text-xs text-gray-600">{{ activo.codigo_inventario }}</td>
                                    <td class="px-4 py-3 font-medium text-gray-900">{{ activo.nombre_activo }}</td>
                                    <td class="px-4 py-3 text-gray-700">{{ activo.fecha_adquisicion }}</td>
                                    <td class="px-4 py-3 text-gray-900">{{ formatCurrency(activo.precio_adquisicion) }}</td>
                                    <td class="px-4 py-3 text-gray-700">{{ activo.vida_util_anos }} años</td>
                                    <td class="px-4 py-3 text-gray-700">{{ formatCurrency(activo.depreciacion_anual) }}</td>
                                    <td class="px-4 py-3 text-orange-600 font-semibold">{{ formatCurrency(activo.depreciacion_acumulada) }}</td>
                                    <td class="px-4 py-3 text-emerald-600 font-semibold">{{ formatCurrency(activo.valor_libros) }}</td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium" :class="calcularPorcentaje(activo) >= 100 ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700'">
                                            {{ calcularPorcentaje(activo) }}%
                                        </span>
                                    </td>
                                </tr>
                                <tr v-if="!activos.length">
                                    <td colspan="9" class="px-4 py-8 text-center text-gray-500">
                                        No hay activos con configuración de depreciación
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
