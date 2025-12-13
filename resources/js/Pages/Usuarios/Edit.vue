<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
const props = defineProps({ user: Object, roles: Array });
const form = useForm({
    nombre: props.user.nombre,
    apellido: props.user.apellido,
    email: props.user.email,
    password: '',
    password_confirmation: '',
    roles: props.user.roles.map(r => r.name),
});

function submit() {
    form.put(route('usuarios.update', props.user.id), {
        onSuccess: () => {
            form.reset('password', 'password_confirmation');
        },
    });
}
</script>

<template>
    <Head title="Editar usuario" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800">Editar usuario</h2>
                    <p class="text-sm text-gray-500">Modifica los datos, roles o contraseña del usuario.</p>
                </div>
            </div>
        </template>
        <div class="py-8">
            <div class="mx-auto max-w-2xl space-y-8 sm:px-6 lg:px-8">
                <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
                    <form @submit.prevent="submit" class="space-y-5 p-8">
                        <div v-if="form.recentlySuccessful" class="rounded bg-green-100 text-green-800 px-4 py-2 text-sm mb-2">Usuario actualizado correctamente.</div>
                        <div v-if="form.hasErrors" class="rounded bg-red-100 text-red-800 px-4 py-2 text-sm mb-2">Por favor revisa los campos marcados.</div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nombre</label>
                                <input v-model="form.nombre" type="text" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                                <div v-if="form.errors.nombre" class="text-red-600 text-xs mt-1">{{ form.errors.nombre }}</div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Apellido</label>
                                <input v-model="form.apellido" type="text" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                                <div v-if="form.errors.apellido" class="text-red-600 text-xs mt-1">{{ form.errors.apellido }}</div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Correo electrónico</label>
                            <input v-model="form.email" type="email" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                            <div v-if="form.errors.email" class="text-red-600 text-xs mt-1">{{ form.errors.email }}</div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nueva contraseña (opcional)</label>
                                <input v-model="form.password" type="password" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                                <div v-if="form.errors.password" class="text-red-600 text-xs mt-1">{{ form.errors.password }}</div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Confirmar contraseña</label>
                                <input v-model="form.password_confirmation" type="password" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Roles</label>
                            <select v-model="form.roles" multiple class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option v-for="role in roles" :key="role.id" :value="role.name">{{ role.name }}</option>
                            </select>
                        </div>
                        <div class="flex gap-2 mt-6">
                            <button type="submit" :disabled="form.processing" class="inline-flex items-center gap-2 rounded-md bg-indigo-600 px-5 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50">
                                <svg v-if="form.processing" class="animate-spin h-4 w-4 mr-2" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg>
                                Guardar
                            </button>
                            <Link :href="route('usuarios.index')" class="px-5 py-2 rounded-md border border-gray-300 text-gray-700 bg-white hover:bg-gray-50">Cancelar</Link>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
