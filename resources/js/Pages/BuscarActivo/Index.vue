<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import axios from 'axios';

const searchQuery = ref('');
const filterQuery = ref('');
const results = ref([]);
const isLoading = ref(false);
const selectedActivo = ref(null);

// Debounce timer
let debounceTimer = null;

const formatCurrency = (value) => {
    if (!value) return '$0.00';
    return new Intl.NumberFormat('es-PA', {
        style: 'currency',
        currency: 'USD',
    }).format(value);
};

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('es-PA');
};

const getEstadoBadgeClass = (estado) => {
    const classes = {
        'ACTIVO': 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-400',
        'INACTIVO': 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-400',
        'EN MANTENIMIENTO': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-400',
        'DADO DE BAJA': 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
    };
    return classes[estado] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
};

// Filter results locally
const filteredResults = computed(() => {
    if (!filterQuery.value) return results.value;
    
    const filter = filterQuery.value.toLowerCase();
    return results.value.filter(activo => {
        return [
            activo.codigo_inventario,
            activo.nombre_activo,
            activo.marca,
            activo.modelo,
            activo.serie,
            activo.color,
            activo.area,
            activo.ubicacion,
            activo.clasificacion,
            activo.responsable,
        ].some(field => String(field || '').toLowerCase().includes(filter));
    });
});

const search = async () => {
    if (!searchQuery.value || searchQuery.value.length < 2) {
        results.value = [];
        selectedActivo.value = null;
        filterQuery.value = '';
        return;
    }
    
    isLoading.value = true;
    
    try {
        const response = await axios.get(route('activos.buscar'), {
            params: { q: searchQuery.value },
            headers: { 'Accept': 'application/json' }
        });
        results.value = response.data;
        filterQuery.value = '';
        
        // Auto-select if exactly one result
        if (results.value.length === 1) {
            selectedActivo.value = results.value[0];
        } else {
            selectedActivo.value = null;
        }
    } catch (error) {
        console.error('Error searching:', error);
        results.value = [];
    } finally {
        isLoading.value = false;
    }
};

// Debounced search
watch(searchQuery, () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(search, 300);
});

const selectActivo = (activo) => {
    selectedActivo.value = activo;
};

const clearSearch = () => {
    searchQuery.value = '';
    results.value = [];
    selectedActivo.value = null;
    filterQuery.value = '';
};
</script>

<template>
    <Head title="Búsqueda Rápida de AF" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-100">Búsqueda Rápida de AF</h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Busca activos por código, nombre, o responsable</p>
            </div>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <!-- Search Box -->
                <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                            <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Código, nombre del activo, o nombre del responsable..."
                            class="block w-full rounded-lg border-gray-300 py-4 pl-12 pr-12 text-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400"
                            autofocus
                        />
                        <button
                            v-if="searchQuery"
                            @click="clearSearch"
                            class="absolute inset-y-0 right-0 flex items-center pr-4 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    
                    <!-- Loading indicator -->
                    <div v-if="isLoading" class="mt-4 flex items-center justify-center text-gray-500">
                        <svg class="h-5 w-5 animate-spin mr-2" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Buscando...
                    </div>
                </div>

                <!-- Results List -->
                <div v-if="results.length > 0 && !selectedActivo" class="mt-4 rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800 overflow-hidden">
                    <div class="border-b border-gray-100 px-4 py-3 dark:border-gray-700">
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-sm text-gray-500 dark:text-gray-400">{{ results.length }} resultado(s) encontrado(s)</span>
                            <div class="relative flex-1 max-w-xs">
                                <input
                                    v-model="filterQuery"
                                    type="text"
                                    placeholder="Filtrar resultados..."
                                    class="block w-full rounded-md border-gray-300 py-1.5 pl-8 pr-3 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400"
                                />
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-2">
                                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                        <span v-if="filterQuery" class="mt-2 block text-xs text-gray-400 dark:text-gray-500">
                            Mostrando {{ filteredResults.length }} de {{ results.length }} resultados
                        </span>
                    </div>
                    <ul class="divide-y divide-gray-100 dark:divide-gray-700">
                        <li
                            v-for="activo in filteredResults"
                            :key="activo.id"
                            @click="selectActivo(activo)"
                            class="flex cursor-pointer items-center gap-4 px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/50"
                        >
                            <div class="flex-shrink-0">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="truncate font-semibold text-gray-900 dark:text-gray-100">{{ activo.codigo_inventario }}</p>
                                <p class="truncate text-sm text-gray-500 dark:text-gray-400">{{ activo.nombre_activo }}</p>
                                <p v-if="activo.responsable" class="truncate text-xs text-gray-400 dark:text-gray-500">👤 {{ activo.responsable }}</p>
                            </div>
                            <span :class="[getEstadoBadgeClass(activo.estado), 'rounded-full px-2 py-1 text-xs font-medium']">
                                {{ activo.estado }}
                            </span>
                        </li>
                    </ul>
                </div>

                <!-- No results message -->
                <div v-if="searchQuery.length >= 2 && !isLoading && results.length === 0" class="mt-4 rounded-xl border border-gray-200 bg-white p-8 text-center shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No se encontraron activos</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Intenta con otro código o nombre</p>
                </div>

                <!-- Selected Asset Detail -->
                <div v-if="selectedActivo" class="mt-4 rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800 overflow-hidden">
                    <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-700 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Detalle del Activo</h3>
                        <button
                            @click="selectedActivo = null"
                            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    
                    <div class="p-6">
                        <div class="flex flex-col sm:flex-row gap-6">
                            <!-- Asset Image -->
                            <div class="flex-shrink-0">
                                <div v-if="selectedActivo.foto" class="h-32 w-32 rounded-lg overflow-hidden">
                                    <img :src="'/storage/' + selectedActivo.foto" :alt="selectedActivo.nombre_activo" class="h-full w-full object-cover" />
                                </div>
                                <div v-else class="flex h-32 w-32 items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-700">
                                    <svg class="h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                            </div>
                            
                            <!-- Asset Info -->
                            <div class="flex-1 grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Código Inventario</label>
                                    <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100">{{ selectedActivo.codigo_inventario }}</p>
                                </div>
                                <div>
                                    <label class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Estado</label>
                                    <p class="mt-1">
                                        <span :class="[getEstadoBadgeClass(selectedActivo.estado), 'rounded-full px-3 py-1 text-sm font-medium']">
                                            {{ selectedActivo.estado }}
                                        </span>
                                    </p>
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Nombre</label>
                                    <p class="mt-1 text-gray-900 dark:text-gray-100">{{ selectedActivo.nombre_activo }}</p>
                                </div>
                                <div>
                                    <label class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Área</label>
                                    <p class="mt-1 text-gray-900 dark:text-gray-100">{{ selectedActivo.area || '-' }}</p>
                                </div>
                                <div>
                                    <label class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Ubicación</label>
                                    <p class="mt-1 text-gray-900 dark:text-gray-100">{{ selectedActivo.ubicacion || '-' }}</p>
                                </div>
                                <div>
                                    <label class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Clasificación</label>
                                    <p class="mt-1 text-gray-900 dark:text-gray-100">{{ selectedActivo.clasificacion || '-' }}</p>
                                </div>
                                <div>
                                    <label class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Responsable</label>
                                    <p class="mt-1 text-gray-900 dark:text-gray-100">{{ selectedActivo.responsable || '-' }}</p>
                                </div>
                                <div>
                                    <label class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Precio Adquisición</label>
                                    <p class="mt-1 text-gray-900 dark:text-gray-100">{{ formatCurrency(selectedActivo.precio_adquisicion) }}</p>
                                </div>
                                <div>
                                    <label class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Fecha Adquisición</label>
                                    <p class="mt-1 text-gray-900 dark:text-gray-100">{{ formatDate(selectedActivo.fecha_adquisicion) }}</p>
                                </div>
                                <div>
                                    <label class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Marca</label>
                                    <p class="mt-1 text-gray-900 dark:text-gray-100">{{ selectedActivo.marca || '-' }}</p>
                                </div>
                                <div>
                                    <label class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Modelo</label>
                                    <p class="mt-1 text-gray-900 dark:text-gray-100">{{ selectedActivo.modelo || '-' }}</p>
                                </div>
                                <div>
                                    <label class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Serie</label>
                                    <p class="mt-1 text-gray-900 dark:text-gray-100">{{ selectedActivo.serie || '-' }}</p>
                                </div>
                                <div>
                                    <label class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Color</label>
                                    <p class="mt-1 text-gray-900 dark:text-gray-100">{{ selectedActivo.color || '-' }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Actions -->
                        <div class="mt-6 flex gap-3">
                            <Link
                                :href="route('activos.index') + '?search=' + selectedActivo.codigo_inventario"
                                class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition"
                            >
                                <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                Ver en Listado
                            </Link>
                            <Link
                                :href="route('activos.trazabilidad') + '?activo_id=' + selectedActivo.id"
                                class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50 transition dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600"
                            >
                                <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                Ver Trazabilidad
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
