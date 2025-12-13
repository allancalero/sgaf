<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { reactive, watch } from 'vue';

const props = defineProps({
    users: { type: Array, default: () => [] },
    roles: { type: Array, default: () => [] },
    permissions: { type: Object, default: () => ({}) },
});

const page = usePage();
const success = page.props.flash?.success;
const error = page.props.flash?.error;

const superForm = useForm({ email: '' });
const forms = reactive({});

const formFor = (user) => {
    if (!forms[user.id]) {
        forms[user.id] = useForm({
            roles: [...(user.roles || [])],
            permissions: [...(user.permissions || [])],
        });
    }
    return forms[user.id];
};

const syncForms = () => {
    props.users.forEach((user) => formFor(user));
};

syncForms();

watch(
    () => props.users,
    () => {
        syncForms();
    },
    { deep: true }
);
</script>

<template>
    <Head title="Seguridad" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-100">Seguridad y roles</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Definición de roles, permisos y políticas de acceso.</p>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-5xl space-y-6 sm:px-6 lg:px-8">
                <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Asignar roles completos</h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Dar acceso total (admin + editor + consulta) por correo.</p>

                    <form class="mt-4 space-y-4" @submit.prevent="superForm.post(route('sistema.seguridad.asignar-super'))">
                        <div>
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Correo electrónico</label>
                            <input
                                v-model="superForm.email"
                                type="email"
                                class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                required
                            />
                            <p v-if="superForm.errors.email" class="mt-1 text-sm text-red-600">{{ superForm.errors.email }}</p>
                        </div>

                        <div class="flex items-center gap-3">
                            <button
                                type="submit"
                                :disabled="superForm.processing"
                                class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50"
                            >
                                Asignar todo
                            </button>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Auditoría: `seguridad.super`.</p>
                        </div>

                        <p v-if="success" class="text-sm font-medium text-emerald-700">{{ success }}</p>
                        <p v-if="error" class="text-sm font-medium text-red-700">{{ error }}</p>
                    </form>
                </div>

                <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div v-if="success || error" class="mb-4 flex items-start gap-3 rounded-lg border px-4 py-3" :class="success ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : 'border-red-200 bg-red-50 text-red-800'">
                        <svg v-if="success" class="h-5 w-5 text-emerald-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <svg v-else class="h-5 w-5 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        <div class="text-sm font-medium">{{ success || error }}</div>
                    </div>

                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Accesos por usuario</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Marca roles o permisos por módulo/vista y guarda.</p>
                        </div>
                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ props.users.length }} usuarios</span>
                    </div>

                    <div class="mt-6 space-y-6">
                        <div
                            v-for="user in props.users"
                            :key="user.id"
                            class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800"
                        >
                            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-4 py-3 dark:border-gray-700">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ user.nombre }} {{ user.apellido }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ user.email }}</p>
                                </div>
                                <div class="flex flex-wrap gap-2 text-xs text-gray-600">
                                    <span class="inline-flex items-center rounded-full bg-indigo-50 px-2.5 py-1 font-medium text-indigo-700">Roles: {{ (formFor(user).data.roles || []).join(', ') || '—' }}</span>
                                    <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-1 font-medium text-emerald-700">Permisos: {{ (formFor(user).data.permissions || []).length }}</span>
                                </div>
                            </div>

                            <form class="space-y-4 p-4" @submit.prevent="formFor(user).put(route('sistema.seguridad.actualizar', user.id), { preserveScroll: true })">
                                <div class="grid gap-4 lg:grid-cols-2">
                                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-900">
                                        <p class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Roles</p>
                                        <div class="mt-2 space-y-2 text-sm text-gray-700 dark:text-gray-300">
                                            <label v-for="role in props.roles" :key="role" class="flex items-center gap-2">
                                                <input
                                                    v-model="formFor(user).data.roles"
                                                    :value="role"
                                                    type="checkbox"
                                                    class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                                />
                                                <span class="capitalize">{{ role }}</span>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-900">
                                        <p class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Permisos</p>
                                        <div class="mt-2 space-y-3 text-sm text-gray-700 dark:text-gray-300">
                                            <div v-for="(perms, grupo) in props.permissions" :key="grupo">
                                                <p class="text-xs font-semibold text-gray-500 capitalize">{{ grupo }}</p>
                                                <div class="mt-1 grid gap-2 sm:grid-cols-2">
                                                    <label v-for="perm in perms" :key="perm" class="flex items-center gap-2">
                                                        <input
                                                            v-model="formFor(user).data.permissions"
                                                            :value="perm"
                                                            type="checkbox"
                                                            class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                                        />
                                                        <span>{{ perm }}</span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between gap-3">
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Auditoría: `seguridad.update`.</div>
                                    <div class="flex gap-2">
                                        <button
                                            type="button"
                                            class="rounded-md border border-gray-200 px-3 py-2 text-xs font-medium text-gray-700 hover:border-indigo-300 hover:text-indigo-700"
                                                @click="() => { formFor(user).data.roles = [...(user.roles || [])]; formFor(user).data.permissions = [...(user.permissions || [])]; }"
                                        >
                                            Reset
                                        </button>
                                        <button
                                            type="submit"
                                                :disabled="formFor(user).processing"
                                            class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50"
                                        >
                                            Guardar
                                        </button>
                                    </div>
                                </div>

                                <p v-if="formFor(user).errors.roles" class="text-xs text-red-600">{{ formFor(user).errors.roles }}</p>
                                <p v-if="formFor(user).errors.permissions" class="text-xs text-red-600">{{ formFor(user).errors.permissions }}</p>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
