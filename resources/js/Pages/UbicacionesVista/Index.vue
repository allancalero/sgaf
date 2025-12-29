<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    areas: { type: Array, default: () => [] },
    ubicaciones: { type: Array, default: () => [] },
});

const activeTab = ref('areas');

// Forms Áreas
const createAreaForm = useForm({ nombre: '', ubicacion_id: null, estado: 'ACTIVO' });
const editAreaForm = useForm({ id: null, nombre: '', ubicacion_id: null, estado: 'ACTIVO' });
const editingArea = ref(false);

// Forms Ubicaciones
const createUbicacionForm = useForm({ nombre: '', direccion: '', estado: 'ACTIVO' });
const editUbicacionForm = useForm({ id: null, nombre: '', direccion: '', estado: 'ACTIVO' });
const editingUbicacion = ref(false);

// Funciones Áreas
const submitCreateArea = () => createAreaForm.post(route('areas.store'), { 
    preserveScroll: true, 
    onSuccess: () => {
        createAreaForm.reset();
        router.reload({ only: ['areas', 'ubicaciones'] });
    }
});
const startEditArea = (area) => { editingArea.value = true; editAreaForm.id = area.id; editAreaForm.nombre = area.nombre; editAreaForm.ubicacion_id = area.ubicacion_id || null; editAreaForm.estado = area.estado || 'ACTIVO'; };
const cancelEditArea = () => { editingArea.value = false; editAreaForm.reset(); };
const submitEditArea = () => editAreaForm.put(route('areas.update', editAreaForm.id), { 
    preserveScroll: true, 
    onSuccess: () => { 
        editingArea.value = false; 
        editAreaForm.reset();
        router.reload({ only: ['areas', 'ubicaciones'] });
    } 
});
const deleteArea = (id) => { 
    if (confirm('¿Estás seguro de eliminar esta área?')) 
        router.delete(route('areas.destroy', id), { 
            preserveScroll: true,
            onSuccess: () => router.reload({ only: ['areas', 'ubicaciones'] })
        }); 
};

// Funciones Ubicaciones
const submitCreateUbicacion = () => createUbicacionForm.post(route('ubicaciones.store'), { 
    preserveScroll: true, 
    onSuccess: () => {
        createUbicacionForm.reset();
        router.reload({ only: ['areas', 'ubicaciones'] });
    }
});
const startEditUbicacion = (ubicacion) => { editingUbicacion.value = true; editUbicacionForm.id = ubicacion.id; editUbicacionForm.nombre = ubicacion.nombre; editUbicacionForm.direccion = ubicacion.direccion || ''; editUbicacionForm.estado = ubicacion.estado || 'ACTIVO'; };
const cancelEditUbicacion = () => { editingUbicacion.value = false; editUbicacionForm.reset(); };
const submitEditUbicacion = () => editUbicacionForm.put(route('ubicaciones.update', editUbicacionForm.id), { 
    preserveScroll: true, 
    onSuccess: () => { 
        editingUbicacion.value = false; 
        editUbicacionForm.reset();
        router.reload({ only: ['areas', 'ubicaciones'] });
    } 
});
const deleteUbicacion = (id) => { 
    if (confirm('¿Estás seguro de eliminar esta ubicación?')) 
        router.delete(route('ubicaciones.destroy', id), { 
            preserveScroll: true,
            onSuccess: () => router.reload({ only: ['areas', 'ubicaciones'] })
        }); 
};
</script>

<template>
    <Head title="Ubicación" />
    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-100">Ubicación</h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Gestión de áreas y ubicaciones</p>
            </div>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Tabs Navigation -->
                <div class="mb-6 border-b border-gray-200 dark:border-gray-700">
                    <nav class="-mb-px flex space-x-8">
                        <button @click="activeTab = 'areas'" :class="[activeTab === 'areas' ? 'border-indigo-500 text-indigo-600 dark:border-indigo-400 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400', 'group inline-flex items-center border-b-2 px-1 py-4 text-sm font-medium']">
                            <svg xmlns="http://www.w3.org/2000/svg" :class="[activeTab === 'areas' ? 'text-indigo-500 dark:text-indigo-400' : 'text-gray-400', '-ml-0.5 mr-2 h-5 w-5']" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5h18M5 9h14M7 13h10M9 17h6" /></svg>
                            Áreas
                            <span :class="[activeTab === 'areas' ? 'bg-indigo-100 text-indigo-600 dark:bg-indigo-900/50 dark:text-indigo-400' : 'bg-gray-100 text-gray-900 dark:bg-gray-800 dark:text-gray-300', 'ml-3 hidden rounded-full px-2.5 py-0.5 text-xs font-medium md:inline-block']">{{ areas.length }}</span>
                        </button>
                        <button @click="activeTab = 'ubicaciones'" :class="[activeTab === 'ubicaciones' ? 'border-indigo-500 text-indigo-600 dark:border-indigo-400 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400', 'group inline-flex items-center border-b-2 px-1 py-4 text-sm font-medium']">
                            <svg xmlns="http://www.w3.org/2000/svg" :class="[activeTab === 'ubicaciones' ? 'text-indigo-500 dark:text-indigo-400' : 'text-gray-400', '-ml-0.5 mr-2 h-5 w-5']" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 2c3.314 0 6 2.477 6 5.533C18 11.364 12 22 12 22S6 11.364 6 7.533C6 4.477 8.686 2 12 2z" /></svg>
                            Ubicaciones
                            <span :class="[activeTab === 'ubicaciones' ? 'bg-indigo-100 text-indigo-600 dark:bg-indigo-900/50 dark:text-indigo-400' : 'bg-gray-100 text-gray-900 dark:bg-gray-800 dark:text-gray-300', 'ml-3 hidden rounded-full px-2.5 py-0.5 text-xs font-medium md:inline-block']">{{ ubicaciones.length }}</span>
                        </button>
                    </nav>
                </div>

                <!-- Tab Content: Áreas -->
                <div v-show="activeTab === 'areas'" class="space-y-8">
                    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Crear área</h3>
                        <form class="mt-4 space-y-4" @submit.prevent="submitCreateArea">
                            <div class="grid gap-4 sm:grid-cols-3">
                                <div><label class="text-sm font-medium text-gray-700 dark:text-gray-300">Nombre *</label><input v-model="createAreaForm.nombre" type="text" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" required maxlength="100" /></div>
                                <div><label class="text-sm font-medium text-gray-700 dark:text-gray-300">Ubicación</label><select v-model="createAreaForm.ubicacion_id" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"><option :value="null">Sin ubicación</option><option v-for="ubicacion in ubicaciones" :key="ubicacion.id" :value="ubicacion.id">{{ ubicacion.nombre }}</option></select></div>
                                <div><label class="text-sm font-medium text-gray-700 dark:text-gray-300">Estado *</label><select v-model="createAreaForm.estado" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" required><option value="ACTIVO">ACTIVO</option><option value="INACTIVO">INACTIVO</option></select></div>
                            </div>
                            <div class="flex justify-end"><button type="submit" :disabled="createAreaForm.processing" class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 disabled:opacity-50 dark:bg-indigo-500">Crear</button></div>
                        </form>
                    </div>
                    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-700"><h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Listado de Áreas</h3><p class="text-sm text-gray-500 dark:text-gray-400">Total: {{ areas.length }}</p></div>
                        <div class="overflow-x-auto"><table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700"><thead class="bg-gray-50 dark:bg-gray-900"><tr><th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">ID</th><th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Nombre</th><th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Ubicación</th><th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Estado</th><th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Acciones</th></tr></thead><tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800"><tr v-for="area in areas" :key="area.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50"><td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ area.id }}</td><td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">{{ area.nombre }}</td><td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ ubicaciones.find(u => u.id === area.ubicacion_id)?.nombre || 'Sin ubicación' }}</td><td class="px-6 py-4 text-sm"><span :class="[area.estado === 'ACTIVO' ? 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-400' : 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-400', 'inline-flex rounded-full px-2 text-xs font-semibold']">{{ area.estado }}</span></td><td class="px-6 py-4 text-right"><button @click="startEditArea(area)" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg></button><button @click="deleteArea(area.id)" class="ml-3 text-red-600 hover:text-red-900 dark:text-red-400"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button></td></tr></tbody></table></div>
                    </div>
                </div>

                <!-- Tab Content: Ubicaciones -->
                <div v-show="activeTab === 'ubicaciones'" class="space-y-8">
                    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Crear ubicación</h3>
                        <form class="mt-4 space-y-4" @submit.prevent="submitCreateUbicacion">
                            <div class="grid gap-4 sm:grid-cols-3">
                                <div><label class="text-sm font-medium text-gray-700 dark:text-gray-300">Nombre *</label><input v-model="createUbicacionForm.nombre" type="text" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" required maxlength="100" /></div>
                                <div><label class="text-sm font-medium text-gray-700 dark:text-gray-300">Dirección</label><input v-model="createUbicacionForm.direccion" type="text" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" maxlength="500" placeholder="Ej: Calle Principal #123" /></div>
                                <div><label class="text-sm font-medium text-gray-700 dark:text-gray-300">Estado *</label><select v-model="createUbicacionForm.estado" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" required><option value="ACTIVO">ACTIVO</option><option value="INACTIVO">INACTIVO</option></select></div>
                            </div>
                            <div class="flex justify-end"><button type="submit" :disabled="createUbicacionForm.processing" class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 disabled:opacity-50 dark:bg-indigo-500">Crear</button></div>
                        </form>
                    </div>
                    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-700"><h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Listado de Ubicaciones</h3><p class="text-sm text-gray-500 dark:text-gray-400">Total: {{ ubicaciones.length }}</p></div>
                        <div class="overflow-x-auto"><table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700"><thead class="bg-gray-50 dark:bg-gray-900"><tr><th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">ID</th><th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Nombre</th><th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Dirección</th><th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Estado</th><th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Acciones</th></tr></thead><tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800"><tr v-for="ubicacion in ubicaciones" :key="ubicacion.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50"><td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ ubicacion.id }}</td><td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">{{ ubicacion.nombre }}</td><td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ ubicacion.direccion || '-' }}</td><td class="px-6 py-4 text-sm"><span :class="[ubicacion.estado === 'ACTIVO' ? 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-400' : 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-400', 'inline-flex rounded-full px-2 text-xs font-semibold']">{{ ubicacion.estado }}</span></td><td class="px-6 py-4 text-right"><button @click="startEditUbicacion(ubicacion)" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg></button><button @click="deleteUbicacion(ubicacion.id)" class="ml-3 text-red-600 hover:text-red-900 dark:text-red-400"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button></td></tr></tbody></table></div>
                    </div>
                </div>

                <!-- Modal Editar Área -->
                <div v-if="editingArea" class="fixed inset-0 z-50 overflow-y-auto"><div class="flex min-h-screen items-end justify-center px-4 pb-20 pt-4 text-center sm:block sm:p-0"><div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="cancelEditArea"></div><span class="hidden sm:inline-block sm:h-screen sm:align-middle">&#8203;</span><div class="inline-block transform overflow-hidden rounded-lg bg-white text-left align-bottom shadow-xl transition-all dark:bg-gray-800 sm:my-8 sm:w-full sm:max-w-lg sm:align-middle"><form @submit.prevent="submitEditArea"><div class="bg-white px-4 pb-4 pt-5 dark:bg-gray-800 sm:p-6"><h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Editar Área</h3><div class="mt-4 space-y-4"><div><label class="text-sm font-medium text-gray-700 dark:text-gray-300">Nombre *</label><input v-model="editAreaForm.nombre" type="text" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" required /></div><div><label class="text-sm font-medium text-gray-700 dark:text-gray-300">Ubicación</label><select v-model="editAreaForm.ubicacion_id" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"><option :value="null">Sin ubicación</option><option v-for="ubicacion in ubicaciones" :key="ubicacion.id" :value="ubicacion.id">{{ ubicacion.nombre }}</option></select></div><div><label class="text-sm font-medium text-gray-700 dark:text-gray-300">Estado *</label><select v-model="editAreaForm.estado" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" required><option value="ACTIVO">ACTIVO</option><option value="INACTIVO">INACTIVO</option></select></div></div></div><div class="bg-gray-50 px-4 py-3 dark:bg-gray-900 sm:flex sm:flex-row-reverse sm:px-6"><button type="submit" :disabled="editAreaForm.processing" class="inline-flex w-full justify-center rounded-md bg-indigo-600 px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-indigo-700 disabled:opacity-50 sm:ml-3 sm:w-auto sm:text-sm">Actualizar</button><button type="button" @click="cancelEditArea" class="mt-3 inline-flex w-full justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-base font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 sm:mt-0 sm:w-auto sm:text-sm">Cancelar</button></div></form></div></div></div>

                <!-- Modal Editar Ubicación -->
                <div v-if="editingUbicacion" class="fixed inset-0 z-50 overflow-y-auto"><div class="flex min-h-screen items-end justify-center px-4 pb-20 pt-4 text-center sm:block sm:p-0"><div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="cancelEditUbicacion"></div><span class="hidden sm:inline-block sm:h-screen sm:align-middle">&#8203;</span><div class="inline-block transform overflow-hidden rounded-lg bg-white text-left align-bottom shadow-xl transition-all dark:bg-gray-800 sm:my-8 sm:w-full sm:max-w-lg sm:align-middle"><form @submit.prevent="submitEditUbicacion"><div class="bg-white px-4 pb-4 pt-5 dark:bg-gray-800 sm:p-6"><h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Editar Ubicación</h3><div class="mt-4 space-y-4"><div><label class="text-sm font-medium text-gray-700 dark:text-gray-300">Nombre *</label><input v-model="editUbicacionForm.nombre" type="text" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" required /></div><div><label class="text-sm font-medium text-gray-700 dark:text-gray-300">Dirección</label><input v-model="editUbicacionForm.direccion" type="text" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" maxlength="500" placeholder="Ej: Calle Principal #123" /></div><div><label class="text-sm font-medium text-gray-700 dark:text-gray-300">Área</label><select v-model="editUbicacionForm.area_id" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"><option :value="null">Sin área</option><option v-for="area in areas" :key="area.id" :value="area.id">{{ area.nombre }}</option></select></div><div><label class="text-sm font-medium text-gray-700 dark:text-gray-300">Estado *</label><select v-model="editUbicacionForm.estado" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" required><option value="ACTIVO">ACTIVO</option><option value="INACTIVO">INACTIVO</option></select></div></div></div><div class="bg-gray-50 px-4 py-3 dark:bg-gray-900 sm:flex sm:flex-row-reverse sm:px-6"><button type="submit" :disabled="editUbicacionForm.processing" class="inline-flex w-full justify-center rounded-md bg-indigo-600 px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-indigo-700 disabled:opacity-50 sm:ml-3 sm:w-auto sm:text-sm">Actualizar</button><button type="button" @click="cancelEditUbicacion" class="mt-3 inline-flex w-full justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-base font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 sm:mt-0 sm:w-auto sm:text-sm">Cancelar</button></div></form></div></div></div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
