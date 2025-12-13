<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    setting: { type: Object, default: () => null },
});

const page = usePage();
const can = (permission) => page.props.auth.user?.permissions?.includes(permission);

const form = useForm({
    nombre_alcaldia: props.setting?.nombre_alcaldia || '',
    moneda: props.setting?.moneda || 'USD',
    ano_fiscal: props.setting?.ano_fiscal || '',
    logo_file: null,
});

const existingLogoUrl = computed(() => props.setting?.logo_url || '');

const submit = () => {
    form.transform((data) => ({ ...data, _method: 'put' }))
        .post(route('sistema.parametros.update'), {
            preserveScroll: true,
            forceFormData: true,
        });
};

const onFileChange = (event) => {
    const [file] = event.target.files;
    form.logo_file = file || null;
};
</script>

<template>
    <Head title="Parámetros del sistema" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="flex items-center gap-2 text-xl font-semibold leading-tight text-gray-800">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h10m-6 6h12" />
                        </svg>
                        <span>Parámetros del sistema</span>
                    </div>
                    <p class="text-sm text-gray-500">Configura logo, nombre de alcaldía, moneda y año fiscal.</p>
                </div>
                <div class="flex flex-wrap gap-2 text-sm">
                    <a :href="route('sistema.index')" class="inline-flex items-center gap-2 rounded-md border border-gray-200 px-3 py-2 text-gray-700 shadow-sm transition hover:border-indigo-300 hover:text-indigo-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 5h18M9 3v2m6-2v2M5 9h14v10H5z" />
                        </svg>
                        Sistema
                    </a>
                    <a :href="route('sistema.respaldo')" class="inline-flex items-center gap-2 rounded-md border border-gray-200 px-3 py-2 text-gray-700 shadow-sm transition hover:border-indigo-300 hover:text-indigo-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m0 0l-4-4m4 4l4-4M4 10h16" />
                        </svg>
                        Respaldos
                    </a>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-4xl space-y-6 sm:px-6 lg:px-8">
                <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h3 class="text-lg font-semibold text-gray-900">Datos generales</h3>
                    <p class="text-sm text-gray-500">Información que se usará en reportes y vistas.</p>

                    <form class="mt-4 space-y-4" @submit.prevent="submit">
                        <div>
                            <label class="text-sm font-medium text-gray-700">Nombre de la alcaldía</label>
                            <input
                                v-model="form.nombre_alcaldia"
                                type="text"
                                class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                maxlength="255"
                                placeholder="Ej. Alcaldía Municipal de ..."
                            />
                            <p v-if="form.errors.nombre_alcaldia" class="mt-1 text-sm text-red-600">{{ form.errors.nombre_alcaldia }}</p>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="text-sm font-medium text-gray-700">Moneda</label>
                                <input
                                    v-model="form.moneda"
                                    type="text"
                                    class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    maxlength="20"
                                    placeholder="USD, NIO, etc."
                                    required
                                />
                                <p v-if="form.errors.moneda" class="mt-1 text-sm text-red-600">{{ form.errors.moneda }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700">Año fiscal</label>
                                <input
                                    v-model="form.ano_fiscal"
                                    type="number"
                                    min="2000"
                                    max="2100"
                                    class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="2025"
                                />
                                <p v-if="form.errors.ano_fiscal" class="mt-1 text-sm text-red-600">{{ form.errors.ano_fiscal }}</p>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <div>
                                <label class="text-sm font-medium text-gray-700">Logo (PNG)</label>
                                <input
                                    type="file"
                                    accept="image/png"
                                    class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    @change="onFileChange"
                                />
                                <p class="mt-1 text-xs text-gray-500">Selecciona un archivo PNG. Tamaño máx 2 MB.</p>
                                <p v-if="form.errors.logo_file" class="mt-1 text-sm text-red-600">{{ form.errors.logo_file }}</p>
                            </div>

                            <div v-if="form.logo_file || existingLogoUrl" class="mt-1 flex items-center gap-3 rounded-md border border-gray-100 bg-gray-50 p-3 text-sm text-gray-700">
                                <img :src="form.logo_file ? URL.createObjectURL(form.logo_file) : existingLogoUrl" alt="Logo" class="h-10 w-auto" />
                                <span class="text-xs text-gray-500">Vista previa del logo</span>
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="inline-flex items-center gap-2 rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                                Guardar parámetros
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
