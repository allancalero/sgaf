<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router, usePage, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import Swal from 'sweetalert2';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
    tipos: {
        type: Object,
        default: () => ({ data: [] }),
    },
    clasificaciones: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();
const can = (permission) => page.props.auth.user?.permissions?.includes(permission);
const canManage = computed(() => can('catalogos.manage'));

const createForm = useForm({
    nombre: '',
    clasificacion_id: '',
});

const editForm = useForm({
    id: null,
    nombre: '',
    clasificacion_id: '',
});

const editing = ref(false);

const startEdit = (tipo) => {
    editing.value = true;
    editForm.id = tipo.id;
    editForm.nombre = tipo.nombre;
    editForm.clasificacion_id = tipo.clasificacion_id ?? '';
};

const cancelEdit = () => {
    editing.value = false;
    editForm.reset('id', 'nombre', 'clasificacion_id');
};

const submitCreate = () => {
    createForm.post(route('tipos.store'), {
        onSuccess: () => createForm.reset('nombre', 'clasificacion_id'),
    });
};

const submitEdit = () => {
    editForm.put(route('tipos.update', editForm.id), {
        onSuccess: () => {
            cancelEdit();
        },
    });
};

const destroyTipo = (id) => {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "Esta acción no se puede revertir",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route('tipos.destroy', id));
        }
    });
};
</script>

<template>
    <Head title="Tipos de activo" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800">Tipos de activo</h2>
                    <p class="text-sm text-gray-500">Relaciona tipos con su clasificación.</p>
                </div>
            </div>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-6xl space-y-8 sm:px-6 lg:px-8">
                <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                        <h3 class="text-lg font-semibold text-gray-800">Crear tipo</h3>
                        <p class="text-sm text-gray-500">Ingresa un nombre y selecciona la clasificación.</p>

                        <form class="mt-4 space-y-4" @submit.prevent="submitCreate">
                            <div>
                                <label class="text-sm font-medium text-gray-700">Nombre</label>
                                <input
                                    v-model="createForm.nombre"
                                    type="text"
                                    class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    required
                                    maxlength="255"
                                />
                                <p v-if="createForm.errors.nombre" class="mt-1 text-sm text-red-600">
                                    {{ createForm.errors.nombre }}
                                </p>
                            </div>

                            <div>
                                <label class="text-sm font-medium text-gray-700">Clasificación</label>
                                <select
                                    v-model="createForm.clasificacion_id"
                                    class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    required
                                >
                                    <option value="" disabled>Selecciona clasificación</option>
                                    <option
                                        v-for="clasificacion in props.clasificaciones"
                                        :key="clasificacion.id"
                                        :value="clasificacion.id"
                                    >
                                        {{ clasificacion.nombre }}
                                    </option>
                                </select>
                                <p v-if="createForm.errors.clasificacion_id" class="mt-1 text-sm text-red-600">
                                    {{ createForm.errors.clasificacion_id }}
                                </p>
                            </div>

                            <div class="flex justify-end">
                                <button
                                    type="submit"
                                    :disabled="createForm.processing"
                                    class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50"
                                >
                                    Guardar
                                </button>
                            </div>
                        </form>
                    </div>

                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                    <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800">Listado</h3>
                            <p class="text-sm text-gray-500">Selecciona para editar o eliminar.</p>
                        </div>
                    </div>
                    <div class="max-h-[480px] overflow-auto">
                        <table class="min-w-full text-left text-sm text-gray-700">
                            <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                                <tr>
                                    <th class="px-4 py-3">ID</th>
                                    <th class="px-4 py-3">Nombre</th>
                                    <th class="px-4 py-3">Clasificación</th>
                                    <th class="px-4 py-3">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="tipo in props.tipos.data"
                                    :key="tipo.id"
                                    class="border-b border-gray-100 hover:bg-gray-50"
                                >
                                    <td class="px-4 py-3 text-gray-500">{{ tipo.id }}</td>
                                    <td class="px-4 py-3 font-medium text-gray-900">{{ tipo.nombre }}</td>
                                    <td class="px-4 py-3 text-gray-700">{{ tipo.clasificacion || 'Sin clasificación' }}</td>
                                    <td class="px-4 py-3 space-x-2 text-sm">
                                        <button
                                            v-if="canManage"
                                            class="inline-flex items-center justify-center rounded-md p-1.5 text-indigo-600 hover:bg-indigo-50 transition"
                                            type="button"
                                            @click="startEdit(tipo)"
                                            title="Editar"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        <button
                                            v-if="canManage"
                                            class="inline-flex items-center justify-center rounded-md p-1.5 text-red-600 hover:bg-red-50 transition"
                                            type="button"
                                            @click="destroyTipo(tipo.id)"
                                            title="Eliminar"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="!props.tipos.data.length">
                                    <td colspan="4" class="px-4 py-4 text-center text-gray-500">
                                        Sin registros
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Paginación -->
                    <Pagination 
                        :links="tipos.links" 
                        :from="tipos.from" 
                        :to="tipos.to" 
                        :total="tipos.total" 
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
