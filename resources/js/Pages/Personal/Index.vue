<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    personal: {
        type: Array,
        default: () => [],
    },
    cargos: {
        type: Array,
        default: () => [],
    },
    areas: {
        type: Array,
        default: () => [],
    },
    ubicaciones: {
        type: Array,
        default: () => [],
    },
});

const createForm = useForm({
    nombre: '',
    apellido: '',
    cargo_id: '',
    area_id: '',
    ubicacion_id: '',
    telefono: '',
    email: '',
    numero_empleado: '',
    numero_cedula: '',
    fecha_nac: '',
    edad: '',
    direccion: '',
    profesion: '',
    estado: 'ACTIVO',
    foto: '',
});

const editForm = useForm({
    id: null,
    nombre: '',
    apellido: '',
    cargo_id: '',
    area_id: '',
    ubicacion_id: '',
    telefono: '',
    email: '',
    numero_empleado: '',
    numero_cedula: '',
    fecha_nac: '',
    edad: '',
    direccion: '',
    profesion: '',
    estado: 'ACTIVO',
    foto: '',
});

const editing = ref(false);

const startEdit = (persona) => {
    editing.value = true;
    editForm.id = persona.id;
    editForm.nombre = persona.nombre;
    editForm.apellido = persona.apellido;
    editForm.cargo_id = persona.cargo_id ?? '';
    editForm.area_id = persona.area_id ?? '';
    editForm.ubicacion_id = persona.ubicacion_id ?? '';
    editForm.telefono = persona.telefono || '';
    editForm.email = persona.email || '';
    editForm.numero_empleado = persona.numero_empleado || '';
    editForm.numero_cedula = persona.numero_cedula || '';
    editForm.fecha_nac = persona.fecha_nac || '';
    editForm.edad = persona.edad || '';
    editForm.direccion = persona.direccion || '';
    editForm.profesion = persona.profesion || '';
    editForm.estado = persona.estado || 'ACTIVO';
    editForm.foto = persona.foto || '';
};

const cancelEdit = () => {
    editing.value = false;
    editForm.reset(
        'id',
        'nombre',
        'apellido',
        'cargo_id',
        'area_id',
        'ubicacion_id',
        'telefono',
        'email',
        'numero_empleado',
        'numero_cedula',
        'fecha_nac',
        'edad',
        'direccion',
        'profesion',
        'estado',
        'foto'
    );
};

const submitCreate = () => {
    createForm.post(route('personal.store'), {
        onSuccess: () => createForm.reset(),
    });
};

const submitEdit = () => {
    editForm.put(route('personal.update', editForm.id), {
        onSuccess: () => {
            cancelEdit();
        },
    });
};

const destroyPersonal = (id) => {
    router.delete(route('personal.destroy', id));
};
</script>

<template>
    <Head title="Personal" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800">Personal</h2>
                    <p class="text-sm text-gray-500">Gestiona colaboradores y su asignación organizacional.</p>
                </div>
            </div>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-7xl space-y-8 sm:px-6 lg:px-8">
                <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                        <h3 class="text-lg font-semibold text-gray-800">Crear persona</h3>
                        <p class="text-sm text-gray-500">Los campos marcados como selección son obligatorios.</p>

                        <form class="mt-4 space-y-4" @submit.prevent="submitCreate">
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="text-sm font-medium text-gray-700">Nombre</label>
                                    <input
                                        v-model="createForm.nombre"
                                        type="text"
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        required
                                        maxlength="100"
                                    />
                                    <p v-if="createForm.errors.nombre" class="mt-1 text-sm text-red-600">
                                        {{ createForm.errors.nombre }}
                                    </p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-700">Apellido</label>
                                    <input
                                        v-model="createForm.apellido"
                                        type="text"
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        required
                                        maxlength="100"
                                    />
                                    <p v-if="createForm.errors.apellido" class="mt-1 text-sm text-red-600">
                                        {{ createForm.errors.apellido }}
                                    </p>
                                </div>

                                <div>
                                    <label class="text-sm font-medium text-gray-700">Cargo</label>
                                    <select
                                        v-model="createForm.cargo_id"
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        required
                                    >
                                        <option value="" disabled>Selecciona cargo</option>
                                        <option v-for="cargo in props.cargos" :key="cargo.id" :value="cargo.id">
                                            {{ cargo.nombre }}
                                        </option>
                                    </select>
                                    <p v-if="createForm.errors.cargo_id" class="mt-1 text-sm text-red-600">
                                        {{ createForm.errors.cargo_id }}
                                    </p>
                                </div>

                                <div>
                                    <label class="text-sm font-medium text-gray-700">Área</label>
                                    <select
                                        v-model="createForm.area_id"
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        required
                                    >
                                        <option value="" disabled>Selecciona área</option>
                                        <option v-for="area in props.areas" :key="area.id" :value="area.id">
                                            {{ area.nombre }}
                                        </option>
                                    </select>
                                    <p v-if="createForm.errors.area_id" class="mt-1 text-sm text-red-600">
                                        {{ createForm.errors.area_id }}
                                    </p>
                                </div>

                                <div>
                                    <label class="text-sm font-medium text-gray-700">Ubicación</label>
                                    <select
                                        v-model="createForm.ubicacion_id"
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        required
                                    >
                                        <option value="" disabled>Selecciona ubicación</option>
                                        <option v-for="ubicacion in props.ubicaciones" :key="ubicacion.id" :value="ubicacion.id">
                                            {{ ubicacion.nombre }}
                                        </option>
                                    </select>
                                    <p v-if="createForm.errors.ubicacion_id" class="mt-1 text-sm text-red-600">
                                        {{ createForm.errors.ubicacion_id }}
                                    </p>
                                </div>

                                <div>
                                    <label class="text-sm font-medium text-gray-700">Teléfono</label>
                                    <input
                                        v-model="createForm.telefono"
                                        type="text"
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        maxlength="100"
                                    />
                                    <p v-if="createForm.errors.telefono" class="mt-1 text-sm text-red-600">
                                        {{ createForm.errors.telefono }}
                                    </p>
                                </div>

                                <div>
                                    <label class="text-sm font-medium text-gray-700">Email</label>
                                    <input
                                        v-model="createForm.email"
                                        type="email"
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        maxlength="255"
                                    />
                                    <p v-if="createForm.errors.email" class="mt-1 text-sm text-red-600">
                                        {{ createForm.errors.email }}
                                    </p>
                                </div>

                                <div>
                                    <label class="text-sm font-medium text-gray-700">N° empleado</label>
                                    <input
                                        v-model="createForm.numero_empleado"
                                        type="text"
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        maxlength="50"
                                    />
                                    <p v-if="createForm.errors.numero_empleado" class="mt-1 text-sm text-red-600">
                                        {{ createForm.errors.numero_empleado }}
                                    </p>
                                </div>

                                <div>
                                    <label class="text-sm font-medium text-gray-700">N° cédula</label>
                                    <input
                                        v-model="createForm.numero_cedula"
                                        type="text"
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        maxlength="50"
                                    />
                                    <p v-if="createForm.errors.numero_cedula" class="mt-1 text-sm text-red-600">
                                        {{ createForm.errors.numero_cedula }}
                                    </p>
                                </div>

                                <div>
                                    <label class="text-sm font-medium text-gray-700">Fecha nacimiento</label>
                                    <input
                                        v-model="createForm.fecha_nac"
                                        type="date"
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                    <p v-if="createForm.errors.fecha_nac" class="mt-1 text-sm text-red-600">
                                        {{ createForm.errors.fecha_nac }}
                                    </p>
                                </div>

                                <div>
                                    <label class="text-sm font-medium text-gray-700">Edad</label>
                                    <input
                                        v-model="createForm.edad"
                                        type="number"
                                        min="0"
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                    <p v-if="createForm.errors.edad" class="mt-1 text-sm text-red-600">
                                        {{ createForm.errors.edad }}
                                    </p>
                                </div>

                                <div class="sm:col-span-2">
                                    <label class="text-sm font-medium text-gray-700">Dirección</label>
                                    <input
                                        v-model="createForm.direccion"
                                        type="text"
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        maxlength="255"
                                    />
                                    <p v-if="createForm.errors.direccion" class="mt-1 text-sm text-red-600">
                                        {{ createForm.errors.direccion }}
                                    </p>
                                </div>

                                <div>
                                    <label class="text-sm font-medium text-gray-700">Profesión</label>
                                    <input
                                        v-model="createForm.profesion"
                                        type="text"
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        maxlength="100"
                                    />
                                    <p v-if="createForm.errors.profesion" class="mt-1 text-sm text-red-600">
                                        {{ createForm.errors.profesion }}
                                    </p>
                                </div>

                                <div>
                                    <label class="text-sm font-medium text-gray-700">Estado</label>
                                    <select
                                        v-model="createForm.estado"
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        required
                                    >
                                        <option value="ACTIVO">ACTIVO</option>
                                        <option value="INACTIVO">INACTIVO</option>
                                    </select>
                                    <p v-if="createForm.errors.estado" class="mt-1 text-sm text-red-600">
                                        {{ createForm.errors.estado }}
                                    </p>
                                </div>

                                <div class="sm:col-span-2">
                                    <label class="text-sm font-medium text-gray-700">Foto (URL)</label>
                                    <input
                                        v-model="createForm.foto"
                                        type="text"
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        maxlength="255"
                                    />
                                    <p v-if="createForm.errors.foto" class="mt-1 text-sm text-red-600">
                                        {{ createForm.errors.foto }}
                                    </p>
                                </div>
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
                    <div class="max-h-[520px] overflow-auto">
                        <table class="min-w-full text-left text-sm text-gray-700">
                            <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                                <tr>
                                    <th class="px-4 py-3">ID</th>
                                    <th class="px-4 py-3">Nombre</th>
                                    <th class="px-4 py-3">Cargo</th>
                                    <th class="px-4 py-3">Área</th>
                                    <th class="px-4 py-3">Ubicación</th>
                                    <th class="px-4 py-3">Estado</th>
                                    <th class="px-4 py-3">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="persona in props.personal"
                                    :key="persona.id"
                                    class="border-b border-gray-100 hover:bg-gray-50"
                                >
                                    <td class="px-4 py-3 text-gray-500">{{ persona.id }}</td>
                                    <td class="px-4 py-3 font-medium text-gray-900">
                                        {{ persona.nombre }} {{ persona.apellido }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-700">{{ persona.cargo || '-' }}</td>
                                    <td class="px-4 py-3 text-gray-700">{{ persona.area || '-' }}</td>
                                    <td class="px-4 py-3 text-gray-700">{{ persona.ubicacion || '-' }}</td>
                                    <td class="px-4 py-3">
                                        <span
                                            class="inline-flex items-center rounded-full px-2 py-1 text-xs font-semibold"
                                            :class="persona.estado === 'ACTIVO'
                                                ? 'bg-green-100 text-green-800'
                                                : 'bg-gray-200 text-gray-700'"
                                        >
                                            {{ persona.estado }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 space-x-2 text-sm">
                                        <button
                                            class="inline-flex items-center justify-center rounded-md p-1.5 text-indigo-600 hover:bg-indigo-50 transition"
                                            type="button"
                                            @click="startEdit(persona)"
                                            title="Editar"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        <button
                                            class="inline-flex items-center justify-center rounded-md p-1.5 text-red-600 hover:bg-red-50 transition"
                                            type="button"
                                            @click="destroyPersonal(persona.id)"
                                            title="Eliminar"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="!props.personal.length">
                                    <td colspan="7" class="px-4 py-4 text-center text-gray-500">
                                        Sin registros
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
