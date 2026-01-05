<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, usePage, router } from '@inertiajs/vue3';
import { computed, reactive, ref, watch } from 'vue';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
    activos: { type: Object, default: () => ({ data: [] }) },
    personal: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
});

const page = usePage();
const can = (permission) => page.props.auth.user?.permissions?.includes(permission);

const busqueda = ref(props.filters?.search || '');
const forms = reactive({});

// Debounced search
let searchTimeout = null;
watch(busqueda, (value) => {
    if (searchTimeout) clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(route('activos.trazabilidad'), { search: value }, {
            preserveState: true,
            preserveScroll: true,
        });
    }, 500);
});

const formFor = (activo) => {
    if (!forms[activo.id]) {
        forms[activo.id] = useForm({
            personal_id: activo.personal_id || '',
            fecha_asignacion: new Date().toISOString().slice(0, 10),
            motivo: '',
        });
    }
    return forms[activo.id];
};

const registrarMovimiento = (activo) => {
    const form = formFor(activo);
    form.post(route('activos.trazabilidad.actualizar', activo.id), {
        preserveScroll: true,
        onSuccess: () => {
            form.defaults({
                personal_id: form.personal_id,
                fecha_asignacion: form.fecha_asignacion,
            });
            form.reset('motivo');
        },
    });
};

const activosList = computed(() => props.activos.data || []);
const activosConHistorial = computed(() => activosList.value.filter((a) => (a.historial || []).length > 0));
</script>

<template>
    <Head title="Trazabilidad de activos" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="flex items-center gap-2 text-xl font-semibold leading-tight text-gray-800">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                        <span>Trazabilidad de activos</span>
                    </div>
                    <p class="text-sm text-gray-500">Historial de asignaciones por activo para auditar cambios de responsable.</p>
                </div>
                    <div class="flex flex-wrap gap-2 text-sm">
                        <a
                            :href="route('activos.index')"
                            class="inline-flex items-center gap-2 rounded-md border border-gray-200 px-3 py-2 text-gray-700 shadow-sm transition hover:border-indigo-300 hover:text-indigo-700"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 5h18M9 3v2m6-2v2M5 9h14v10H5z" />
                            </svg>
                            Inventario
                        </a>
                        <a
                            :href="route('activos.reportes')"
                            class="inline-flex items-center gap-2 rounded-md border border-gray-200 px-3 py-2 text-gray-700 shadow-sm transition hover:border-indigo-300 hover:text-indigo-700"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h10m-6 6h12" />
                            </svg>
                            Reportes
                        </a>
                    </div>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <div class="grid gap-4 sm:grid-cols-3">
                    <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Activos en página</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ activosList.length }}</p>
                    </div>
                    <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Con historial</p>
                        <p class="text-2xl font-semibold text-indigo-700 dark:text-indigo-400">{{ activosConHistorial.length }}</p>
                    </div>
                    <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Total páginas</p>
                        <p class="text-2xl font-semibold text-amber-600 dark:text-amber-400">{{ props.activos.last_page || 1 }}</p>
                    </div>
                </div>

                <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Historial por activo</h3>
                            <p class="text-sm text-gray-500">Filtra por código, nombre, responsable, área o ubicación.</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <input
                                v-model="busqueda"
                                type="search"
                                placeholder="Buscar..."
                                class="w-full rounded-md border border-gray-200 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:w-64"
                            />
                        </div>
                    </div>

                    <div class="mt-4 space-y-4">
                        <div
                            v-for="activo in activosList"
                            :key="activo.id"
                            class="rounded-xl border border-gray-100 bg-gray-50/60 p-4 shadow-sm"
                        >
                            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 pb-3">
                                <div>
                                    <p class="font-semibold text-gray-900">
                                        {{ activo.codigo_inventario }} · {{ activo.nombre_activo }}
                                    </p>
                                    <p class="text-sm text-gray-600">
                                        {{ activo.area || 'Sin área' }} • {{ activo.ubicacion || 'Sin ubicación' }} • Responsable actual: {{ activo.responsable || 'No asignado' }}
                                    </p>
                                </div>
                                <span
                                    class="inline-flex items-center rounded-full px-2 py-1 text-xs font-semibold"
                                    :class="{
                                        'bg-green-100 text-green-800': activo.estado === 'BUENO',
                                        'bg-amber-100 text-amber-800': activo.estado === 'REGULAR',
                                        'bg-rose-100 text-rose-800': activo.estado === 'MALO',
                                        'bg-gray-200 text-gray-700': !['BUENO', 'REGULAR', 'MALO'].includes(activo.estado),
                                    }"
                                >
                                    {{ activo.estado || 'SIN ESTADO' }}
                                </span>
                            </div>

                            <div v-if="(activo.historial || []).length" class="mt-4 space-y-3">
                                <div
                                    v-for="mov in activo.historial"
                                    :key="mov.id"
                                    class="flex gap-3 rounded-lg border border-gray-200 bg-white p-4 shadow-sm"
                                >
                                    <div class="mt-1 h-3 w-3 shrink-0 rounded-full bg-indigo-500" />
                                    <div class="flex-1 space-y-1">
                                        <div class="flex flex-wrap items-center justify-between gap-2">
                                            <p class="text-sm font-semibold text-gray-900">Transferido a {{ mov.hacia || 'Sin registro' }}</p>
                                            <span class="text-xs text-gray-500">{{ mov.fecha_asignacion }}</span>
                                        </div>
                                        <p class="text-sm text-gray-700">Desde: {{ mov.desde || 'Sin asignación previa' }}</p>
                                        <p class="text-xs text-gray-600">Área: {{ mov.area_desde || 'Sin área' }} → {{ mov.area_hacia || 'Sin área' }}</p>
                                        <p v-if="mov.motivo" class="text-sm text-gray-600">Motivo: {{ mov.motivo }}</p>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="mt-4 rounded-lg border border-dashed border-gray-300 bg-white p-4 text-sm text-gray-600">
                                Sin historial de asignaciones registrado.
                            </div>
                        </div>
                        <div v-if="!activosList.length" class="rounded-lg border border-dashed border-gray-300 bg-white p-6 text-center text-sm text-gray-600 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400">
                            No hay activos que coincidan con la búsqueda.
                        </div>
                    </div>
                    
                    <!-- Pagination -->
                    <div v-if="props.activos.last_page > 1" class="mt-4">
                        <Pagination :links="props.activos.links" />
                    </div>
                </div>

                <div v-if="can('activos.manage')" class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                    Si registras nuevas asignaciones en el módulo de activos, aparecerán aquí automáticamente para respaldar la trazabilidad.
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
