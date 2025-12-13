<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    areas: Array,
    cargos: Array,
    ubicaciones: Array,
    clasificaciones: Array,
    fuentes: Array,
    tipos: Array,
    proveedores: Array,
    personal: Array,
    responsables: Array,
});

const sections = [
    { title: 'Áreas', key: 'areas', columns: ['id', 'nombre', 'estado'] },
    { title: 'Cargos', key: 'cargos', columns: ['id', 'nombre', 'estado'] },
    { title: 'Ubicaciones', key: 'ubicaciones', columns: ['id', 'nombre', 'estado'] },
    { title: 'Clasificaciones', key: 'clasificaciones', columns: ['id', 'nombre'] },
    { title: 'Fuentes de financiamiento', key: 'fuentes', columns: ['id', 'nombre', 'estado'] },
    { title: 'Tipos de activos', key: 'tipos', columns: ['id', 'nombre', 'clasificacion'] },
    { title: 'Proveedores', key: 'proveedores', columns: ['id', 'nombre', 'telefono', 'email'] },
    { title: 'Personal', key: 'personal', columns: ['id', 'nombre', 'apellido', 'cargo', 'area', 'ubicacion', 'email'] },
    { title: 'Responsables', key: 'responsables', columns: ['id', 'nombre', 'cargo', 'area', 'estado'] },
];
</script>

<template>
    <Head title="Catálogos" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800">
                        Catálogos
                    </h2>
                    <p class="text-sm text-gray-500">
                        Listados base: áreas, cargos, ubicaciones, clasificaciones, fuentes, tipos, proveedores, personal y responsables.
                    </p>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl space-y-8 sm:px-6 lg:px-8">
                <section class="grid gap-6 lg:grid-cols-2">
                    <div
                        v-for="section in sections"
                        :key="section.key"
                        class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm"
                    >
                        <div class="border-b border-gray-100 px-4 py-3">
                            <h3 class="text-base font-semibold text-gray-800">
                                {{ section.title }}
                            </h3>
                            <p class="text-xs text-gray-500">
                                {{ (props[section.key]?.length || 0) }} registros
                            </p>
                        </div>
                        <div class="max-h-64 overflow-auto">
                            <table class="min-w-full text-left text-sm text-gray-700">
                                <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                                    <tr>
                                        <th v-for="col in section.columns" :key="col" class="px-3 py-2">
                                            {{ col }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="row in props[section.key] || []"
                                        :key="`${section.key}-${row.id}`"
                                        class="odd:bg-white even:bg-gray-50"
                                    >
                                        <td
                                            v-for="col in section.columns"
                                            :key="col"
                                            class="px-3 py-2 align-top"
                                        >
                                            {{ row[col] ?? '—' }}
                                        </td>
                                    </tr>
                                    <tr v-if="!props[section.key] || props[section.key].length === 0">
                                        <td
                                            :colspan="section.columns.length"
                                            class="px-3 py-4 text-center text-gray-500"
                                        >
                                            Sin datos
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
