<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const props = defineProps({
    proveedores: { type: Array, default: () => [] },
    cheques: { type: Array, default: () => [] },
    fuentes: { type: Array, default: () => [] },
    clasificaciones: { type: Array, default: () => [] },
    tipos: { type: Array, default: () => [] },
    responsables: { type: Array, default: () => [] },
    areas: { type: Array, default: () => [] },
});

const page = usePage();
const can = (permission) => page.props.auth.user?.permissions?.includes(permission);
const canManage = computed(() => can('catalogos.manage'));

const activeTab = ref('proveedores');

// Forms - Proveedores
const createProveedorForm = useForm({ nombre: '', ruc: '', direccion: '', telefono: '', email: '' });
const editProveedorForm = useForm({ id: null, nombre: '', ruc: '', direccion: '', telefono: '', email: '' });
const editingProveedor = ref(false);

// Forms - Cheques
const createChequeForm = useForm({ numero_cheque: '', banco: '', monto_total: '', estado: 'PENDIENTE' });
const editChequeForm = useForm({ id: null, numero_cheque: '', banco: '', monto_total: '', estado: 'PENDIENTE' });
const editingCheque = ref(false);

// Forms - Fuentes
const createFuenteForm = useForm({ nombre: '', estado: 'ACTIVO' });
const editFuenteForm = useForm({ id: null, nombre: '', estado: 'ACTIVO' });
const editingFuente = ref(false);

// Forms - Clasificaciones (sin estado)
const createClasificacionForm = useForm({ nombre: '' });
const editClasificacionForm = useForm({ id: null, nombre: '' });
const editingClasificacion = ref(false);

// Forms - Tipos (sin estado)
const createTipoForm = useForm({ nombre: '' });
const editTipoForm = useForm({ id: null, nombre: '' });
const editingTipo = ref(false);

// Forms - Responsables (solo nombre)
const createResponsableForm = useForm({ nombre: '' });
const editResponsableForm = useForm({ id: null, nombre: '' });
const editingResponsable = ref(false);

// Funciones Proveedores
const submitCreateProveedor = () => createProveedorForm.post(route('proveedores.store'), { preserveScroll: true, onSuccess: () => createProveedorForm.reset() });
const startEditProveedor = (p) => { editingProveedor.value = true; Object.assign(editProveedorForm, p); };
const cancelEditProveedor = () => { editingProveedor.value = false; editProveedorForm.reset(); };
const submitEditProveedor = () => editProveedorForm.put(route('proveedores.update', editProveedorForm.id), { preserveScroll: true, onSuccess: () => { editingProveedor.value = false; editProveedorForm.reset(); } });
const deleteProveedor = (id) => { if (confirm('¿Eliminar proveedor?')) router.delete(route('proveedores.destroy', id), { preserveScroll: true }); };

// Funciones Cheques
const submitCreateCheque = () => createChequeForm.post(route('cheques.store'), { preserveScroll: true, onSuccess: () => createChequeForm.reset() });
const startEditCheque = (c) => { editingCheque.value = true; editChequeForm.id = c.id; editChequeForm.numero_cheque = c.numero_cheque; editChequeForm.banco = c.banco; editChequeForm.monto_total = c.monto_total; editChequeForm.estado = c.estado; };
const cancelEditCheque = () => { editingCheque.value = false; editChequeForm.reset(); };
const submitEditCheque = () => editChequeForm.put(route('cheques.update', editChequeForm.id), { preserveScroll: true, onSuccess: () => { editingCheque.value = false; editChequeForm.reset(); } });
const deleteCheque = (id) => { if (confirm('¿Eliminar cheque?')) router.delete(route('cheques.destroy', id), { preserveScroll: true }); };

// Funciones Fuentes
const submitCreateFuente = () => createFuenteForm.post(route('fuentes.store'), { preserveScroll: true, onSuccess: () => createFuenteForm.reset() });
const startEditFuente = (f) => { editingFuente.value = true; editFuenteForm.id = f.id; editFuenteForm.nombre = f.nombre; editFuenteForm.estado = f.estado || 'ACTIVO'; };
const cancelEditFuente = () => { editingFuente.value = false; editFuenteForm.reset(); };
const submitEditFuente = () => editFuenteForm.put(route('fuentes.update', editFuenteForm.id), { preserveScroll: true, onSuccess: () => { editingFuente.value = false; editFuenteForm.reset(); } });
const deleteFuente = (id) => { if (confirm('¿Eliminar fuente?')) router.delete(route('fuentes.destroy', id), { preserveScroll: true }); };

// Funciones Clasificaciones
const submitCreateClasificacion = () => createClasificacionForm.post(route('clasificaciones.store'), { preserveScroll: true, onSuccess: () => createClasificacionForm.reset() });
const startEditClasificacion = (c) => { editingClasificacion.value = true; editClasificacionForm.id = c.id; editClasificacionForm.nombre = c.nombre; };
const cancelEditClasificacion = () => { editingClasificacion.value = false; editClasificacionForm.reset(); };
const submitEditClasificacion = () => editClasificacionForm.put(route('clasificaciones.update', editClasificacionForm.id), { preserveScroll: true, onSuccess: () => { editingClasificacion.value = false; editClasificacionForm.reset(); } });
const deleteClasificacion = (id) => { if (confirm('¿Eliminar clasificación?')) router.delete(route('clasificaciones.destroy', id), { preserveScroll: true }); };

// Funciones Tipos
const submitCreateTipo = () => createTipoForm.post(route('tipos.store'), { preserveScroll: true, onSuccess: () => createTipoForm.reset() });
const startEditTipo = (t) => { editingTipo.value = true; editTipoForm.id = t.id; editTipoForm.nombre = t.nombre; };
const cancelEditTipo = () => { editingTipo.value = false; editTipoForm.reset(); };
const submitEditTipo = () => editTipoForm.put(route('tipos.update', editTipoForm.id), { preserveScroll: true, onSuccess: () => { editingTipo.value = false; editTipoForm.reset(); } });
const deleteTipo = (id) => { if (confirm('¿Eliminar tipo?')) router.delete(route('tipos.destroy', id), { preserveScroll: true }); };

// Funciones Responsables
const submitCreateResponsable = () => createResponsableForm.post(route('responsables.store'), { preserveScroll: true, onSuccess: () => createResponsableForm.reset() });
const startEditResponsable = (r) => { editingResponsable.value = true; editResponsableForm.id = r.id; editResponsableForm.nombre = r.nombre; };
const cancelEditResponsable = () => { editingResponsable.value = false; editResponsableForm.reset(); };
const submitEditResponsable = () => editResponsableForm.put(route('responsables.update', editResponsableForm.id), { preserveScroll: true, onSuccess: () => { editingResponsable.value = false; editResponsableForm.reset(); } });
const deleteResponsable = (id) => { if (confirm('¿Eliminar responsable?')) router.delete(route('responsables.destroy', id), { preserveScroll: true }); };
</script>

<template>
    <Head title="Activos Fijo" />
    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-100">Activos Fijo</h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Gestión de activos, proveedores y asignaciones</p>
            </div>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Tabs Navigation - Scrollable on mobile -->
                <div class="mb-6 border-b border-gray-200 dark:border-gray-700">
                    <nav class="-mb-px flex space-x-4 overflow-x-auto pb-px">
                        <button @click="activeTab = 'proveedores'" :class="[activeTab === 'proveedores' ? 'border-indigo-500 text-indigo-600 dark:border-indigo-400 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400', 'group inline-flex items-center border-b-2 px-1 py-4 text-sm font-medium whitespace-nowrap']">
                            Proveedores <span :class="[activeTab === 'proveedores' ? 'bg-indigo-100 text-indigo-600 dark:bg-indigo-900/50 dark:text-indigo-400' : 'bg-gray-100 text-gray-900 dark:bg-gray-800 dark:text-gray-300', 'ml-2 rounded-full px-2 py-0.5 text-xs font-medium']">{{ proveedores.length }}</span>
                        </button>
                        <button @click="activeTab = 'cheques'" :class="[activeTab === 'cheques' ? 'border-indigo-500 text-indigo-600 dark:border-indigo-400 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400', 'group inline-flex items-center border-b-2 px-1 py-4 text-sm font-medium whitespace-nowrap']">
                            Cheques <span :class="[activeTab === 'cheques' ? 'bg-indigo-100 text-indigo-600 dark:bg-indigo-900/50 dark:text-indigo-400' : 'bg-gray-100 text-gray-900 dark:bg-gray-800 dark:text-gray-300', 'ml-2 rounded-full px-2 py-0.5 text-xs font-medium']">{{ cheques.length }}</span>
                        </button>
                        <button @click="activeTab = 'fuentes'" :class="[activeTab === 'fuentes' ? 'border-indigo-500 text-indigo-600 dark:border-indigo-400 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400', 'group inline-flex items-center border-b-2 px-1 py-4 text-sm font-medium whitespace-nowrap']">
                            Fuentes <span :class="[activeTab === 'fuentes' ? 'bg-indigo-100 text-indigo-600 dark:bg-indigo-900/50 dark:text-indigo-400' : 'bg-gray-100 text-gray-900 dark:bg-gray-800 dark:text-gray-300', 'ml-2 rounded-full px-2 py-0.5 text-xs font-medium']">{{ fuentes.length }}</span>
                        </button>
                        <button @click="activeTab = 'clasificaciones'" :class="[activeTab === 'clasificaciones' ? 'border-indigo-500 text-indigo-600 dark:border-indigo-400 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400', 'group inline-flex items-center border-b-2 px-1 py-4 text-sm font-medium whitespace-nowrap']">
                            Clasificaciones <span :class="[activeTab === 'clasificaciones' ? 'bg-indigo-100 text-indigo-600 dark:bg-indigo-900/50 dark:text-indigo-400' : 'bg-gray-100 text-gray-900 dark:bg-gray-800 dark:text-gray-300', 'ml-2 rounded-full px-2 py-0.5 text-xs font-medium']">{{ clasificaciones.length }}</span>
                        </button>
                        <button @click="activeTab = 'tipos'" :class="[activeTab === 'tipos' ? 'border-indigo-500 text-indigo-600 dark:border-indigo-400 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400', 'group inline-flex items-center border-b-2 px-1 py-4 text-sm font-medium whitespace-nowrap']">
                            Tipos <span :class="[activeTab === 'tipos' ? 'bg-indigo-100 text-indigo-600 dark:bg-indigo-900/50 dark:text-indigo-400' : 'bg-gray-100 text-gray-900 dark:bg-gray-800 dark:text-gray-300', 'ml-2 rounded-full px-2 py-0.5 text-xs font-medium']">{{ tipos.length }}</span>
                        </button>
                        <button @click="activeTab = 'responsables'" :class="[activeTab === 'responsables' ? 'border-indigo-500 text-indigo-600 dark:border-indigo-400 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400', 'group inline-flex items-center border-b-2 px-1 py-4 text-sm font-medium whitespace-nowrap']">
                            Asignación <span :class="[activeTab === 'responsables' ? 'bg-indigo-100 text-indigo-600 dark:bg-indigo-900/50 dark:text-indigo-400' : 'bg-gray-100 text-gray-900 dark:bg-gray-800 dark:text-gray-300', 'ml-2 rounded-full px-2 py-0.5 text-xs font-medium']">{{ responsables.length }}</span>
                        </button>
                    </nav>
                </div>

                <!-- Tab Content: Proveedores -->
                <div v-show="activeTab === 'proveedores'" class="space-y-6">
                    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Crear proveedor</h3>
                        <form class="mt-4" @submit.prevent="submitCreateProveedor">
                            <div class="grid gap-4 sm:grid-cols-3">
                                <div><label class="text-sm font-medium text-gray-700 dark:text-gray-300">Nombre *</label><input v-model="createProveedorForm.nombre" type="text" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" required /></div>
                                <div><label class="text-sm font-medium text-gray-700 dark:text-gray-300">RUC</label><input v-model="createProveedorForm.ruc" type="text" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" /></div>
                                <div><label class="text-sm font-medium text-gray-700 dark:text-gray-300">Teléfono</label><input v-model="createProveedorForm.telefono" type="text" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" /></div>
                            </div>
                            <div v-if="canManage" class="mt-4 flex justify-end"><button type="submit" :disabled="createProveedorForm.processing" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 disabled:opacity-50">Crear</button></div>
                        </form>
                    </div>
                    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="border-b px-6 py-4 dark:border-gray-700"><h3 class="text-lg font-semibold">Proveedores</h3></div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-900">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">ID</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Nombre</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">RUC</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                                    <tr v-for="p in proveedores" :key="p.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                        <td class="px-6 py-4 text-sm">{{ p.id }}</td>
                                        <td class="px-6 py-4 text-sm font-medium">{{ p.nombre }}</td>
                                        <td class="px-6 py-4 text-sm">{{ p.ruc }}</td>
                                        <td class="px-6 py-4 text-right whitespace-nowrap">
                                            <template v-if="canManage">
                                                <button @click="startEditProveedor(p)" class="text-indigo-600 hover:text-indigo-900">
                                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                </button>
                                                <button @click="deleteProveedor(p.id)" class="ml-3 text-red-600 hover:text-red-900">
                                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </template>
                                            <span v-else class="text-gray-400 font-mono">---</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Otros tabs (Cheques, Fuentes, Clasificaciones, Tipos, Responsables) seguirían el mismo patrón -->
                <!-- Por brevedad, incluyo solo la estructura básica de los demás tabs -->
                
                
                <!-- Tab Content: Cheques -->
                <div v-show="activeTab === 'cheques'" class="space-y-6">
                    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Crear cheque</h3>
                        <form class="mt-4" @submit.prevent="submitCreateCheque">
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div><label class="text-sm font-medium text-gray-700 dark:text-gray-300">Número *</label><input v-model="createChequeForm.numero_cheque" type="text" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" required /></div>
                                <div><label class="text-sm font-medium text-gray-700 dark:text-gray-300">Banco *</label><input v-model="createChequeForm.banco" type="text" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" required /></div>
                                <div><label class="text-sm font-medium text-gray-700 dark:text-gray-300">Monto *</label><input v-model="createChequeForm.monto_total" type="number" step="0.01" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" required /></div>
                                <div><label class="text-sm font-medium text-gray-700 dark:text-gray-300">Estado *</label><select v-model="createChequeForm.estado" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" required><option value="PENDIENTE">PENDIENTE</option><option value="COBRADO">COBRADO</option><option value="ANULADO">ANULADO</option></select></div>
                            </div>
                            <div v-if="canManage" class="mt-4 flex justify-end"><button type="submit" :disabled="createChequeForm.processing" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 disabled:opacity-50">Crear</button></div>
                        </form>
                    </div>
                    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="border-b px-6 py-4 dark:border-gray-700"><h3 class="text-lg font-semibold">Cheques</h3></div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-900">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Número</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Banco</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Monto</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Estado</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                                    <tr v-for="c in cheques" :key="c.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                        <td class="px-6 py-4 text-sm font-medium">{{ c.numero_cheque }}</td>
                                        <td class="px-6 py-4 text-sm">{{ c.banco }}</td>
                                        <td class="px-6 py-4 text-sm">{{ c.monto_total }}</td>
                                        <td class="px-6 py-4 text-sm">
                                            <span :class="[c.estado === 'COBRADO' ? 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-400' : c.estado === 'PENDIENTE' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-400' : 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-400', 'inline-flex rounded-full px-2 text-xs font-semibold']">{{ c.estado }}</span>
                                        </td>
                                        <td class="px-6 py-4 text-right whitespace-nowrap">
                                            <template v-if="canManage">
                                                <button @click="startEditCheque(c)" class="text-indigo-600 hover:text-indigo-900">
                                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                </button>
                                                <button @click="deleteCheque(c.id)" class="ml-3 text-red-600 hover:text-red-900">
                                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </template>
                                            <span v-else class="text-gray-400 font-mono">---</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Tab Content: Fuentes -->
                <div v-show="activeTab === 'fuentes'" class="space-y-6">
                    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Crear fuente de financiamiento</h3>
                        <form class="mt-4" @submit.prevent="submitCreateFuente">
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div><label class="text-sm font-medium text-gray-700 dark:text-gray-300">Nombre *</label><input v-model="createFuenteForm.nombre" type="text" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" required /></div>
                                <div><label class="text-sm font-medium text-gray-700 dark:text-gray-300">Estado *</label><select v-model="createFuenteForm.estado" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" required><option value="ACTIVO">ACTIVO</option><option value="INACTIVO">INACTIVO</option></select></div>
                            </div>
                            <div v-if="canManage" class="mt-4 flex justify-end"><button type="submit" :disabled="createFuenteForm.processing" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 disabled:opacity-50">Crear</button></div>
                        </form>
                    </div>
                    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="border-b px-6 py-4 dark:border-gray-700"><h3 class="text-lg font-semibold">Fuentes de Financiamiento</h3></div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-900">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">ID</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Nombre</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Estado</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                                    <tr v-for="f in fuentes" :key="f.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                        <td class="px-6 py-4 text-sm">{{ f.id }}</td>
                                        <td class="px-6 py-4 text-sm font-medium">{{ f.nombre }}</td>
                                        <td class="px-6 py-4 text-sm">
                                            <span :class="[f.estado === 'ACTIVO' ? 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-400' : 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-400', 'inline-flex rounded-full px-2 text-xs font-semibold']">{{ f.estado }}</span>
                                        </td>
                                        <td class="px-6 py-4 text-right whitespace-nowrap">
                                            <template v-if="canManage">
                                                <button @click="startEditFuente(f)" class="text-indigo-600 hover:text-indigo-900">
                                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                </button>
                                                <button @click="deleteFuente(f.id)" class="ml-3 text-red-600 hover:text-red-900">
                                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </template>
                                            <span v-else class="text-gray-400 font-mono">---</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Tab Content: Clasificaciones -->
                <div v-show="activeTab === 'clasificaciones'" class="space-y-6">
                    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Crear clasificación</h3>
                        <form class="mt-4" @submit.prevent="submitCreateClasificacion">
                            <div class="grid gap-4 sm:grid-cols-1">
                                <div><label class="text-sm font-medium text-gray-700 dark:text-gray-300">Nombre *</label><input v-model="createClasificacionForm.nombre" type="text" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" required /></div>
                            </div>
                            <div v-if="canManage" class="mt-4 flex justify-end"><button type="submit" :disabled="createClasificacionForm.processing" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 disabled:opacity-50">Crear</button></div>
                        </form>
                    </div>
                    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="border-b px-6 py-4 dark:border-gray-700"><h3 class="text-lg font-semibold">Clasificaciones</h3></div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-900">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">ID</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Nombre</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                                    <tr v-for="c in clasificaciones" :key="c.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                        <td class="px-6 py-4 text-sm">{{ c.id }}</td>
                                        <td class="px-6 py-4 text-sm font-medium">{{ c.nombre }}</td>
                                        <td class="px-6 py-4 text-right whitespace-nowrap">
                                            <template v-if="canManage">
                                                <button @click="startEditClasificacion(c)" class="text-indigo-600 hover:text-indigo-900">
                                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                </button>
                                                <button @click="deleteClasificacion(c.id)" class="ml-3 text-red-600 hover:text-red-900">
                                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </template>
                                            <span v-else class="text-gray-400 font-mono">---</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Tab Content: Tipos -->
                <div v-show="activeTab === 'tipos'" class="space-y-6">
                    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Crear tipo de activo</h3>
                        <form class="mt-4" @submit.prevent="submitCreateTipo">
                            <div class="grid gap-4 sm:grid-cols-1">
                                <div><label class="text-sm font-medium text-gray-700 dark:text-gray-300">Nombre *</label><input v-model="createTipoForm.nombre" type="text" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" required /></div>
                            </div>
                            <div v-if="canManage" class="mt-4 flex justify-end"><button type="submit" :disabled="createTipoForm.processing" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 disabled:opacity-50">Crear</button></div>
                        </form>
                    </div>
                    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="border-b px-6 py-4 dark:border-gray-700"><h3 class="text-lg font-semibold">Tipos de Activos</h3></div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-900">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">ID</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Nombre</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                                    <tr v-for="t in tipos" :key="t.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                        <td class="px-6 py-4 text-sm">{{ t.id }}</td>
                                        <td class="px-6 py-4 text-sm font-medium">{{ t.nombre }}</td>
                                        <td class="px-6 py-4 text-right whitespace-nowrap">
                                            <template v-if="canManage">
                                                <button @click="startEditTipo(t)" class="text-indigo-600 hover:text-indigo-900">
                                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                </button>
                                                <button @click="deleteTipo(t.id)" class="ml-3 text-red-600 hover:text-red-900">
                                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </template>
                                            <span v-else class="text-gray-400 font-mono">---</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Tab Content: Responsables/Asignación -->
                <div v-show="activeTab === 'responsables'" class="space-y-6">
                    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Crear responsable</h3>
                        <form class="mt-4" @submit.prevent="submitCreateResponsable">
                            <div class="grid gap-4 sm:grid-cols-1">
                                <div><label class="text-sm font-medium text-gray-700 dark:text-gray-300">Nombre *</label><input v-model="createResponsableForm.nombre" type="text" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" required /></div>
                            </div>
                            <div v-if="canManage" class="mt-4 flex justify-end"><button type="submit" :disabled="createResponsableForm.processing" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 disabled:opacity-50">Crear</button></div>
                        </form>
                    </div>
                    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="border-b px-6 py-4 dark:border-gray-700"><h3 class="text-lg font-semibold">Responsables (Asignación)</h3></div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-900">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">ID</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Nombre</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                                    <tr v-for="r in responsables" :key="r.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                        <td class="px-6 py-4 text-sm">{{ r.id }}</td>
                                        <td class="px-6 py-4 text-sm font-medium">{{ r.nombre }}</td>
                                        <td class="px-6 py-4 text-right whitespace-nowrap">
                                            <template v-if="canManage">
                                                <button @click="startEditResponsable(r)" class="text-indigo-600 hover:text-indigo-900">
                                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                </button>
                                                <button @click="deleteResponsable(r.id)" class="ml-3 text-red-600 hover:text-red-900">
                                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </template>
                                            <span v-else class="text-gray-400 font-mono">---</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Editar Proveedor -->
        <div v-if="editingProveedor" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="flex min-h-screen items-end justify-center px-4 pb-20 pt-4 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity dark:bg-gray-900 dark:bg-opacity-75" @click="cancelEditProveedor"></div>
                <span class="hidden sm:inline-block sm:h-screen sm:align-middle">&#8203;</span>
                <div class="inline-block transform overflow-hidden rounded-lg bg-white text-left align-bottom shadow-xl transition-all dark:bg-gray-800 sm:my-8 sm:w-full sm:max-w-lg sm:align-middle">
                    <form @submit.prevent="submitEditProveedor">
                        <div class="bg-white px-4 pb-4 pt-5 dark:bg-gray-800 sm:p-6">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Editar Proveedor</h3>
                            <div class="mt-4 space-y-4">
                                <div><label class="text-sm font-medium text-gray-700 dark:text-gray-300">Nombre *</label><input v-model="editProveedorForm.nombre" type="text" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" required /></div>
                                <div><label class="text-sm font-medium text-gray-700 dark:text-gray-300">RUC</label><input v-model="editProveedorForm.ruc" type="text" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" /></div>
                                <div><label class="text-sm font-medium text-gray-700 dark:text-gray-300">Teléfono</label><input v-model="editProveedorForm.telefono" type="text" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" /></div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 dark:bg-gray-700 sm:flex sm:flex-row-reverse sm:px-6">
                            <button type="submit" :disabled="editProveedorForm.processing" class="inline-flex w-full justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 sm:ml-3 sm:w-auto">Guardar</button>
                            <button type="button" @click="cancelEditProveedor" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 dark:bg-gray-600 dark:text-gray-100 dark:ring-gray-500 sm:mt-0 sm:w-auto">Cancelar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Editar Cheque -->
        <div v-if="editingCheque" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="flex min-h-screen items-end justify-center px-4 pb-20 pt-4 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity dark:bg-gray-900 dark:bg-opacity-75" @click="cancelEditCheque"></div>
                <span class="hidden sm:inline-block sm:h-screen sm:align-middle">&#8203;</span>
                <div class="inline-block transform overflow-hidden rounded-lg bg-white text-left align-bottom shadow-xl transition-all dark:bg-gray-800 sm:my-8 sm:w-full sm:max-w-lg sm:align-middle">
                    <form @submit.prevent="submitEditCheque">
                        <div class="bg-white px-4 pb-4 pt-5 dark:bg-gray-800 sm:p-6">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Editar Cheque</h3>
                            <div class="mt-4 space-y-4">
                                <div><label class="text-sm font-medium text-gray-700 dark:text-gray-300">Número *</label><input v-model="editChequeForm.numero_cheque" type="text" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" required /></div>
                                <div><label class="text-sm font-medium text-gray-700 dark:text-gray-300">Banco *</label><input v-model="editChequeForm.banco" type="text" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" required /></div>
                                <div><label class="text-sm font-medium text-gray-700 dark:text-gray-300">Monto *</label><input v-model="editChequeForm.monto_total" type="number" step="0.01" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" required /></div>
                                <div><label class="text-sm font-medium text-gray-700 dark:text-gray-300">Estado *</label><select v-model="editChequeForm.estado" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" required><option value="PENDIENTE">PENDIENTE</option><option value="COBRADO">COBRADO</option><option value="ANULADO">ANULADO</option></select></div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 dark:bg-gray-700 sm:flex sm:flex-row-reverse sm:px-6">
                            <button type="submit" :disabled="editChequeForm.processing" class="inline-flex w-full justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 sm:ml-3 sm:w-auto">Guardar</button>
                            <button type="button" @click="cancelEditCheque" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 dark:bg-gray-600 dark:text-gray-100 dark:ring-gray-500 sm:mt-0 sm:w-auto">Cancelar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Editar Fuente -->
        <div v-if="editingFuente" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="flex min-h-screen items-end justify-center px-4 pb-20 pt-4 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity dark:bg-gray-900 dark:bg-opacity-75" @click="cancelEditFuente"></div>
                <span class="hidden sm:inline-block sm:h-screen sm:align-middle">&#8203;</span>
                <div class="inline-block transform overflow-hidden rounded-lg bg-white text-left align-bottom shadow-xl transition-all dark:bg-gray-800 sm:my-8 sm:w-full sm:max-w-lg sm:align-middle">
                    <form @submit.prevent="submitEditFuente">
                        <div class="bg-white px-4 pb-4 pt-5 dark:bg-gray-800 sm:p-6">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Editar Fuente de Financiamiento</h3>
                            <div class="mt-4 space-y-4">
                                <div><label class="text-sm font-medium text-gray-700 dark:text-gray-300">Nombre *</label><input v-model="editFuenteForm.nombre" type="text" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" required /></div>
                                <div><label class="text-sm font-medium text-gray-700 dark:text-gray-300">Estado *</label><select v-model="editFuenteForm.estado" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" required><option value="ACTIVO">ACTIVO</option><option value="INACTIVO">INACTIVO</option></select></div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 dark:bg-gray-700 sm:flex sm:flex-row-reverse sm:px-6">
                            <button type="submit" :disabled="editFuenteForm.processing" class="inline-flex w-full justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 sm:ml-3 sm:w-auto">Guardar</button>
                            <button type="button" @click="cancelEditFuente" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 dark:bg-gray-600 dark:text-gray-100 dark:ring-gray-500 sm:mt-0 sm:w-auto">Cancelar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Editar Clasificación -->
        <div v-if="editingClasificacion" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="flex min-h-screen items-end justify-center px-4 pb-20 pt-4 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity dark:bg-gray-900 dark:bg-opacity-75" @click="cancelEditClasificacion"></div>
                <span class="hidden sm:inline-block sm:h-screen sm:align-middle">&#8203;</span>
                <div class="inline-block transform overflow-hidden rounded-lg bg-white text-left align-bottom shadow-xl transition-all dark:bg-gray-800 sm:my-8 sm:w-full sm:max-w-lg sm:align-middle">
                    <form @submit.prevent="submitEditClasificacion">
                        <div class="bg-white px-4 pb-4 pt-5 dark:bg-gray-800 sm:p-6">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Editar Clasificación</h3>
                            <div class="mt-4 space-y-4">
                                <div><label class="text-sm font-medium text-gray-700 dark:text-gray-300">Nombre *</label><input v-model="editClasificacionForm.nombre" type="text" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" required /></div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 dark:bg-gray-700 sm:flex sm:flex-row-reverse sm:px-6">
                            <button type="submit" :disabled="editClasificacionForm.processing" class="inline-flex w-full justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 sm:ml-3 sm:w-auto">Guardar</button>
                            <button type="button" @click="cancelEditClasificacion" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 dark:bg-gray-600 dark:text-gray-100 dark:ring-gray-500 sm:mt-0 sm:w-auto">Cancelar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Editar Tipo -->
        <div v-if="editingTipo" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="flex min-h-screen items-end justify-center px-4 pb-20 pt-4 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity dark:bg-gray-900 dark:bg-opacity-75" @click="cancelEditTipo"></div>
                <span class="hidden sm:inline-block sm:h-screen sm:align-middle">&#8203;</span>
                <div class="inline-block transform overflow-hidden rounded-lg bg-white text-left align-bottom shadow-xl transition-all dark:bg-gray-800 sm:my-8 sm:w-full sm:max-w-lg sm:align-middle">
                    <form @submit.prevent="submitEditTipo">
                        <div class="bg-white px-4 pb-4 pt-5 dark:bg-gray-800 sm:p-6">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Editar Tipo de Activo</h3>
                            <div class="mt-4 space-y-4">
                                <div><label class="text-sm font-medium text-gray-700 dark:text-gray-300">Nombre *</label><input v-model="editTipoForm.nombre" type="text" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" required /></div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 dark:bg-gray-700 sm:flex sm:flex-row-reverse sm:px-6">
                            <button type="submit" :disabled="editTipoForm.processing" class="inline-flex w-full justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 sm:ml-3 sm:w-auto">Guardar</button>
                            <button type="button" @click="cancelEditTipo" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 dark:bg-gray-600 dark:text-gray-100 dark:ring-gray-500 sm:mt-0 sm:w-auto">Cancelar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Editar Responsable -->
        <div v-if="editingResponsable" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="flex min-h-screen items-end justify-center px-4 pb-20 pt-4 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity dark:bg-gray-900 dark:bg-opacity-75" @click="cancelEditResponsable"></div>
                <span class="hidden sm:inline-block sm:h-screen sm:align-middle">&#8203;</span>
                <div class="inline-block transform overflow-hidden rounded-lg bg-white text-left align-bottom shadow-xl transition-all dark:bg-gray-800 sm:my-8 sm:w-full sm:max-w-lg sm:align-middle">
                    <form @submit.prevent="submitEditResponsable">
                        <div class="bg-white px-4 pb-4 pt-5 dark:bg-gray-800 sm:p-6">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Editar Responsable</h3>
                            <div class="mt-4 space-y-4">
                                <div><label class="text-sm font-medium text-gray-700 dark:text-gray-300">Nombre *</label><input v-model="editResponsableForm.nombre" type="text" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" required /></div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 dark:bg-gray-700 sm:flex sm:flex-row-reverse sm:px-6">
                            <button type="submit" :disabled="editResponsableForm.processing" class="inline-flex w-full justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 sm:ml-3 sm:w-auto">Guardar</button>
                            <button type="button" @click="cancelEditResponsable" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 dark:bg-gray-600 dark:text-gray-100 dark:ring-gray-500 sm:mt-0 sm:w-auto">Cancelar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
