<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const page = usePage();
const municipalityName = computed(() => page.props.system?.nombre_alcaldia || 'SGAF');
const currencySymbol = computed(() => page.props.system?.moneda || 'C$');

const props = defineProps({
    activos: { type: Array, default: () => [] },
    activosSinConfigurar: { type: Array, default: () => [] },
    totales: { type: Object, default: () => ({}) },
    porClasificacion: { type: Array, default: () => [] },
    areas: { type: Array, default: () => [] },
    clasificaciones: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
});

const activeTab = ref('configurados');
const showConfigModal = ref(false);
const selectedActivo = ref(null);
const selectedIds = ref([]);

const filtros = useForm({
    area_id: props.filters?.area_id || '',
    clasificacion_id: props.filters?.clasificacion_id || '',
    search: props.filters?.search || '',
});

const configForm = useForm({
    vida_util_anos: 5,
    valor_residual: 0,
    metodo_depreciacion: 'LINEAL',
});

const masivoForm = useForm({
    activo_ids: [],
    vida_util_anos: 5,
    valor_residual: 0,
    metodo_depreciacion: 'LINEAL',
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

const openConfigModal = (activo) => {
    selectedActivo.value = activo;
    configForm.vida_util_anos = activo.vida_util_anos || 5;
    configForm.valor_residual = activo.valor_residual || 0;
    configForm.metodo_depreciacion = activo.metodo_depreciacion || 'LINEAL';
    showConfigModal.value = true;
};

const closeConfigModal = () => {
    showConfigModal.value = false;
    selectedActivo.value = null;
    configForm.reset();
};

const submitConfig = () => {
    if (!selectedActivo.value) return;
    configForm.put(route('activos.depreciacion.configurar', selectedActivo.value.id), {
        onSuccess: () => {
            closeConfigModal();
        },
    });
};

const toggleSelection = (id) => {
    const idx = selectedIds.value.indexOf(id);
    if (idx > -1) {
        selectedIds.value.splice(idx, 1);
    } else {
        selectedIds.value.push(id);
    }
};

const selectAll = () => {
    if (selectedIds.value.length === props.activosSinConfigurar.length) {
        selectedIds.value = [];
    } else {
        selectedIds.value = props.activosSinConfigurar.map(a => a.id);
    }
};

const submitMasivo = () => {
    if (selectedIds.value.length === 0) {
        alert('Seleccione al menos un activo');
        return;
    }
    masivoForm.activo_ids = selectedIds.value;
    masivoForm.post(route('activos.depreciacion.masivo'), {
        onSuccess: () => {
            selectedIds.value = [];
            masivoForm.reset();
        },
    });
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
                    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                            <span>Total Activos</span>
                        </div>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ totales.total_activos }}</p>
                    </div>

                    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
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
                <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Filtros</h3>
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

                <!-- Tabs Navigation -->
                <div class="border-b border-gray-200">
                    <nav class="-mb-px flex space-x-6">
                        <button @click="activeTab = 'configurados'" :class="[activeTab === 'configurados' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700', 'whitespace-nowrap border-b-2 py-3 px-1 text-sm font-medium']">
                            Activos Configurados <span class="ml-2 rounded-full bg-indigo-100 px-2 py-0.5 text-xs font-semibold text-indigo-700">{{ activos.length }}</span>
                        </button>
                        <button @click="activeTab = 'sinConfigurar'" :class="[activeTab === 'sinConfigurar' ? 'border-orange-500 text-orange-600' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700', 'whitespace-nowrap border-b-2 py-3 px-1 text-sm font-medium']">
                            Sin Configurar <span class="ml-2 rounded-full bg-orange-100 px-2 py-0.5 text-xs font-semibold text-orange-700">{{ totales.sin_configurar || 0 }}</span>
                        </button>
                    </nav>
                </div>

                <!-- Tab: Activos Configurados -->
                <div v-show="activeTab === 'configurados'" class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
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
                                    <th class="px-4 py-3">Acciones</th>
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
                                    <td class="px-4 py-3">
                                        <button @click="openConfigModal(activo)" class="text-indigo-600 hover:text-indigo-900" title="Editar configuración">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="!activos.length">
                                    <td colspan="10" class="px-4 py-8 text-center text-gray-500">No hay activos con configuración de depreciación</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tab: Activos Sin Configurar -->
                <div v-show="activeTab === 'sinConfigurar'" class="space-y-6">
                    <!-- Form Masivo -->
                    <div v-if="selectedIds.length > 0" class="rounded-xl border border-orange-200 bg-orange-50 p-4 shadow-sm">
                        <h4 class="font-semibold text-orange-800 mb-3">Configurar {{ selectedIds.length }} activos seleccionados</h4>
                        <form @submit.prevent="submitMasivo" class="grid gap-4 sm:grid-cols-4">
                            <div>
                                <label class="text-xs font-semibold text-gray-700">Vida Útil (años) *</label>
                                <input v-model.number="masivoForm.vida_util_anos" type="number" min="1" max="100" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500" required />
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-gray-700">Valor Residual</label>
                                <input v-model.number="masivoForm.valor_residual" type="number" min="0" step="0.01" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500" />
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-gray-700">Método</label>
                                <select v-model="masivoForm.metodo_depreciacion" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500">
                                    <option value="LINEAL">Línea Recta</option>
                                    <option value="SALDO_DECRECIENTE">Saldo Decreciente</option>
                                    <option value="UNIDADES_PRODUCIDAS">Unidades Producidas</option>
                                </select>
                            </div>
                            <div class="flex items-end">
                                <button type="submit" :disabled="masivoForm.processing" class="w-full rounded-md bg-orange-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-orange-700 disabled:opacity-50">
                                    Aplicar a Seleccionados
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-100 text-left text-sm text-gray-700">
                                <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                                    <tr>
                                        <th class="px-4 py-3">
                                            <input type="checkbox" @change="selectAll" :checked="selectedIds.length === activosSinConfigurar.length && activosSinConfigurar.length > 0" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                                        </th>
                                        <th class="px-4 py-3">Código</th>
                                        <th class="px-4 py-3">Activo</th>
                                        <th class="px-4 py-3">Área</th>
                                        <th class="px-4 py-3">Clasificación</th>
                                        <th class="px-4 py-3">Fecha Adq.</th>
                                        <th class="px-4 py-3">Precio</th>
                                        <th class="px-4 py-3">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="activo in activosSinConfigurar" :key="activo.id" class="border-b border-gray-100 hover:bg-gray-50">
                                        <td class="px-4 py-3">
                                            <input type="checkbox" :checked="selectedIds.includes(activo.id)" @change="toggleSelection(activo.id)" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                                        </td>
                                        <td class="px-4 py-3 font-mono text-xs text-gray-600">{{ activo.codigo_inventario }}</td>
                                        <td class="px-4 py-3 font-medium text-gray-900">{{ activo.nombre_activo }}</td>
                                        <td class="px-4 py-3 text-gray-700">{{ activo.area || '-' }}</td>
                                        <td class="px-4 py-3 text-gray-700">{{ activo.clasificacion || '-' }}</td>
                                        <td class="px-4 py-3 text-gray-700">{{ activo.fecha_adquisicion || '-' }}</td>
                                        <td class="px-4 py-3 text-gray-900">{{ formatCurrency(activo.precio_adquisicion) }}</td>
                                        <td class="px-4 py-3">
                                            <button @click="openConfigModal(activo)" class="text-emerald-600 hover:text-emerald-900 font-semibold text-sm">
                                                Configurar
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="!activosSinConfigurar.length">
                                        <td colspan="8" class="px-4 py-8 text-center text-gray-500">Todos los activos tienen configuración de depreciación</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Modal Configurar Depreciación -->
        <div v-if="showConfigModal" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="flex min-h-screen items-end justify-center px-4 pb-20 pt-4 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeConfigModal"></div>
                <span class="hidden sm:inline-block sm:h-screen sm:align-middle">&#8203;</span>
                <div class="inline-block transform overflow-hidden rounded-lg bg-white text-left align-bottom shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:align-middle">
                    <form @submit.prevent="submitConfig">
                        <div class="bg-white px-4 pb-4 pt-5 sm:p-6">
                            <h3 class="text-lg font-medium text-gray-900">Configurar Depreciación</h3>
                            <p class="mt-1 text-sm text-gray-500">{{ selectedActivo?.nombre_activo }}</p>
                            <div class="mt-4 space-y-4">
                                <div>
                                    <label class="text-sm font-medium text-gray-700">Vida Útil (años) *</label>
                                    <input v-model.number="configForm.vida_util_anos" type="number" min="1" max="100" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required />
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-700">Valor Residual</label>
                                    <input v-model.number="configForm.valor_residual" type="number" min="0" step="0.01" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-700">Método de Depreciación</label>
                                    <select v-model="configForm.metodo_depreciacion" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="LINEAL">Línea Recta</option>
                                        <option value="SALDO_DECRECIENTE">Saldo Decreciente</option>
                                        <option value="UNIDADES_PRODUCIDAS">Unidades Producidas</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                            <button type="submit" :disabled="configForm.processing" class="inline-flex w-full justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 sm:ml-3 sm:w-auto disabled:opacity-50">Guardar</button>
                            <button type="button" @click="closeConfigModal" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">Cancelar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
