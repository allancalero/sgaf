<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    cheques: {
        type: Array,
        default: () => [],
    },
    areas: {
        type: Array,
        default: () => [],
    },
});

const createForm = useForm({
    numero_cheque: '',
    banco: '',
    cuenta_bancaria: '',
    monto_total: '',
    fecha_emision: '',
    fecha_vencimiento: '',
    beneficiario: '',
    beneficiario_ruc: '',
    descripcion: '',
    estado: 'EMITIDO',
    area_solicitante_id: '',
});

const editForm = useForm({
    id: null,
    numero_cheque: '',
    banco: '',
    cuenta_bancaria: '',
    monto_total: '',
    saldo_disponible: '',
    fecha_emision: '',
    fecha_vencimiento: '',
    beneficiario: '',
    beneficiario_ruc: '',
    descripcion: '',
    estado: 'EMITIDO',
    area_solicitante_id: '',
});

const editing = ref(false);

const formatCurrency = (value) => {
    if (!value) return '$0.00';
    return new Intl.NumberFormat('es-PA', {
        style: 'currency',
        currency: 'USD',
    }).format(value);
};

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('es-PA');
};

const getEstadoBadgeClass = (estado) => {
    const classes = {
        'EMITIDO': 'bg-blue-100 text-blue-800',
        'COBRADO': 'bg-green-100 text-green-800',
        'ANULADO': 'bg-red-100 text-red-800',
    };
    return classes[estado] || 'bg-gray-100 text-gray-800';
};

const startEdit = (cheque) => {
    editing.value = true;
    editForm.id = cheque.id;
    editForm.numero_cheque = cheque.numero_cheque;
    editForm.banco = cheque.banco;
    editForm.cuenta_bancaria = cheque.cuenta_bancaria;
    editForm.monto_total = cheque.monto_total;
    editForm.saldo_disponible = cheque.saldo_disponible;
    editForm.fecha_emision = cheque.fecha_emision;
    editForm.fecha_vencimiento = cheque.fecha_vencimiento || '';
    editForm.beneficiario = cheque.beneficiario;
    editForm.beneficiario_ruc = cheque.beneficiario_ruc || '';
    editForm.descripcion = cheque.descripcion || '';
    editForm.estado = cheque.estado;
    editForm.area_solicitante_id = cheque.area_solicitante_id;
};

const cancelEdit = () => {
    editing.value = false;
    editForm.reset();
};

const submitCreate = () => {
    createForm.post(route('cheques.store'), {
        onSuccess: () => createForm.reset(),
    });
};

const submitEdit = () => {
    editForm.put(route('cheques.update', editForm.id), {
        onSuccess: () => {
            cancelEdit();
        },
    });
};

const destroyCheque = (id) => {
    if (confirm('¿Estás seguro de eliminar este cheque?')) {
        router.delete(route('cheques.destroy', id));
    }
};
</script>

<template>
    <Head title="Cheques" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800">Cheques</h2>
                    <p class="text-sm text-gray-500">Gestiona los cheques emitidos para adquisición de activos.</p>
                </div>
            </div>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-7xl space-y-8 sm:px-6 lg:px-8">
                <!-- Formulario de Creación -->
                <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                        <h3 class="text-lg font-semibold text-gray-800">Crear cheque</h3>
                        <p class="text-sm text-gray-500">Registra un nuevo cheque emitido.</p>

                        <form class="mt-4 space-y-4" @submit.prevent="submitCreate">
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="text-sm font-medium text-gray-700">Número de Cheque *</label>
                                    <input
                                        v-model="createForm.numero_cheque"
                                        type="text"
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        required
                                        maxlength="50"
                                    />
                                    <p v-if="createForm.errors.numero_cheque" class="mt-1 text-sm text-red-600">
                                        {{ createForm.errors.numero_cheque }}
                                    </p>
                                </div>

                                <div>
                                    <label class="text-sm font-medium text-gray-700">Banco *</label>
                                    <input
                                        v-model="createForm.banco"
                                        type="text"
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        required
                                        maxlength="100"
                                    />
                                    <p v-if="createForm.errors.banco" class="mt-1 text-sm text-red-600">
                                        {{ createForm.errors.banco }}
                                    </p>
                                </div>

                                <div>
                                    <label class="text-sm font-medium text-gray-700">Cuenta Bancaria *</label>
                                    <input
                                        v-model="createForm.cuenta_bancaria"
                                        type="text"
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        required
                                        maxlength="50"
                                    />
                                    <p v-if="createForm.errors.cuenta_bancaria" class="mt-1 text-sm text-red-600">
                                        {{ createForm.errors.cuenta_bancaria }}
                                    </p>
                                </div>

                                <div>
                                    <label class="text-sm font-medium text-gray-700">Monto Total *</label>
                                    <input
                                        v-model="createForm.monto_total"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        required
                                    />
                                    <p v-if="createForm.errors.monto_total" class="mt-1 text-sm text-red-600">
                                        {{ createForm.errors.monto_total }}
                                    </p>
                                </div>

                                <div>
                                    <label class="text-sm font-medium text-gray-700">Fecha Emisión *</label>
                                    <input
                                        v-model="createForm.fecha_emision"
                                        type="date"
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        required
                                    />
                                    <p v-if="createForm.errors.fecha_emision" class="mt-1 text-sm text-red-600">
                                        {{ createForm.errors.fecha_emision }}
                                    </p>
                                </div>

                                <div>
                                    <label class="text-sm font-medium text-gray-700">Fecha Vencimiento</label>
                                    <input
                                        v-model="createForm.fecha_vencimiento"
                                        type="date"
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                    <p v-if="createForm.errors.fecha_vencimiento" class="mt-1 text-sm text-red-600">
                                        {{ createForm.errors.fecha_vencimiento }}
                                    </p>
                                </div>

                                <div class="sm:col-span-2">
                                    <label class="text-sm font-medium text-gray-700">Beneficiario *</label>
                                    <input
                                        v-model="createForm.beneficiario"
                                        type="text"
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        required
                                        maxlength="255"
                                    />
                                    <p v-if="createForm.errors.beneficiario" class="mt-1 text-sm text-red-600">
                                        {{ createForm.errors.beneficiario }}
                                    </p>
                                </div>

                                <div>
                                    <label class="text-sm font-medium text-gray-700">RUC Beneficiario</label>
                                    <input
                                        v-model="createForm.beneficiario_ruc"
                                        type="text"
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        maxlength="100"
                                    />
                                    <p v-if="createForm.errors.beneficiario_ruc" class="mt-1 text-sm text-red-600">
                                        {{ createForm.errors.beneficiario_ruc }}
                                    </p>
                                </div>

                                <div>
                                    <label class="text-sm font-medium text-gray-700">Estado *</label>
                                    <select
                                        v-model="createForm.estado"
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        required
                                    >
                                        <option value="EMITIDO">EMITIDO</option>
                                        <option value="COBRADO">COBRADO</option>
                                        <option value="ANULADO">ANULADO</option>
                                    </select>
                                    <p v-if="createForm.errors.estado" class="mt-1 text-sm text-red-600">
                                        {{ createForm.errors.estado }}
                                    </p>
                                </div>

                                <div class="sm:col-span-2">
                                    <label class="text-sm font-medium text-gray-700">Área Solicitante *</label>
                                    <select
                                        v-model="createForm.area_solicitante_id"
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        required
                                    >
                                        <option value="">Seleccionar área</option>
                                        <option v-for="area in areas" :key="area.id" :value="area.id">
                                            {{ area.nombre }}
                                        </option>
                                    </select>
                                    <p v-if="createForm.errors.area_solicitante_id" class="mt-1 text-sm text-red-600">
                                        {{ createForm.errors.area_solicitante_id }}
                                    </p>
                                </div>

                                <div class="sm:col-span-2">
                                    <label class="text-sm font-medium text-gray-700">Descripción</label>
                                    <textarea
                                        v-model="createForm.descripcion"
                                        rows="2"
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    ></textarea>
                                    <p v-if="createForm.errors.descripcion" class="mt-1 text-sm text-red-600">
                                        {{ createForm.errors.descripcion }}
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

                <!-- Tabla de Listado -->
                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                    <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800">Listado de Cheques</h3>
                            <p class="text-sm text-gray-500">Total: {{ cheques.length }} cheques registrados</p>
                        </div>
                    </div>
                    <div class="max-h-[600px] overflow-auto">
                        <table class="min-w-full text-left text-sm text-gray-700">
                            <thead class="bg-gray-50 text-xs uppercase text-gray-500 sticky top-0">
                                <tr>
                                    <th class="px-4 py-3">Número</th>
                                    <th class="px-4 py-3">Banco</th>
                                    <th class="px-4 py-3">Beneficiario</th>
                                    <th class="px-4 py-3">Monto</th>
                                    <th class="px-4 py-3">Saldo</th>
                                    <th class="px-4 py-3">Fecha</th>
                                    <th class="px-4 py-3">Estado</th>
                                    <th class="px-4 py-3">Área</th>
                                    <th class="px-4 py-3">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="cheque in cheques"
                                    :key="cheque.id"
                                    class="border-b border-gray-100 hover:bg-gray-50"
                                >
                                    <td class="px-4 py-3 font-medium text-gray-900">{{ cheque.numero_cheque }}</td>
                                    <td class="px-4 py-3 text-gray-700">{{ cheque.banco }}</td>
                                    <td class="px-4 py-3 text-gray-700">{{ cheque.beneficiario }}</td>
                                    <td class="px-4 py-3 text-gray-900 font-medium">{{ formatCurrency(cheque.monto_total) }}</td>
                                    <td class="px-4 py-3 text-gray-700">{{ formatCurrency(cheque.saldo_disponible) }}</td>
                                    <td class="px-4 py-3 text-gray-700">{{ formatDate(cheque.fecha_emision) }}</td>
                                    <td class="px-4 py-3">
                                        <span :class="getEstadoBadgeClass(cheque.estado)" class="px-2 py-1 rounded-full text-xs font-medium">
                                            {{ cheque.estado }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-gray-700">{{ cheque.area_solicitante?.nombre || '-' }}</td>
                                    <td class="px-4 py-3 space-x-2 text-sm">
                                        <button
                                            class="inline-flex items-center justify-center rounded-md p-1.5 text-indigo-600 hover:bg-indigo-50 transition"
                                            type="button"
                                            @click="startEdit(cheque)"
                                            title="Editar"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        <button
                                            class="inline-flex items-center justify-center rounded-md p-1.5 text-red-600 hover:bg-red-50 transition"
                                            type="button"
                                            @click="destroyCheque(cheque.id)"
                                            title="Eliminar"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="!cheques.length">
                                    <td colspan="9" class="px-4 py-8 text-center text-gray-500">
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
