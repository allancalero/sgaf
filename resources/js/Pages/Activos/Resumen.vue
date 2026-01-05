<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({})
    }
});

const page = usePage();
const canView = computed(() => page.props.auth.user?.permissions?.includes('activos.view'));

const cards = computed(() => [
    { label: 'Áreas', value: props.stats.areas ?? 0, color: 'from-indigo-500 to-sky-500' },
    { label: 'Ubicaciones', value: props.stats.ubicaciones ?? 0, color: 'from-emerald-500 to-lime-500' },
    { label: 'Personal', value: props.stats.personal ?? 0, color: 'from-amber-500 to-orange-500' },
    { label: 'Activos', value: props.stats.activos ?? 0, color: 'from-violet-500 to-fuchsia-500' },
    { label: 'Cheques', value: props.stats.cheques ?? 0, color: 'from-rose-500 to-red-500' },
]);
</script>

<template>
    <Head title="Activos" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800">
                        Resumen de activos
                    </h2>
                    <p class="text-sm text-gray-500">
                        Conteos rápidos de áreas, ubicaciones, personal, activos y cheques.
                    </p>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="card in cards"
                        :key="card.label"
                        class="rounded-xl border border-gray-200 bg-white shadow-sm"
                    >
                        <div
                            class="h-2 rounded-t-xl bg-gradient-to-r"
                            :class="card.color"
                        />
                        <div class="p-4">
                            <p class="text-sm font-medium text-gray-500">
                                {{ card.label }}
                            </p>
                            <p class="mt-2 text-3xl font-bold text-gray-900">
                                {{ card.value }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-dashed border-gray-300 bg-white p-6 text-sm text-gray-600">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-semibold text-gray-800">Acciones rápidas</p>
                            <p class="text-gray-500">Consulta el inventario o registra un nuevo activo.</p>
                        </div>
                        <a
                            v-if="canView"
                            :href="route('activos.index')"
                            class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700"
                        >
                            Ir al inventario
                        </a>
                        <p v-else class="text-gray-600">Sin permiso para navegar al inventario.</p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
