<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    activos: { type: Array, default: () => [] },
    areas: { type: Array, default: () => [] },
    ubicaciones: { type: Array, default: () => [] },
    clasificaciones: { type: Array, default: () => [] },
    tipos: { type: Array, default: () => [] },
    fuentes: { type: Array, default: () => [] },
    proveedores: { type: Array, default: () => [] },
    personal: { type: Array, default: () => [] },
    cheques: { type: Array, default: () => [] },
});

const page = usePage();
const can = (permission) => page.props.auth.user?.permissions?.includes(permission);
const canManage = computed(() => can('activos.manage'));

const currencySymbol = computed(() => page.props.system?.moneda || 'C$');
const formatCurrency = (value) => {
    if (value === null || value === undefined || value === '') return '-';
    return `${currencySymbol.value}${Number(value).toLocaleString('es-ES', { minimumFractionDigits: 2 })}`;
};

const busqueda = ref('');

const estadoOptions = [
    { value: 'BUENO', label: 'Bueno' },
    { value: 'REGULAR', label: 'Regular' },
    { value: 'MALO', label: 'Malo' },
];

const createForm = useForm({
    codigo_inventario: '',
    nombre_activo: '',
    area_id: '',
    ubicacion_id: '',
    clasificacion_id: '',
    tipo_activo_id: '',
    fuente_financiamiento_id: '',
    proveedor_id: '',
    personal_id: '',
    cheque_id: '',
    monto_cheque_utilizado: '',
    cantidad: 1,
    precio_adquisicion: '',
    fecha_adquisicion: '',
    numero_factura: '',
    estado: 'BUENO',
    descripcion: '',
    marca: '',
    modelo: '',
    color: '',
    serie: '',
    foto: '',
});

const editForm = useForm({
    codigo_inventario: '',
    nombre_activo: '',
    area_id: '',
    ubicacion_id: '',
    clasificacion_id: '',
    tipo_activo_id: '',
    fuente_financiamiento_id: '',
    proveedor_id: '',
    personal_id: '',
    cheque_id: '',
    monto_cheque_utilizado: '',
    cantidad: 1,
    precio_adquisicion: '',
    fecha_adquisicion: '',
    numero_factura: '',
    estado: 'BUENO',
    descripcion: '',
    marca: '',
    modelo: '',
    color: '',
    serie: '',
    foto: '',
});

const showEditPanel = ref(false);
const selectedActivo = ref(null);

const tipoByClasificacion = computed(() => {
    const grouped = {};
    props.tipos.forEach((t) => {
        grouped[t.clasificacion_id] = grouped[t.clasificacion_id] || [];
        grouped[t.clasificacion_id].push(t);
    });
    return grouped;
});

const clasificacionById = computed(() => {
    const map = {};
    props.clasificaciones.forEach((c) => {
        map[c.id] = c;
    });
    return map;
});

const createCodigoTouched = ref(false);
const editCodigoTouched = ref(false);

const buildCodigoInventario = (clasificacionId) => {
    const cls = clasificacionById.value[clasificacionId];
    if (!cls?.codigo) return '';
    const parts = cls.codigo.replace(/\s+/g, '-').split('-').filter(Boolean);
    const prefix = parts.slice(0, 3);
    if (!prefix.length) return '';
    return `${prefix.join('-')}-000-000-000`;
};

const activosFiltrados = computed(() => {
    const term = busqueda.value.trim().toLowerCase();
    if (!term) return props.activos;

    return props.activos.filter((a) =>
        [
            a.codigo_inventario,
            a.nombre_activo,
            a.area,
            a.ubicacion,
            a.clasificacion,
            a.responsable,
        ]
            .filter(Boolean)
            .some((value) => value.toLowerCase().includes(term))
    );
});

const submitCreate = () => {
    createForm.post(route('activos.store'), {
        onSuccess: () => {
            createForm.reset();
            createCodigoTouched.value = false;
        },
    });
};

const destroyActivo = (id) => {
    router.delete(route('activos.destroy', id));
};

const startEdit = (activo) => {
    selectedActivo.value = activo;
    editCodigoTouched.value = false;
    editForm.clearErrors();
    editForm.codigo_inventario = activo.codigo_inventario || '';
    editForm.nombre_activo = activo.nombre_activo || '';
    editForm.area_id = activo.area_id || '';
    editForm.ubicacion_id = activo.ubicacion_id || '';
    editForm.clasificacion_id = activo.clasificacion_id || '';
    editForm.tipo_activo_id = activo.tipo_activo_id || '';
    editForm.fuente_financiamiento_id = activo.fuente_financiamiento_id || '';
    editForm.proveedor_id = activo.proveedor_id || '';
    editForm.personal_id = activo.personal_id || '';
    editForm.cheque_id = activo.cheque_id || '';
    editForm.monto_cheque_utilizado = activo.monto_cheque_utilizado || '';
    editForm.cantidad = activo.cantidad || 1;
    editForm.precio_adquisicion = activo.precio_adquisicion || '';
    editForm.fecha_adquisicion = activo.fecha_adquisicion || '';
    editForm.numero_factura = activo.numero_factura || '';
    editForm.estado = activo.estado || 'BUENO';
    editForm.descripcion = activo.descripcion || '';
    editForm.marca = activo.marca || '';
    editForm.modelo = activo.modelo || '';
    editForm.color = activo.color || '';
    editForm.serie = activo.serie || '';
    editForm.foto = activo.foto || '';
    showEditPanel.value = true;
};

const submitEdit = () => {
    if (!selectedActivo.value) return;
    editForm.put(route('activos.update', selectedActivo.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            showEditPanel.value = false;
            selectedActivo.value = null;
            editForm.reset();
        },
    });
};

const closeEditPanel = () => {
    showEditPanel.value = false;
    selectedActivo.value = null;
    editForm.reset();
    editForm.clearErrors();
    editCodigoTouched.value = false;
};

const onCreateCodigoInput = () => {
    createCodigoTouched.value = true;
};

const onEditCodigoInput = () => {
    editCodigoTouched.value = true;
};

watch(
    () => createForm.clasificacion_id,
    (id) => {
        const plantilla = buildCodigoInventario(id);
        if (plantilla && (!createForm.codigo_inventario || !createCodigoTouched.value)) {
            createForm.codigo_inventario = plantilla;
        }
    }
);

watch(
    () => editForm.clasificacion_id,
    (id) => {
        if (!showEditPanel.value) return;
        const plantilla = buildCodigoInventario(id);
        if (!plantilla) return;
        const isSameAsOriginal = selectedActivo.value && selectedActivo.value.clasificacion_id === id && !editCodigoTouched.value;
        if (isSameAsOriginal) return;
        if (!editCodigoTouched.value || !editForm.codigo_inventario) {
            editForm.codigo_inventario = plantilla;
        }
    }
);
</script>

<template>
    <Head title="Activos" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-100">Activos fijos</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Alta, control y consulta del inventario físico.</p>
                </div>
                <div class="flex gap-3 text-sm">
                    <a
                        :href="route('activos.trazabilidad')"
                        class="inline-flex items-center gap-2 rounded-md border border-gray-200 px-3 py-2 text-gray-700 shadow-sm transition hover:border-indigo-300 hover:text-indigo-700 dark:border-gray-600 dark:text-gray-300 dark:hover:border-indigo-400 dark:hover:text-indigo-400"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 7h4m0 0h14M7 7v14m14-14v10m0 0h-4m4 0H7m0 0V7" />
                        </svg>
                        Trazabilidad
                    </a>
                    <a
                        :href="route('activos.reportes')"
                        class="inline-flex items-center gap-2 rounded-md border border-gray-200 px-3 py-2 text-gray-700 shadow-sm transition hover:border-indigo-300 hover:text-indigo-700 dark:border-gray-600 dark:text-gray-300 dark:hover:border-indigo-400 dark:hover:text-indigo-400"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h10m-6 6h12" />
                        </svg>
                        Reportes
                    </a>
                    <a
                        :href="route('activos.resumen')"
                        class="inline-flex items-center gap-2 rounded-md border border-gray-200 px-3 py-2 text-gray-700 shadow-sm transition hover:border-indigo-300 hover:text-indigo-700 dark:border-gray-600 dark:text-gray-300 dark:hover:border-indigo-400 dark:hover:text-indigo-400"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 12h16M4 12l4-4m-4 4l4 4" />
                        </svg>
                        Resumen
                    </a>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl space-y-8 sm:px-6 lg:px-8">
                <div v-if="canManage" class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-indigo-500 dark:text-indigo-400">Registro rápido</p>
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Registrar activo</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Solo los campos clave son obligatorios; los demás son opcionales.</p>
                        </div>
                        <div class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">Nuevo ingreso</div>
                    </div>

                    <form class="mt-6 space-y-6" @submit.prevent="submitCreate">
                        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                            <div class="sm:col-span-2 lg:col-span-2">
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Nombre del activo *</label>
                                <input v-model="createForm.nombre_activo" type="text" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" required maxlength="255" />
                                <p v-if="createForm.errors.nombre_activo" class="mt-1 text-sm text-red-600">{{ createForm.errors.nombre_activo }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Código inventario *</label>
                                <input
                                    v-model="createForm.codigo_inventario"
                                    type="text"
                                    class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                    required
                                    maxlength="50"
                                    @input="onCreateCodigoInput"
                                />
                                <p v-if="createForm.errors.codigo_inventario" class="mt-1 text-sm text-red-600">{{ createForm.errors.codigo_inventario }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Cantidad *</label>
                                <input v-model.number="createForm.cantidad" type="number" min="1" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" required />
                                <p v-if="createForm.errors.cantidad" class="mt-1 text-sm text-red-600">{{ createForm.errors.cantidad }}</p>
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Área *</label>
                                <select v-model="createForm.area_id" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" required>
                                    <option value="" disabled>Selecciona área</option>
                                    <option v-for="area in props.areas" :key="area.id" :value="area.id">{{ area.nombre }}</option>
                                </select>
                                <p v-if="createForm.errors.area_id" class="mt-1 text-sm text-red-600">{{ createForm.errors.area_id }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Ubicación *</label>
                                <select v-model="createForm.ubicacion_id" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" required>
                                    <option value="" disabled>Selecciona ubicación</option>
                                    <option v-for="ubi in props.ubicaciones" :key="ubi.id" :value="ubi.id">{{ ubi.nombre }}</option>
                                </select>
                                <p v-if="createForm.errors.ubicacion_id" class="mt-1 text-sm text-red-600">{{ createForm.errors.ubicacion_id }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Estado *</label>
                                <select v-model="createForm.estado" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" required>
                                    <option v-for="opt in estadoOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                                </select>
                                <p v-if="createForm.errors.estado" class="mt-1 text-sm text-red-600">{{ createForm.errors.estado }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Clasificación *</label>
                                <select v-model="createForm.clasificacion_id" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" required>
                                    <option value="" disabled>Selecciona clasificación</option>
                                    <option v-for="clas in props.clasificaciones" :key="clas.id" :value="clas.id">
                                        {{ clas.codigo ? `${clas.codigo.replace(/\s+/g, '-')}- ${clas.nombre}` : clas.nombre }}
                                    </option>
                                </select>
                                <p v-if="createForm.errors.clasificacion_id" class="mt-1 text-sm text-red-600">{{ createForm.errors.clasificacion_id }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Tipo</label>
                                <select v-model="createForm.tipo_activo_id" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                    <option value="">(Opcional)</option>
                                    <option
                                        v-for="tipo in tipoByClasificacion[createForm.clasificacion_id] || props.tipos"
                                        :key="tipo.id"
                                        :value="tipo.id"
                                    >
                                        {{ tipo.nombre }}
                                    </option>
                                </select>
                                <p v-if="createForm.errors.tipo_activo_id" class="mt-1 text-sm text-red-600">{{ createForm.errors.tipo_activo_id }}</p>
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Fuente financiamiento *</label>
                                <select v-model="createForm.fuente_financiamiento_id" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" required>
                                    <option value="" disabled>Selecciona fuente</option>
                                    <option v-for="f in props.fuentes" :key="f.id" :value="f.id">{{ f.nombre }}</option>
                                </select>
                                <p v-if="createForm.errors.fuente_financiamiento_id" class="mt-1 text-sm text-red-600">{{ createForm.errors.fuente_financiamiento_id }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Proveedor</label>
                                <select v-model="createForm.proveedor_id" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                    <option value="">(Opcional)</option>
                                    <option v-for="p in props.proveedores" :key="p.id" :value="p.id">{{ p.nombre }}</option>
                                </select>
                                <p v-if="createForm.errors.proveedor_id" class="mt-1 text-sm text-red-600">{{ createForm.errors.proveedor_id }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Responsable</label>
                                <select v-model="createForm.personal_id" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                    <option value="">(Opcional)</option>
                                    <option v-for="per in props.personal" :key="per.id" :value="per.id">{{ per.nombre }} {{ per.apellido }}</option>
                                </select>
                                <p v-if="createForm.errors.personal_id" class="mt-1 text-sm text-red-600">{{ createForm.errors.personal_id }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Costo adquisición</label>
                                <input v-model="createForm.precio_adquisicion" type="number" min="0" step="0.01" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
                                <p v-if="createForm.errors.precio_adquisicion" class="mt-1 text-sm text-red-600">{{ createForm.errors.precio_adquisicion }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Fecha adquisición</label>
                                <input v-model="createForm.fecha_adquisicion" type="date" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
                                <p v-if="createForm.errors.fecha_adquisicion" class="mt-1 text-sm text-red-600">{{ createForm.errors.fecha_adquisicion }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Factura</label>
                                <input v-model="createForm.numero_factura" type="text" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" maxlength="100" />
                                <p v-if="createForm.errors.numero_factura" class="mt-1 text-sm text-red-600">{{ createForm.errors.numero_factura }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Cheque</label>
                                <select v-model="createForm.cheque_id" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                    <option value="">(Opcional)</option>
                                    <option v-for="cheque in props.cheques" :key="cheque.id" :value="cheque.id">
                                        {{ cheque.numero_cheque }} - {{ cheque.beneficiario }} ({{ formatCurrency(cheque.saldo_disponible) }} disponible)
                                    </option>
                                </select>
                                <p v-if="createForm.errors.cheque_id" class="mt-1 text-sm text-red-600">{{ createForm.errors.cheque_id }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Monto del cheque utilizado</label>
                                <input v-model="createForm.monto_cheque_utilizado" type="number" min="0" step="0.01" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" placeholder="Monto asignado de este cheque" />
                                <p v-if="createForm.errors.monto_cheque_utilizado" class="mt-1 text-sm text-red-600">{{ createForm.errors.monto_cheque_utilizado }}</p>
                            </div>
                        </div>

                        <div class="space-y-3 rounded-xl border border-gray-100 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900">
                            <div class="flex items-center justify-between text-sm">
                                <p class="font-semibold text-gray-800 dark:text-gray-100">Detalles opcionales</p>
                                <span class="text-gray-500 dark:text-gray-400">(Marca, modelo, color, serie, foto, descripción)</span>
                            </div>
                            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                <div>
                                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Marca</label>
                                    <input v-model="createForm.marca" type="text" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
                                    <p v-if="createForm.errors.marca" class="mt-1 text-sm text-red-600">{{ createForm.errors.marca }}</p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Modelo</label>
                                    <input v-model="createForm.modelo" type="text" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
                                    <p v-if="createForm.errors.modelo" class="mt-1 text-sm text-red-600">{{ createForm.errors.modelo }}</p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Color</label>
                                    <input v-model="createForm.color" type="text" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
                                    <p v-if="createForm.errors.color" class="mt-1 text-sm text-red-600">{{ createForm.errors.color }}</p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Serie</label>
                                    <input v-model="createForm.serie" type="text" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
                                    <p v-if="createForm.errors.serie" class="mt-1 text-sm text-red-600">{{ createForm.errors.serie }}</p>
                                </div>
                                <div class="lg:col-span-2">
                                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Foto (URL)</label>
                                    <input v-model="createForm.foto" type="text" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
                                    <p v-if="createForm.errors.foto" class="mt-1 text-sm text-red-600">{{ createForm.errors.foto }}</p>
                                </div>
                                <div class="sm:col-span-2 lg:col-span-3">
                                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Descripción</label>
                                    <textarea
                                        v-model="createForm.descripcion"
                                        rows="3"
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                    ></textarea>
                                    <p v-if="createForm.errors.descripcion" class="mt-1 text-sm text-red-600">{{ createForm.errors.descripcion }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button
                                type="submit"
                                :disabled="createForm.processing"
                                class="inline-flex items-center gap-2 rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-700 focus:ring-offset-2 disabled:opacity-50"
                            >
                                Registrar activo
                            </button>
                        </div>
                    </form>
                </div>

                <div v-else class="rounded-2xl border border-dashed border-gray-300 bg-white p-6 text-sm text-gray-700 shadow-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Vista de solo lectura</h3>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">No tienes permisos para crear activos. Puedes revisar el inventario y ver códigos QR.</p>
                </div>

                <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Inventario</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Consulta rápida, elimina registros o descarga el QR.</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-3 text-sm text-gray-600 dark:text-gray-300">
                            <input
                                v-model="busqueda"
                                type="search"
                                placeholder="Buscar por código, nombre, área, ubicación..."
                                class="w-full rounded-md border border-gray-200 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:w-72 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                            />
                            <span class="inline-flex items-center rounded-full bg-indigo-50 px-3 py-1 font-medium text-indigo-700">
                                {{ activosFiltrados.length }} / {{ props.activos.length }} activos
                            </span>
                        </div>
                    </div>
                    <div class="overflow-auto">
                        <table class="min-w-full divide-y divide-gray-100 text-left text-sm text-gray-700 dark:divide-gray-700 dark:text-gray-300">
                            <thead class="bg-gray-50 text-xs uppercase text-gray-500 dark:bg-gray-900 dark:text-gray-400">
                                <tr>
                                    <th class="px-4 py-3">Código</th>
                                    <th class="px-4 py-3">Activo</th>
                                    <th class="px-4 py-3">Área</th>
                                    <th class="px-4 py-3">Clasificación</th>
                                    <th class="px-4 py-3">Ubicación</th>
                                    <th class="px-4 py-3">Responsable</th>
                                    <th class="px-4 py-3"># Cheque</th>
                                    <th class="px-4 py-3">Estado</th>
                                    <th class="px-4 py-3">Costo</th>
                                    <th class="px-4 py-3">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="activo in activosFiltrados"
                                    :key="activo.id"
                                    class="border-b border-gray-100 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700/50"
                                >
                                    <td class="px-4 py-3 font-mono text-xs text-gray-600">{{ activo.codigo_inventario }}</td>
                                    <td class="px-4 py-3 font-medium text-gray-900">{{ activo.nombre_activo }}</td>
                                    <td class="px-4 py-3 text-gray-700">{{ activo.area }}</td>
                                    <td class="px-4 py-3 text-gray-700">{{ activo.clasificacion }}</td>
                                    <td class="px-4 py-3 text-gray-700">{{ activo.ubicacion }}</td>
                                    <td class="px-4 py-3 text-gray-700">{{ activo.responsable || 'No asignado' }}</td>
                                    <td class="px-4 py-3 text-gray-700">
                                        <span v-if="activo.numero_cheque" class="inline-flex items-center rounded-full bg-teal-50 px-2 py-1 text-xs font-medium text-teal-700">
                                            {{ activo.numero_cheque }}
                                        </span>
                                        <span v-else class="text-gray-400">-</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span
                                            class="inline-flex items-center rounded-full px-2 py-1 text-xs font-semibold"
                                            :class="{
                                                'bg-green-100 text-green-800': activo.estado === 'BUENO',
                                                'bg-amber-100 text-amber-800': activo.estado === 'REGULAR',
                                                'bg-rose-100 text-rose-800': activo.estado === 'MALO',
                                                'bg-gray-200 text-gray-700': !['BUENO', 'REGULAR', 'MALO'].includes(activo.estado),
                                            }"
                                        >
                                            {{ activo.estado }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-gray-900">
                                        {{ formatCurrency(activo.precio_adquisicion) }}
                                    </td>
                                    <td class="px-4 py-3 flex flex-wrap gap-2 text-sm">
                                        <button
                                            v-if="canManage"
                                            class="text-indigo-600 hover:underline"
                                            type="button"
                                            @click="startEdit(activo)"
                                        >
                                            Editar
                                        </button>
                                        <button
                                            v-if="canManage"
                                            class="text-red-600 hover:underline"
                                            type="button"
                                            @click="destroyActivo(activo.id)"
                                        >
                                            Eliminar
                                        </button>
                                        <a
                                            :href="route('activos.qr', activo.id) + '?cache=' + encodeURIComponent(activo.updated_at || activo.id)"
                                            target="_blank"
                                            class="text-emerald-600 hover:underline"
                                        >
                                            QR
                                        </a>
                                    </td>
                                </tr>
                                <tr v-if="!props.activos.length">
                                    <td colspan="10" class="px-4 py-4 text-center text-gray-500">Sin registros</td>
                                </tr>
                                <tr v-else-if="!activosFiltrados.length">
                                    <td colspan="10" class="px-4 py-4 text-center text-gray-500">No hay activos que coincidan con la búsqueda</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div v-if="showEditPanel" class="rounded-2xl border border-indigo-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex flex-wrap items-start justify-between gap-3 border-b border-indigo-50 px-6 py-4 dark:border-gray-700">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-indigo-500 dark:text-indigo-400">Edición</p>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Editar activo</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Actualiza los datos del activo seleccionado.</p>
                        </div>
                        <button type="button" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200" @click="closeEditPanel">Cerrar</button>
                    </div>

                    <form class="space-y-6 p-6" @submit.prevent="submitEdit">
                        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            <div class="sm:col-span-2">
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Nombre del activo *</label>
                                <input v-model="editForm.nombre_activo" type="text" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" required maxlength="255" />
                                <p v-if="editForm.errors.nombre_activo" class="mt-1 text-sm text-red-600">{{ editForm.errors.nombre_activo }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Código inventario *</label>
                                <input
                                    v-model="editForm.codigo_inventario"
                                    type="text"
                                    class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                    required
                                    maxlength="50"
                                    @input="onEditCodigoInput"
                                />
                                <p v-if="editForm.errors.codigo_inventario" class="mt-1 text-sm text-red-600">{{ editForm.errors.codigo_inventario }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Cantidad *</label>
                                <input v-model.number="editForm.cantidad" type="number" min="1" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" required />
                                <p v-if="editForm.errors.cantidad" class="mt-1 text-sm text-red-600">{{ editForm.errors.cantidad }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Área *</label>
                                <select v-model="editForm.area_id" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" required>
                                    <option value="" disabled>Selecciona área</option>
                                    <option v-for="area in props.areas" :key="area.id" :value="area.id">{{ area.nombre }}</option>
                                </select>
                                <p v-if="editForm.errors.area_id" class="mt-1 text-sm text-red-600">{{ editForm.errors.area_id }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Ubicación *</label>
                                <select v-model="editForm.ubicacion_id" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" required>
                                    <option value="" disabled>Selecciona ubicación</option>
                                    <option v-for="ubi in props.ubicaciones" :key="ubi.id" :value="ubi.id">{{ ubi.nombre }}</option>
                                </select>
                                <p v-if="editForm.errors.ubicacion_id" class="mt-1 text-sm text-red-600">{{ editForm.errors.ubicacion_id }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Estado *</label>
                                <select v-model="editForm.estado" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" required>
                                    <option v-for="opt in estadoOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                                </select>
                                <p v-if="editForm.errors.estado" class="mt-1 text-sm text-red-600">{{ editForm.errors.estado }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Clasificación *</label>
                                <select v-model="editForm.clasificacion_id" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" required>
                                    <option value="" disabled>Selecciona clasificación</option>
                                    <option v-for="clas in props.clasificaciones" :key="clas.id" :value="clas.id">
                                        {{ clas.codigo ? `${clas.codigo.replace(/\s+/g, '-')}- ${clas.nombre}` : clas.nombre }}
                                    </option>
                                </select>
                                <p v-if="editForm.errors.clasificacion_id" class="mt-1 text-sm text-red-600">{{ editForm.errors.clasificacion_id }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Tipo</label>
                                <select v-model="editForm.tipo_activo_id" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                    <option value="">(Opcional)</option>
                                    <option
                                        v-for="tipo in tipoByClasificacion[editForm.clasificacion_id] || props.tipos"
                                        :key="tipo.id"
                                        :value="tipo.id"
                                    >
                                        {{ tipo.nombre }}
                                    </option>
                                </select>
                                <p v-if="editForm.errors.tipo_activo_id" class="mt-1 text-sm text-red-600">{{ editForm.errors.tipo_activo_id }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Fuente financiamiento *</label>
                                <select v-model="editForm.fuente_financiamiento_id" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" required>
                                    <option value="" disabled>Selecciona fuente</option>
                                    <option v-for="f in props.fuentes" :key="f.id" :value="f.id">{{ f.nombre }}</option>
                                </select>
                                <p v-if="editForm.errors.fuente_financiamiento_id" class="mt-1 text-sm text-red-600">{{ editForm.errors.fuente_financiamiento_id }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Proveedor</label>
                                <select v-model="editForm.proveedor_id" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                    <option value="">(Opcional)</option>
                                    <option v-for="p in props.proveedores" :key="p.id" :value="p.id">{{ p.nombre }}</option>
                                </select>
                                <p v-if="editForm.errors.proveedor_id" class="mt-1 text-sm text-red-600">{{ editForm.errors.proveedor_id }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Responsable</label>
                                <select v-model="editForm.personal_id" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                    <option value="">(Opcional)</option>
                                    <option v-for="per in props.personal" :key="per.id" :value="per.id">{{ per.nombre }} {{ per.apellido }}</option>
                                </select>
                                <p v-if="editForm.errors.personal_id" class="mt-1 text-sm text-red-600">{{ editForm.errors.personal_id }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Costo adquisición</label>
                                <input v-model="editForm.precio_adquisicion" type="number" min="0" step="0.01" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
                                <p v-if="editForm.errors.precio_adquisicion" class="mt-1 text-sm text-red-600">{{ editForm.errors.precio_adquisicion }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Fecha adquisición</label>
                                <input v-model="editForm.fecha_adquisicion" type="date" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
                                <p v-if="editForm.errors.fecha_adquisicion" class="mt-1 text-sm text-red-600">{{ editForm.errors.fecha_adquisicion }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Factura</label>
                                <input v-model="editForm.numero_factura" type="text" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" maxlength="100" />
                                <p v-if="editForm.errors.numero_factura" class="mt-1 text-sm text-red-600">{{ editForm.errors.numero_factura }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Cheque</label>
                                <select v-model="editForm.cheque_id" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                    <option value="">(Opcional)</option>
                                    <option v-for="cheque in props.cheques" :key="cheque.id" :value="cheque.id">
                                        {{ cheque.numero_cheque }} - {{ cheque.beneficiario }} ({{ formatCurrency(cheque.saldo_disponible) }} disponible)
                                    </option>
                                </select>
                                <p v-if="editForm.errors.cheque_id" class="mt-1 text-sm text-red-600">{{ editForm.errors.cheque_id }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Monto del cheque utilizado</label>
                                <input v-model="editForm.monto_cheque_utilizado" type="number" min="0" step="0.01" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" placeholder="Monto asignado de este cheque" />
                                <p v-if="editForm.errors.monto_cheque_utilizado" class="mt-1 text-sm text-red-600">{{ editForm.errors.monto_cheque_utilizado }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Marca</label>
                                <input v-model="editForm.marca" type="text" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
                                <p v-if="editForm.errors.marca" class="mt-1 text-sm text-red-600">{{ editForm.errors.marca }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Modelo</label>
                                <input v-model="editForm.modelo" type="text" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
                                <p v-if="editForm.errors.modelo" class="mt-1 text-sm text-red-600">{{ editForm.errors.modelo }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Color</label>
                                <input v-model="editForm.color" type="text" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
                                <p v-if="editForm.errors.color" class="mt-1 text-sm text-red-600">{{ editForm.errors.color }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Serie</label>
                                <input v-model="editForm.serie" type="text" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
                                <p v-if="editForm.errors.serie" class="mt-1 text-sm text-red-600">{{ editForm.errors.serie }}</p>
                            </div>
                            <div class="lg:col-span-2">
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Foto (URL)</label>
                                <input v-model="editForm.foto" type="text" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
                                <p v-if="editForm.errors.foto" class="mt-1 text-sm text-red-600">{{ editForm.errors.foto }}</p>
                            </div>
                            <div class="sm:col-span-2 lg:col-span-3">
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Descripción</label>
                                <textarea
                                    v-model="editForm.descripcion"
                                    rows="3"
                                    class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                ></textarea>
                                <p v-if="editForm.errors.descripcion" class="mt-1 text-sm text-red-600">{{ editForm.errors.descripcion }}</p>
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 border-t border-gray-100 pt-4 dark:border-gray-700">
                            <button type="button" class="rounded-md px-4 py-2 text-sm font-semibold text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-gray-100" @click="closeEditPanel">Cancelar</button>
                            <button
                                type="submit"
                                :disabled="editForm.processing"
                                class="inline-flex items-center gap-2 rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50"
                            >
                                Guardar cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
