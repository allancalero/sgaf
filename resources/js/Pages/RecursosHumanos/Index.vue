<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import Pagination from '@/Components/Pagination.vue';
import Swal from 'sweetalert2';

const page = usePage();
const can = (permission) => page.props.auth.user?.permissions?.includes(permission);
const canManage = computed(() => can('catalogos.manage'));

const props = defineProps({
    personal: {
        type: Object,
        default: () => ({ data: [] }),
    },
    cargos: {
        type: Object,
        default: () => ({ data: [] }),
    },
    todosLosCargos: {
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
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const search = ref(props.filters?.search || '');
const filtroArea = ref(props.filters?.area_id || '');

watch([search, filtroArea], ([newSearch, newArea]) => {
    router.get(
        route('recursos-humanos.index'),
        { search: newSearch, area_id: newArea },
        { preserveState: true, preserveScroll: true, replace: true }
    );
});

// Tab activo
const activeTab = ref('personal');

// Forms para Personal
const createPersonalForm = useForm({
    nombre: '',
    apellido: '',
    cargo_id: '',
    area_id: '',
    ubicacion_id: '',
    telefono: '',
    email: '',
    numero_empleado: '',
    numero_cedula: '',
    sexo: '',
    fecha_nac: '',
    edad: '',
    direccion: '',
    profesion: '',
    estado: 'ACTIVO',
    foto: null,
});

const editPersonalForm = useForm({
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
    sexo: '',
    fecha_nac: '',
    edad: '',
    direccion: '',
    profesion: '',
    estado: 'ACTIVO',
    foto: null,
});

const editingPersonal = ref(false);
const currentFoto = ref(null);
const fotoPreview = ref(null);
const viewingPersonal = ref(null);

// File upload handlers
const handleCreateFotoChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        createPersonalForm.foto = file;
        fotoPreview.value = URL.createObjectURL(file);
    }
};

const handleEditFotoChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        editPersonalForm.foto = file;
        currentFoto.value = URL.createObjectURL(file);
    }
};

// Auto-format cedula number (XXX-XXXXXX-XXXXX format)
const formatCedula = (value, formField) => {
    // Remove all non-alphanumeric characters
    let cleaned = value.replace(/[^a-zA-Z0-9]/g, '').toUpperCase();
    
    // Apply format XXX-XXXXXX-XXXXX
    let formatted = '';
    if (cleaned.length > 0) {
        formatted = cleaned.substring(0, 3);
    }
    if (cleaned.length > 3) {
        formatted += '-' + cleaned.substring(3, 9);
    }
    if (cleaned.length > 9) {
        formatted += '-' + cleaned.substring(9, 14);
    }
    
    // Update the form field
    if (formField === 'create') {
        createPersonalForm.numero_cedula = formatted;
    } else {
        editPersonalForm.numero_cedula = formatted;
    }
};

// Forms para Cargos
const createCargoForm = useForm({
    nombre: '',
    estado: 'ACTIVO',
});

const editCargoForm = useForm({
    id: null,
    nombre: '',
    estado: 'ACTIVO',
});

const editingCargo = ref(false);

// Funciones Personal
const submitCreatePersonal = () => {
    createPersonalForm.post(route('personal.store'), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            createPersonalForm.reset();
            fotoPreview.value = null;
        },
    });
};

const startEditPersonal = (persona) => {
    editingPersonal.value = true;
    editPersonalForm.id = persona.id;
    editPersonalForm.nombre = persona.nombre;
    editPersonalForm.apellido = persona.apellido;
    editPersonalForm.cargo_id = persona.cargo_id ?? '';
    editPersonalForm.area_id = persona.area_id ?? '';
    editPersonalForm.ubicacion_id = persona.ubicacion_id ?? '';
    editPersonalForm.telefono = persona.telefono || '';
    editPersonalForm.email = persona.email || '';
    editPersonalForm.numero_empleado = persona.numero_empleado || '';
    editPersonalForm.numero_cedula = persona.numero_cedula || '';
    editPersonalForm.sexo = persona.sexo || '';
    editPersonalForm.fecha_nac = persona.fecha_nac || '';
    editPersonalForm.edad = persona.edad || '';
    editPersonalForm.direccion = persona.direccion || '';
    editPersonalForm.profesion = persona.profesion || '';
    editPersonalForm.estado = persona.estado || 'ACTIVO';
    editPersonalForm.foto = null; // Reset foto for new upload
    currentFoto.value = persona.foto || null;
};

const cancelEditPersonal = () => {
    editingPersonal.value = false;
    editPersonalForm.reset();
};

const submitEditPersonal = () => {
    editPersonalForm.post(route('personal.update', editPersonalForm.id), {
        preserveScroll: true,
        forceFormData: true,
        _method: 'PUT',
        onSuccess: () => {
            editingPersonal.value = false;
            editPersonalForm.reset();
            currentFoto.value = null;
        },
    });
};

const deletePersonal = (id) => {
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
            router.delete(route('personal.destroy', id), {
                preserveScroll: true,
            });
        }
    });
};

// Funciones Cargos
const submitCreateCargo = () => {
    createCargoForm.post(route('cargos.store'), {
        preserveScroll: true,
        onSuccess: () => createCargoForm.reset(),
    });
};

const startEditCargo = (cargo) => {
    editingCargo.value = true;
    editCargoForm.id = cargo.id;
    editCargoForm.nombre = cargo.nombre;
    editCargoForm.estado = cargo.estado || 'ACTIVO';
};

const cancelEditCargo = () => {
    editingCargo.value = false;
    editCargoForm.reset();
};

const submitEditCargo = () => {
    editCargoForm.put(route('cargos.update', editCargoForm.id), {
        preserveScroll: true,
        onSuccess: () => {
            editingCargo.value = false;
            editCargoForm.reset();
        },
    });
};

const deleteCargo = (id) => {
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
            router.delete(route('cargos.destroy', id), {
                preserveScroll: true,
            });
        }
    });
};
</script>

<template>
    <Head title="Recursos Humanos" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-100">
                        Recursos Humanos
                    </h2>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Gestión de personal y cargos
                    </p>
                </div>
            </div>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Tabs Navigation -->
                <div class="mb-6 border-b border-gray-200 dark:border-gray-700">
                    <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                        <button
                            @click="activeTab = 'personal'"
                            :class="[
                                activeTab === 'personal'
                                    ? 'border-indigo-500 text-indigo-600 dark:border-indigo-400 dark:text-indigo-400'
                                    : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300',
                                'group inline-flex items-center border-b-2 px-1 py-4 text-sm font-medium transition-colors',
                            ]"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                :class="[
                                    activeTab === 'personal' ? 'text-indigo-500 dark:text-indigo-400' : 'text-gray-400 group-hover:text-gray-500',
                                    '-ml-0.5 mr-2 h-5 w-5',
                                ]"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="1.6"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 12a4 4 0 100-8 4 4 0 000 8zm-6 8a6 6 0 1112 0H6z" />
                            </svg>
                            Personal
                            <span
                                :class="[
                                    activeTab === 'personal'
                                        ? 'bg-indigo-100 text-indigo-600 dark:bg-indigo-900/50 dark:text-indigo-400'
                                        : 'bg-gray-100 text-gray-900 dark:bg-gray-800 dark:text-gray-300',
                                    'ml-3 hidden rounded-full px-2.5 py-0.5 text-xs font-medium md:inline-block',
                                ]"
                            >
                                {{ personal.total || 0 }}
                            </span>
                        </button>

                        <button
                            @click="activeTab = 'cargos'"
                            :class="[
                                activeTab === 'cargos'
                                    ? 'border-indigo-500 text-indigo-600 dark:border-indigo-400 dark:text-indigo-400'
                                    : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300',
                                'group inline-flex items-center border-b-2 px-1 py-4 text-sm font-medium transition-colors',
                            ]"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                :class="[
                                    activeTab === 'cargos' ? 'text-indigo-500 dark:text-indigo-400' : 'text-gray-400 group-hover:text-gray-500',
                                    '-ml-0.5 mr-2 h-5 w-5',
                                ]"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="1.6"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m0 0l-4-4m4 4l4-4M4 10h16" />
                            </svg>
                            Cargos
                            <span
                                :class="[
                                    activeTab === 'cargos'
                                        ? 'bg-indigo-100 text-indigo-600 dark:bg-indigo-900/50 dark:text-indigo-400'
                                        : 'bg-gray-100 text-gray-900 dark:bg-gray-800 dark:text-gray-300',
                                    'ml-3 hidden rounded-full px-2.5 py-0.5 text-xs font-medium md:inline-block',
                                ]"
                            >
                                {{ cargos.total || 0 }}
                            </span>
                        </button>
                    </nav>
                </div>

                <!-- Tab Content: Personal -->
                <div v-show="activeTab === 'personal'" class="space-y-8">
                    <!-- Formulario de Creación Personal -->
                    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Crear persona</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Los campos marcados como selección son obligatorios.</p>

                        <form class="mt-4 space-y-4" @submit.prevent="submitCreatePersonal">
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Nombre *</label>
                                    <input
                                        v-model="createPersonalForm.nombre"
                                        type="text"
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                        required
                                        maxlength="100"
                                    />
                                    <p v-if="createPersonalForm.errors.nombre" class="mt-1 text-sm text-red-600">
                                        {{ createPersonalForm.errors.nombre }}
                                    </p>
                                </div>

                                <div>
                                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Apellido *</label>
                                    <input
                                        v-model="createPersonalForm.apellido"
                                        type="text"
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                        required
                                        maxlength="100"
                                    />
                                    <p v-if="createPersonalForm.errors.apellido" class="mt-1 text-sm text-red-600">
                                        {{ createPersonalForm.errors.apellido }}
                                    </p>
                                </div>

                                <div>
                                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Cargo *</label>
                                    <select
                                        v-model="createPersonalForm.cargo_id"
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                        required
                                    >
                                        <option value="" disabled>Selecciona cargo</option>
                                        <option v-for="cargo in todosLosCargos" :key="cargo.id" :value="cargo.id">
                                            {{ cargo.nombre }}
                                        </option>
                                    </select>
                                    <p v-if="createPersonalForm.errors.cargo_id" class="mt-1 text-sm text-red-600">
                                        {{ createPersonalForm.errors.cargo_id }}
                                    </p>
                                </div>

                                <div>
                                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Área *</label>
                                    <select
                                        v-model="createPersonalForm.area_id"
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                        required
                                    >
                                        <option value="" disabled>Selecciona área</option>
                                        <option v-for="area in areas" :key="area.id" :value="area.id">
                                            {{ area.nombre }}
                                        </option>
                                    </select>
                                    <p v-if="createPersonalForm.errors.area_id" class="mt-1 text-sm text-red-600">
                                        {{ createPersonalForm.errors.area_id }}
                                    </p>
                                </div>

                                <div>
                                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Ubicación *</label>
                                    <select
                                        v-model="createPersonalForm.ubicacion_id"
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                        required
                                    >
                                        <option value="" disabled>Selecciona ubicación</option>
                                        <option v-for="ubicacion in ubicaciones" :key="ubicacion.id" :value="ubicacion.id">
                                            {{ ubicacion.nombre }}
                                        </option>
                                    </select>
                                    <p v-if="createPersonalForm.errors.ubicacion_id" class="mt-1 text-sm text-red-600">
                                        {{ createPersonalForm.errors.ubicacion_id }}
                                    </p>
                                </div>

                                <div>
                                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Teléfono</label>
                                    <input
                                        v-model="createPersonalForm.telefono"
                                        type="text"
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                        maxlength="100"
                                    />
                                </div>

                                <div>
                                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                                    <input
                                        v-model="createPersonalForm.email"
                                        type="email"
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                        maxlength="255"
                                    />
                                </div>

                                <div>
                                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Número de Cédula</label>
                                    <input
                                        v-model="createPersonalForm.numero_cedula"
                                        @input="formatCedula($event.target.value, 'create')"
                                        type="text"
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                        maxlength="16"
                                        placeholder="000-000000-0000X"
                                    />
                                </div>

                                <div>
                                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Sexo</label>
                                    <select
                                        v-model="createPersonalForm.sexo"
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                    >
                                        <option value="">Selecciona</option>
                                        <option value="MASCULINO">Masculino</option>
                                        <option value="FEMENINO">Femenino</option>
                                    </select>
                                </div>

                                <div class="sm:col-span-2">
                                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Dirección Domiciliar</label>
                                    <textarea
                                        v-model="createPersonalForm.direccion"
                                        rows="2"
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                        maxlength="500"
                                        placeholder="Dirección completa..."
                                    ></textarea>
                                </div>

                                <div class="sm:col-span-2">
                                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Foto del Personal</label>
                                    <div class="mt-1 flex items-center gap-4">
                                        <div v-if="fotoPreview" class="shrink-0">
                                            <img :src="fotoPreview" alt="Preview" class="h-16 w-16 rounded-full object-cover border-2 border-indigo-500" />
                                        </div>
                                        <input
                                            type="file"
                                            accept="image/*"
                                            @change="handleCreateFotoChange"
                                            class="block w-full text-sm text-gray-500 file:mr-4 file:rounded-md file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100 dark:text-gray-400 dark:file:bg-indigo-900/50 dark:file:text-indigo-400"
                                        />
                                    </div>
                                </div>

                                <div>
                                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Estado *</label>
                                    <select
                                        v-model="createPersonalForm.estado"
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                        required
                                    >
                                        <option value="ACTIVO">ACTIVO</option>
                                        <option value="INACTIVO">INACTIVO</option>
                                    </select>
                                </div>
                            </div>

                            <div class="flex justify-end">
                                <button
                                    type="submit"
                                    :disabled="createPersonalForm.processing"
                                    class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 dark:bg-indigo-500 dark:hover:bg-indigo-600"
                                >
                                    Crear
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Filtros -->
                    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Buscar</label>
                                <input
                                    v-model="search"
                                    type="text"
                                    placeholder="Nombre, apellido o email..."
                                    class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                />
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Filtrar por Área</label>
                                <select
                                    v-model="filtroArea"
                                    class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                >
                                    <option value="">Todas las áreas</option>
                                    <option v-for="area in areas" :key="area.id" :value="area.id">
                                        {{ area.nombre }}
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla Personal -->
                    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Listado de Personal</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Total: {{ personal.total || 0 }} registros</p>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-900">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Foto</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Nombre</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Cargo</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Área</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Estado</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                                    <tr v-for="persona in personal.data" :key="persona.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                        <td class="whitespace-nowrap px-6 py-4">
                                            <img v-if="persona.foto" :src="`/storage/${persona.foto}`" :alt="persona.nombre" class="h-10 w-10 rounded-full object-cover border border-gray-200 dark:border-gray-600" />
                                            <div v-else class="h-10 w-10 rounded-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center text-gray-500 dark:text-gray-400 text-sm font-bold">
                                                {{ persona.nombre?.charAt(0) }}{{ persona.apellido?.charAt(0) }}
                                            </div>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ persona.nombre }} {{ persona.apellido }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ persona.cargo }}</td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ persona.area }}</td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm">
                                            <span
                                                :class="[
                                                    persona.estado === 'ACTIVO'
                                                        ? 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-400'
                                                        : 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-400',
                                                    'inline-flex rounded-full px-2 text-xs font-semibold leading-5',
                                                ]"
                                            >
                                                {{ persona.estado }}
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                            <button
                                                @click="viewingPersonal = persona"
                                                class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-300"
                                                title="Ver detalles"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                            </button>
                                            <button
                                                v-if="canManage"
                                                @click="startEditPersonal(persona)"
                                                class="ml-3 text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
                                                title="Editar"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </button>
                                            <button
                                                v-if="canManage"
                                                @click="deletePersonal(persona.id)"
                                                class="ml-3 text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                                title="Eliminar"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <Pagination 
                            :links="personal.links" 
                            :from="personal.from" 
                            :to="personal.to" 
                            :total="personal.total" 
                        />
                    </div>
                </div>

                <!-- Tab Content: Cargos -->
                <div v-show="activeTab === 'cargos'" class="space-y-8">
                    <!-- Formulario de Creación Cargos -->
                    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Crear cargo</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Registra un nuevo cargo en el sistema.</p>

                        <form class="mt-4 space-y-4" @submit.prevent="submitCreateCargo">
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Nombre *</label>
                                    <input
                                        v-model="createCargoForm.nombre"
                                        type="text"
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                        required
                                        maxlength="100"
                                    />
                                    <p v-if="createCargoForm.errors.nombre" class="mt-1 text-sm text-red-600">
                                        {{ createCargoForm.errors.nombre }}
                                    </p>
                                </div>

                                <div>
                                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Estado *</label>
                                    <select
                                        v-model="createCargoForm.estado"
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                        required
                                    >
                                        <option value="ACTIVO">ACTIVO</option>
                                        <option value="INACTIVO">INACTIVO</option>
                                    </select>
                                </div>
                            </div>

                            <div class="flex justify-end">
                                <button
                                    type="submit"
                                    :disabled="createCargoForm.processing"
                                    class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 dark:bg-indigo-500 dark:hover:bg-indigo-600"
                                >
                                    Crear
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Tabla Cargos -->
                    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Listado de Cargos</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Total: {{ cargos.total || 0 }} cargos</p>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-900">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">ID</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Nombre</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Estado</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                                    <tr v-for="cargo in cargos.data" :key="cargo.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ cargo.id }}</td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">{{ cargo.nombre }}</td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm">
                                            <span
                                                :class="[
                                                    cargo.estado === 'ACTIVO'
                                                        ? 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-400'
                                                        : 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-400',
                                                    'inline-flex rounded-full px-2 text-xs font-semibold leading-5',
                                                ]"
                                            >
                                                {{ cargo.estado }}
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                            <button
                                                v-if="canManage"
                                                @click="startEditCargo(cargo)"
                                                class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
                                                title="Editar"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </button>
                                            <button
                                                v-if="canManage"
                                                @click="deleteCargo(cargo.id)"
                                                class="ml-3 text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                                title="Eliminar"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <Pagination 
                            :links="cargos.links" 
                            :from="cargos.from" 
                            :to="cargos.to" 
                            :total="cargos.total" 
                        />
                    </div>
                </div>

                <!-- Modal de Edición Personal -->
                <div v-if="editingPersonal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                    <div class="flex min-h-screen items-end justify-center px-4 pb-20 pt-4 text-center sm:block sm:p-0">
                        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="cancelEditPersonal"></div>
                        <span class="hidden sm:inline-block sm:h-screen sm:align-middle" aria-hidden="true">&#8203;</span>
                        <div class="inline-block transform overflow-hidden rounded-lg bg-white text-left align-bottom shadow-xl transition-all dark:bg-gray-800 sm:my-8 sm:w-full sm:max-w-2xl sm:align-middle">
                            <form @submit.prevent="submitEditPersonal">
                                <div class="bg-white px-4 pb-4 pt-5 dark:bg-gray-800 sm:p-6 sm:pb-4">
                                    <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100">Editar Personal</h3>
                                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                                        <div>
                                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Nombre *</label>
                                            <input v-model="editPersonalForm.nombre" type="text" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" required />
                                        </div>
                                        <div>
                                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Apellido *</label>
                                            <input v-model="editPersonalForm.apellido" type="text" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" required />
                                        </div>
                                        <div>
                                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Cargo *</label>
                                            <select v-model="editPersonalForm.cargo_id" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" required>
                                                <option value="">Selecciona cargo</option>
                                                <option v-for="cargo in todosLosCargos" :key="cargo.id" :value="cargo.id">{{ cargo.nombre }}</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Área *</label>
                                            <select v-model="editPersonalForm.area_id" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" required>
                                                <option value="">Selecciona área</option>
                                                <option v-for="area in areas" :key="area.id" :value="area.id">{{ area.nombre }}</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Ubicación *</label>
                                            <select v-model="editPersonalForm.ubicacion_id" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" required>
                                                <option value="">Selecciona ubicación</option>
                                                <option v-for="ubicacion in ubicaciones" :key="ubicacion.id" :value="ubicacion.id">{{ ubicacion.nombre }}</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Número de Cédula</label>
                                            <input v-model="editPersonalForm.numero_cedula" @input="formatCedula($event.target.value, 'edit')" type="text" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" maxlength="16" placeholder="000-000000-0000X" />
                                        </div>
                                        <div>
                                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Sexo</label>
                                            <select v-model="editPersonalForm.sexo" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                                <option value="">Selecciona</option>
                                                <option value="MASCULINO">Masculino</option>
                                                <option value="FEMENINO">Femenino</option>
                                            </select>
                                        </div>
                                        <div class="sm:col-span-2">
                                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Dirección Domiciliar</label>
                                            <textarea v-model="editPersonalForm.direccion" rows="2" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" maxlength="500" placeholder="Dirección completa..."></textarea>
                                        </div>
                                        <div class="sm:col-span-2">
                                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Foto del Personal</label>
                                            <div class="mt-1 flex items-center gap-4">
                                                <div v-if="currentFoto" class="shrink-0">
                                                    <img :src="currentFoto.startsWith('blob:') ? currentFoto : `/storage/${currentFoto}`" alt="Foto actual" class="h-16 w-16 rounded-full object-cover border-2 border-indigo-500" />
                                                </div>
                                                <input
                                                    type="file"
                                                    accept="image/*"
                                                    @change="handleEditFotoChange"
                                                    class="block w-full text-sm text-gray-500 file:mr-4 file:rounded-md file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100 dark:text-gray-400 dark:file:bg-indigo-900/50 dark:file:text-indigo-400"
                                                />
                                            </div>
                                        </div>
                                        <div>
                                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Estado *</label>
                                            <select v-model="editPersonalForm.estado" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" required>
                                                <option value="ACTIVO">ACTIVO</option>
                                                <option value="INACTIVO">INACTIVO</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-gray-50 px-4 py-3 dark:bg-gray-900 sm:flex sm:flex-row-reverse sm:px-6">
                                    <button type="submit" :disabled="editPersonalForm.processing" class="inline-flex w-full justify-center rounded-md bg-indigo-600 px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 dark:bg-indigo-500 dark:hover:bg-indigo-600 sm:ml-3 sm:w-auto sm:text-sm">
                                        Actualizar
                                    </button>
                                    <button type="button" @click="cancelEditPersonal" class="mt-3 inline-flex w-full justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-base font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 sm:mt-0 sm:w-auto sm:text-sm">
                                        Cancelar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Modal de Ver Personal -->
                <div v-if="viewingPersonal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                    <div class="flex min-h-screen items-end justify-center px-4 pb-20 pt-4 text-center sm:block sm:p-0">
                        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="viewingPersonal = null"></div>
                        <span class="hidden sm:inline-block sm:h-screen sm:align-middle" aria-hidden="true">&#8203;</span>
                        <div class="inline-block transform overflow-hidden rounded-lg bg-white text-left align-bottom shadow-xl transition-all dark:bg-gray-800 sm:my-8 sm:w-full sm:max-w-lg sm:align-middle">
                            <div class="bg-white px-4 pb-4 pt-5 dark:bg-gray-800 sm:p-6">
                                <div class="flex flex-col items-center text-center">
                                    <img v-if="viewingPersonal.foto" :src="`/storage/${viewingPersonal.foto}`" :alt="viewingPersonal.nombre" class="h-24 w-24 rounded-full object-cover border-4 border-indigo-500 shadow-lg" />
                                    <div v-else class="h-24 w-24 rounded-full bg-indigo-100 dark:bg-indigo-900/50 flex items-center justify-center text-indigo-600 dark:text-indigo-400 text-2xl font-bold shadow-lg">
                                        {{ viewingPersonal.nombre?.charAt(0) }}{{ viewingPersonal.apellido?.charAt(0) }}
                                    </div>
                                    <h3 class="mt-4 text-xl font-bold text-gray-900 dark:text-gray-100">{{ viewingPersonal.nombre }} {{ viewingPersonal.apellido }}</h3>
                                    <p class="text-indigo-600 dark:text-indigo-400 font-medium">{{ viewingPersonal.cargo }}</p>
                                </div>
                                <dl class="mt-6 grid gap-4 sm:grid-cols-2">
                                    <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-700">
                                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Área</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-200">{{ viewingPersonal.area || '—' }}</dd>
                                    </div>
                                    <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-700">
                                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Ubicación</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-200">{{ viewingPersonal.ubicacion || '—' }}</dd>
                                    </div>
                                    <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-700">
                                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Cédula</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-200">{{ viewingPersonal.numero_cedula || '—' }}</dd>
                                    </div>
                                    <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-700">
                                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Sexo</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-200">{{ viewingPersonal.sexo || '—' }}</dd>
                                    </div>
                                    <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-700">
                                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Teléfono</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-200">{{ viewingPersonal.telefono || '—' }}</dd>
                                    </div>
                                    <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-700">
                                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Email</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-200">{{ viewingPersonal.email || '—' }}</dd>
                                    </div>
                                    <div class="sm:col-span-2 rounded-lg bg-gray-50 p-3 dark:bg-gray-700">
                                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Dirección</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-200">{{ viewingPersonal.direccion || '—' }}</dd>
                                    </div>
                                </dl>
                            </div>
                            <div class="bg-gray-50 px-4 py-3 dark:bg-gray-900 sm:flex sm:flex-row-reverse sm:px-6">
                                <button type="button" @click="viewingPersonal = null" class="inline-flex w-full justify-center rounded-md bg-indigo-600 px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:bg-indigo-500 dark:hover:bg-indigo-600 sm:w-auto sm:text-sm">
                                    Cerrar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal de Edición Cargo -->
                <div v-if="editingCargo" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                    <div class="flex min-h-screen items-end justify-center px-4 pb-20 pt-4 text-center sm:block sm:p-0">
                        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="cancelEditCargo"></div>
                        <span class="hidden sm:inline-block sm:h-screen sm:align-middle" aria-hidden="true">&#8203;</span>
                        <div class="inline-block transform overflow-hidden rounded-lg bg-white text-left align-bottom shadow-xl transition-all dark:bg-gray-800 sm:my-8 sm:w-full sm:max-w-lg sm:align-middle">
                            <form @submit.prevent="submitEditCargo">
                                <div class="bg-white px-4 pb-4 pt-5 dark:bg-gray-800 sm:p-6 sm:pb-4">
                                    <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100">Editar Cargo</h3>
                                    <div class="mt-4 space-y-4">
                                        <div>
                                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Nombre *</label>
                                            <input v-model="editCargoForm.nombre" type="text" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" required />
                                        </div>
                                        <div>
                                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Estado *</label>
                                            <select v-model="editCargoForm.estado" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" required>
                                                <option value="ACTIVO">ACTIVO</option>
                                                <option value="INACTIVO">INACTIVO</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-gray-50 px-4 py-3 dark:bg-gray-900 sm:flex sm:flex-row-reverse sm:px-6">
                                    <button type="submit" :disabled="editCargoForm.processing" class="inline-flex w-full justify-center rounded-md bg-indigo-600 px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 dark:bg-indigo-500 dark:hover:bg-indigo-600 sm:ml-3 sm:w-auto sm:text-sm">
                                        Actualizar
                                    </button>
                                    <button type="button" @click="cancelEditCargo" class="mt-3 inline-flex w-full justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-base font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 sm:mt-0 sm:w-auto sm:text-sm">
                                        Cancelar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
