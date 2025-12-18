<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    activo: {
        type: Object,
        required: true,
    },
});

const formatCurrency = (value) => {
    if (value === null || value === undefined || value === '') return 'C$0.00';
    return `C$${Number(value).toLocaleString('es-ES', { minimumFractionDigits: 2 })}`;
};

const formatDate = (dateString) => {
    if (!dateString) return 'No registrada';
    return new Date(dateString).toLocaleDateString('es-ES', { year: 'numeric', month: 'long', day: 'numeric' });
};
</script>

<template>
    <Head :title="`Detalles de ${activo.codigo_inventario}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-100">
                    Detalles del Activo
                </h2>
                <Link
                    :href="route('activos.busqueda')"
                    class="rounded-md bg-gray-200 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
                >
                    Volver a la búsqueda
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-2xl dark:bg-gray-800">
                    <!-- Header del Activo -->
                    <div class="border-b border-gray-100 bg-gray-50 p-6 dark:border-gray-700 dark:bg-gray-800">
                        <div class="md:flex md:items-center md:justify-between">
                            <div class="min-w-0 flex-1">
                                <h1 class="text-2xl font-bold leading-7 text-gray-900 dark:text-gray-100 sm:truncate sm:text-3xl sm:tracking-tight">
                                    {{ activo.nombre_activo }}
                                </h1>
                                <p class="mt-2 text-lg text-indigo-600 dark:text-indigo-400 font-mono">
                                    {{ activo.codigo_inventario }}
                                </p>
                            </div>
                            <div class="mt-4 flex md:ml-4 md:mt-0">
                                <span :class="{
                                    'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300': activo.estado === 'BUENO' || activo.estado === 'Bueno',
                                    'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300': activo.estado === 'REGULAR' || activo.estado === 'Regular',
                                    'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300': activo.estado === 'MALO' || activo.estado === 'Malo'
                                }" class="inline-flex items-center rounded-full px-3 py-0.5 text-sm font-medium">
                                    {{ activo.estado }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="p-6">
                        <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
                            <!-- Información General -->
                            <div class="rounded-xl border border-gray-100 p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800/50">
                                <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                                    <svg class="mr-2 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Información General
                                </h3>
                                <dl class="space-y-3">
                                    <div>
                                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Descripción</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-200">{{ activo.descripcion || 'Sin descripción' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Marca</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-200">{{ activo.marca || '—' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Modelo</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-200">{{ activo.modelo || '—' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Serie</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-200">{{ activo.serie || '—' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Color</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-200">{{ activo.color || '—' }}</dd>
                                    </div>
                                </dl>
                            </div>

                            <!-- Ubicación y Responsable -->
                            <div class="rounded-xl border border-gray-100 p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800/50">
                                <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                                    <svg class="mr-2 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    Ubicación y Asignación
                                </h3>
                                <dl class="space-y-3">
                                    <div>
                                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Área</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-200">{{ activo.area?.nombre || 'No asignada' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Ubicación física</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-200">{{ activo.ubicacion?.nombre || 'No asignada' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Responsable</dt>
                                        <dd class="mt-1 text-sm font-semibold text-indigo-600 dark:text-indigo-400">
                                            {{ activo.personal ? `${activo.personal.nombre} ${activo.personal.apellido}` : 'Sin asignar' }}
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Clasificación</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-200">{{ activo.clasificacion?.nombre || '—' }}</dd>
                                    </div>
                                </dl>
                            </div>

                            <!-- Información Financiera -->
                            <div class="rounded-xl border border-gray-100 p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800/50">
                                <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                                    <svg class="mr-2 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Datos Financieros
                                </h3>
                                <dl class="space-y-3">
                                    <div>
                                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Precio de Adquisición</dt>
                                        <dd class="mt-1 text-lg font-bold text-gray-900 dark:text-gray-100">{{ formatCurrency(activo.precio_adquisicion) }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Fecha de Adquisición</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-200">{{ formatDate(activo.fecha_adquisicion) }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Fuente de Financiamiento</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-200">{{ activo.fuente_financiamiento?.nombre || '—' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Factura / Cheque</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-200">
                                            <span v-if="activo.numero_factura">Fact: {{ activo.numero_factura }}</span>
                                            <span v-if="activo.cheque"> / Chq: {{ activo.cheque.numero_cheque }}</span>
                                            <span v-if="!activo.numero_factura && !activo.cheque">—</span>
                                        </dd>
                                    </div>
                                </dl>
                            </div>

                            <!-- Foto del Activo -->
                            <div v-if="activo.foto" class="md:col-span-2 lg:col-span-3">
                                <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-4">Fotografía del Activo</h3>
                                <div class="overflow-hidden rounded-lg border border-gray-200 shadow-sm dark:border-gray-700">
                                    <img :src="`/storage/${activo.foto}`" :alt="activo.nombre_activo" class="h-96 w-full object-contain bg-gray-100 dark:bg-gray-900" />
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Footer Actions -->
                    <div class="border-t border-gray-100 bg-gray-50 px-6 py-4 flex flex-col sm:flex-row gap-3 dark:border-gray-700 dark:bg-gray-800">
                         <Link 
                            :href="route('activos.acta-asignacion', { activo: activo.id })"
                            class="inline-flex justify-center items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600"
                            title="Descargar Acta de Asignación en PDF"
                            target="_blank"
                         >
                            <svg class="mr-2 -ml-1 h-5 w-5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                            Imprimir Acta
                         </Link>
                         
                         <Link 
                            :href="route('activos.qr', { activo: activo.id })"
                            class="inline-flex justify-center items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600"
                            title="Ver Código QR"
                         >
                            <svg class="mr-2 -ml-1 h-5 w-5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4h2v-4zM6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            Ver QR
                         </Link>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
