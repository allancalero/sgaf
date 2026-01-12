<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted } from 'vue';
const props = defineProps({ users: Object, search: String });
const busqueda = ref(props.search || '');
const total = computed(() => props.users.total || props.users.data.length);
const usuariosFiltrados = computed(() => {
    const term = busqueda.value.trim().toLowerCase();
    if (!term) return props.users.data;
    return props.users.data.filter((u) =>
        [u.nombre, u.apellido, u.email, ...(u.roles?.map(r => r.name) || [])]
            .filter(Boolean)
    );
});

const handleKeydown = (event) => {
    if (event.ctrlKey && event.altKey && event.key === 'n') {
        event.preventDefault();
        router.visit(route('usuarios.create'));
    }
};

onMounted(() => {
    window.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeydown);
});
</script>

<template>
    <Head title="Usuarios" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-100">Usuarios</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Gestión, control y consulta de usuarios del sistema.</p>
                </div>
            </div>
        </template>
        <div class="py-8">
            <div class="mx-auto max-w-3xl space-y-8 sm:px-6 lg:px-8">
                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="border-b border-gray-100 px-4 py-3 flex items-center justify-between dark:border-gray-700">
                        <div>
                            <h3 class="text-base font-semibold text-gray-800 dark:text-gray-100">Usuarios</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ usuariosFiltrados.length }} usuarios registrados</p>
                        </div>
                        <Link :href="route('usuarios.create')" class="inline-flex items-center gap-2 rounded-md bg-indigo-600 px-3 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-indigo-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                            </svg>
                            Nuevo usuario
                        </Link>
                    </div>
                    <div class="max-h-96 overflow-auto">
                        <table class="min-w-full text-left text-sm text-gray-700 dark:text-gray-300">
                            <thead class="bg-gray-50 text-xs uppercase text-gray-500 dark:bg-gray-900 dark:text-gray-400">
                                <tr>
                                    <th class="px-3 py-2">Nombre</th>
                                    <th class="px-3 py-2">Correo</th>
                                    <th class="px-3 py-2">Roles</th>
                                    <th class="px-3 py-2">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="user in usuariosFiltrados" :key="user.id" class="odd:bg-white even:bg-gray-50 dark:odd:bg-gray-800 dark:even:bg-gray-700/50">
                                    <td class="px-3 py-2 align-top">{{ user.nombre }} {{ user.apellido }}</td>
                                    <td class="px-3 py-2 align-top">{{ user.email }}</td>
                                    <td class="px-3 py-2 align-top">
                                        <span v-for="role in user.roles" :key="role.id" class="inline-flex items-center rounded-full bg-indigo-50 px-2 py-1 text-xs font-medium text-indigo-700 mr-1">
                                            {{ role.name }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-2 align-top">
                                        <Link :href="route('usuarios.edit', user.id)" class="text-indigo-600 hover:underline mr-2">Editar</Link>
                                        <Link as="button" method="delete" :href="route('usuarios.destroy', user.id)" class="text-red-600 hover:underline">Eliminar</Link>
                                    </td>
                                </tr>
                                <tr v-if="!usuariosFiltrados.length">
                                    <td colspan="4" class="px-3 py-4 text-center text-gray-500 dark:text-gray-400">Sin datos</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
