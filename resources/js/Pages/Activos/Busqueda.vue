<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    activos: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({ search: '' }),
    },
});

const search = ref(props.filters.search);
const subSearch = ref('');

// Auto-format codigo inventario (adds dashes every 3 digits) - only if input is numeric
const formatCodigoInventario = (value) => {
    if (!value) return '';
    
    // Check if the input contains only digits and existing dashes (looks like a code)
    const cleanForCheck = value.replace(/-/g, '');
    const isNumericCode = /^\d+$/.test(cleanForCheck);
    
    // If it's not purely numeric, return as-is (user is searching by name)
    if (!isNumericCode) {
        return value;
    }
    
    // Remove all non-digit characters
    let digits = value.replace(/\D/g, '');
    
    // If no digits, return empty
    if (!digits) return '';
    
    // Split into groups of 3
    let groups = [];
    for (let i = 0; i < digits.length; i += 3) {
        groups.push(digits.substring(i, i + 3));
    }
    
    // Join with dashes
    return groups.join('-');
};

// Handle search input with auto-formatting
const handleSearchInput = (event) => {
    const cursorPos = event.target.selectionStart;
    const prevValue = event.target.value;
    const prevLength = prevValue.length;
    
    // Format the value
    search.value = formatCodigoInventario(prevValue);
    
    // Adjust cursor position after formatting
    const newLength = search.value.length;
    const diff = newLength - prevLength;
    
    // Set cursor position after Vue updates the DOM
    setTimeout(() => {
        const newPos = Math.max(0, cursorPos + diff);
        event.target.setSelectionRange(newPos, newPos);
    }, 0);
    
    // Trigger search
    handleSearch();
};

// Búsqueda en tiempo real con debounce
let timeout;
const handleSearch = () => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        router.get(route('activos.busqueda'), { search: search.value }, { preserveState: true, replace: true });
    }, 500);
};

// Filtrado del lado del cliente (Sub-búsqueda)
import { computed } from 'vue';
const filteredActivos = computed(() => {
    if (!subSearch.value) return props.activos;
    const query = subSearch.value.toLowerCase();
    return props.activos.filter(activo => 
        activo.codigo_inventario.toLowerCase().includes(query) ||
        activo.nombre_activo.toLowerCase().includes(query) ||
        (activo.responsable && activo.responsable.toLowerCase().includes(query)) ||
        (activo.area && activo.area.toLowerCase().includes(query)) ||
        (activo.ubicacion && activo.ubicacion.toLowerCase().includes(query))
    );
});

// Formato de moneda
const formatCurrency = (value) => {
    return new Intl.NumberFormat('es-NI', { style: 'currency', currency: 'NIO' }).format(value);
};

// Formato de fecha
const formatDate = (dateString) => {
    if (!dateString) return '—';
    return new Date(dateString).toLocaleDateString('es-ES', { year: 'numeric', month: 'long', day: 'numeric' });
};
</script>

<template>
    <Head title="Búsqueda Rápida" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-100">
                Búsqueda Rápida de Activos
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Barra de búsqueda grande -->
                <div class="mb-8">
                    <div class="flex justify-center mb-6">
                        <div class="relative w-full max-w-2xl">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                                <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input
                                v-model="search"
                                @input="handleSearchInput"
                                type="text"
                                class="block w-full rounded-2xl border-gray-300 bg-white py-4 pl-12 pr-4 text-lg shadow-lg focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
                                placeholder="Código, nombre de activo o responsable..."
                                autofocus
                            />
                        </div>
                    </div>

                    <!-- Sub-búsqueda (Filtro local) -->
                    <div v-if="activos.length > 0" class="flex justify-center">
                        <div class="relative w-full max-w-lg">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <svg class="h-5 w-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                                </svg>
                            </div>
                            <input
                                v-model="subSearch"
                                type="text"
                                class="block w-full rounded-full border-gray-200 bg-gray-50 py-2 pl-10 pr-4 text-sm shadow-sm focus:border-indigo-500 focus:bg-white focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                placeholder="Filtrar dentro de los resultados..."
                            />
                        </div>
                    </div>
                </div>

                <!-- Resultados -->
                <div v-if="search && activos.length === 0" class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="mt-4 text-lg text-gray-500">No se encontraron activos con "{{ search }}".</p>
                </div>

                <div v-else-if="!search" class="text-center py-12">
                    <p class="text-lg text-gray-400">Escribe algo para comenzar a buscar...</p>
                </div>
                
                <div v-else-if="filteredActivos.length === 0" class="text-center py-8">
                     <p class="text-gray-500">No hay coincidencias en el filtro local para "{{ subSearch }}".</p>
                </div>

                <div v-else class="grid gap-6 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-3">
                    <div v-for="activo in filteredActivos" :key="activo.id" class="group relative flex flex-col overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm transition hover:shadow-md dark:border-gray-700 dark:bg-gray-800">
                        <div class="absolute top-0 right-0 mt-3 mr-3">
                             <span :class="{
                                'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300': activo.estado === 'Bueno',
                                'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300': activo.estado === 'Regular',
                                'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300': activo.estado === 'Malo'
                            }" class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold">
                                {{ activo.estado }}
                            </span>
                        </div>
                        
                        <div class="p-5">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-1 font-mono tracking-tight">
                                {{ activo.codigo_inventario }}
                            </h3>
                            <p class="text-sm font-medium text-indigo-600 dark:text-indigo-400 mb-4 line-clamp-2 min-h-[1.25rem]">
                                {{ activo.nombre_activo }}
                            </p>
                            
                            <dl class="space-y-3">
                                <div v-if="activo.responsable">
                                    <dt class="flex items-center text-xs font-medium text-gray-500 dark:text-gray-400">
                                        <svg class="mr-1.5 h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                        Responsable
                                    </dt>
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-200 pl-5">{{ activo.responsable }}</dd>
                                </div>
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <dt class="flex items-center text-xs font-medium text-gray-500 dark:text-gray-400">
                                            <svg class="mr-1.5 h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                                            Área
                                        </dt>
                                        <dd class="mt-1 truncate text-xs text-gray-700 dark:text-gray-300 pl-5" :title="activo.area">{{ activo.area || '—' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="flex items-center text-xs font-medium text-gray-500 dark:text-gray-400">
                                            <svg class="mr-1.5 h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                            Ubicación
                                        </dt>
                                        <dd class="mt-1 truncate text-xs text-gray-700 dark:text-gray-300 pl-5" :title="activo.ubicacion">{{ activo.ubicacion || '—' }}</dd>
                                    </div>
                                </div>
                            </dl>
                        </div>

                        <div class="mt-auto border-t border-gray-100 bg-gray-50 px-5 py-3 dark:border-gray-700 dark:bg-gray-800/50">
                            <Link :href="route('activos.show', activo.id)" class="flex items-center justify-center text-sm font-medium text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300">
                                Ver detalles
                                <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
