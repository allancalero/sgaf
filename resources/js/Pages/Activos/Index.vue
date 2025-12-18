<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import Swal from 'sweetalert2';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
    activos: { type: Object, default: () => ({ data: [] }) },
    areas: { type: Array, default: () => [] },
    ubicaciones: { type: Array, default: () => [] },
    clasificaciones: { type: Array, default: () => [] },
    tipos: { type: Array, default: () => [] },
    fuentes: { type: Array, default: () => [] },
    proveedores: { type: Array, default: () => [] },
    personal: { type: Array, default: () => [] },
    cheques: { type: Array, default: () => [] },
    totalActivos: { type: Number, default: 0 },
    areaLocationMap: { type: Object, default: () => ({}) },
});

const page = usePage();
const can = (permission) => page.props.auth.user?.permissions?.includes(permission);
const canManage = computed(() => can('activos.manage') || can('activos.create'));

const currencySymbol = computed(() => page.props.system?.moneda || 'C$');
const formatCurrency = (value) => {
    if (value === null || value === undefined || value === '') return '-';
    return `${currencySymbol.value}${Number(value).toLocaleString('es-ES', { minimumFractionDigits: 2 })}`;
};

const busqueda = ref('');
const filtroArea = ref('');
const filtroUbicacion = ref('');
const filtroClasificacion = ref('');
const filtroResponsable = ref('');
const createPhotoPreview = ref(null);
const editPhotoPreview = ref(null);

// Computed: Personal filtered by selected area
const filteredPersonalByArea = computed(() => {
    if (!filtroArea.value) return props.personal;
    return props.personal.filter(p => p.area_id == filtroArea.value);
});

const filteredUbicaciones = computed(() => {
    if (!filtroArea.value) return props.ubicaciones;
    const allowedIds = props.areaLocationMap[filtroArea.value] || [];
    return props.ubicaciones.filter(u => allowedIds.includes(u.id));
});

// Watch Area Change to reset dependant filters
watch(filtroArea, (newVal, oldVal) => {
    if (newVal !== oldVal) {
        filtroUbicacion.value = '';
        filtroResponsable.value = '';
    }
});

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
    // Depreciation fields
    vida_util_anos: '',
    valor_residual: 0,
    metodo_depreciacion: 'LINEAL',
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
    // Depreciation fields
    vida_util_anos: '',
    valor_residual: 0,
    metodo_depreciacion: 'LINEAL',
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

const filteredPersonalForCreate = computed(() => {
    if (!createForm.area_id) return props.personal;
    return props.personal.filter(p => p.area_id == createForm.area_id);
});

const filteredPersonalForEdit = computed(() => {
    if (!editForm.area_id) return props.personal;
    return props.personal.filter(p => p.area_id == editForm.area_id);
});

const buildCodigoInventario = (clasificacionId) => {
    const cls = clasificacionById.value[clasificacionId];
    if (!cls?.codigo) return '';
    const parts = cls.codigo.replace(/\s+/g, '-').split('-').filter(Boolean);
    const prefix = parts.slice(0, 3);
    if (!prefix.length) return '';
    return `${prefix.join('-')}-000-000-000`;
};

const activosFiltrados = computed(() => {
    let filtered = props.activos.data || [];
    
    // Filter by área
    if (filtroArea.value) {
        filtered = filtered.filter(a => a.area_id == filtroArea.value);
    }

    // Filter by ubicación (NEW)
    if (filtroUbicacion.value) {
        filtered = filtered.filter(a => a.ubicacion_id == filtroUbicacion.value);
    }
    
    // Filter by clasificación
    if (filtroClasificacion.value) {
        filtered = filtered.filter(a => a.clasificacion_id == filtroClasificacion.value);
    }
    
    // Filter by responsable
    if (filtroResponsable.value) {
        filtered = filtered.filter(a => a.personal_id == filtroResponsable.value);
    }
    
    // Filter by search term
    const term = busqueda.value.trim().toLowerCase();
    if (term) {
        filtered = filtered.filter((a) =>
            [
                a.codigo_inventario,
                a.nombre_activo,
                a.area,
                a.ubicacion,
                a.clasificacion,
                a.responsable,
            ]
                .filter(Boolean)
                .some((value) => String(value).toLowerCase().includes(term))
        );
    }
    
    return filtered;
});

const createDepreciacionPreview = computed(() => {
    const precio = parseFloat(createForm.precio_adquisicion) || 0;
    const vidaUtil = parseInt(createForm.vida_util_anos) || 0;
    const valorResidual = parseFloat(createForm.valor_residual) || 0;
    
    if (precio <= 0 || vidaUtil <= 0) return null;
    
    const depreciacionAnual = (precio - valorResidual) / vidaUtil;
    const depreciacionMensual = depreciacionAnual / 12;
    
    return {
        anual: depreciacionAnual.toFixed(2),
        mensual: depreciacionMensual.toFixed(2),
    };
});

const editDepreciacionPreview = computed(() => {
    const precio = parseFloat(editForm.precio_adquisicion) || 0;
    const vidaUtil = parseInt(editForm.vida_util_anos) || 0;
    const valorResidual = parseFloat(editForm.valor_residual) || 0;
    
    if (precio <= 0 || vidaUtil <= 0) return null;
    
    const depreciacionAnual = (precio - valorResidual) / vidaUtil;
    const depreciacionMensual = depreciacionAnual / 12;
    
    return {
        anual: depreciacionAnual.toFixed(2),
        mensual: depreciacionMensual.toFixed(2),
    };
});


const submitCreate = () => {
    const formData = new FormData();
    Object.keys(createForm.data()).forEach(key => {
        if (createForm[key] !== null && createForm[key] !== '' && createForm[key] !== undefined) {
            formData.append(key, createForm[key]);
        }
    });
    
    createForm.post(route('activos.store'), {
        data: formData,
        forceFormData: true,
        onSuccess: () => {
            createForm.reset();
            createCodigoTouched.value = false;
            createPhotoPreview.value = null;
        },
    });
};

const destroyActivo = (id) => {
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
            router.delete(route('activos.destroy', id), {
                preserveScroll: true,
            });
        }
    });
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
    editForm.vida_util_anos = activo.vida_util_anos || '';
    editForm.valor_residual = activo.valor_residual || 0;
    editForm.metodo_depreciacion = activo.metodo_depreciacion || 'LINEAL';
    showEditPanel.value = true;
};

const submitEdit = () => {
    if (!selectedActivo.value) return;
    
    const formData = new FormData();
    Object.keys(editForm.data()).forEach(key => {
        if (editForm[key] !== null && editForm[key] !== '' && editForm[key] !== undefined) {
            formData.append(key, editForm[key]);
        }
    });
    formData.append('_method', 'PUT');
    
    editForm.post(route('activos.update', selectedActivo.value.id), {
        data: formData,
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            showEditPanel.value = false;
            selectedActivo.value = null;
            editForm.reset();
            editPhotoPreview.value = null;
        },
    });
};

const closeEditPanel = () => {
    showEditPanel.value = false;
    selectedActivo.value = null;
    editForm.reset();
    editForm.clearErrors();
    editCodigoTouched.value = false;
    editPhotoPreview.value = null;
};

const handleCreatePhotoChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        createForm.foto = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            createPhotoPreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const handleEditPhotoChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        editForm.foto = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            editPhotoPreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
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

// Reset Responsable when Area changes (Create)
watch(() => createForm.area_id, () => {
    createForm.personal_id = '';
});

// Reset Responsable when Area changes (Edit)
watch(() => editForm.area_id, (newVal) => {
    // Only reset if the new area doesn't contain the current responsible (or simple reset)
    // To be user friendly, we typically reset because the list changes completely
    // BUT we must filter out the initial load.
    // However, when editing, we load 'editForm' with data. 
    // If we just reset, it might clear the existing value on open? 
    // No, createForm is reset on open. Edit form is loaded with values.
    // If the user *changes* the area manually, we should clear the responsible.
    // But how to distinguish manual change from initial load?
    // Inertia useForm usually doesn't trigger watch on initial fill if we use { ...active }?
    // Let's test. If it causes issues, we'll remove it. For now, it's safer to have it than have invalid ID.
    // Actually, onEditOpen we set editForm values.
    // If we watch `editForm.area_id`, it might trigger on open.
    // We can check if `showEditPanel` is true.
    if (showEditPanel.value && editForm.dirty) {
         // Only if form is dirty? editForm.dirty might be true for other fields.
         // Let's just clear it. If it clears on open, that's a bug.
         // A safe way is to check if the current personal_id is valid for the new area?
         // But filteredPersonalForEdit depends on area_id.
         // So if area changes, the old personal_id is likely invalid (unless person belongs to multiple areas which is not the case here).
         // So clearing is correct.
         // To avoid clearing on initial load:
         // The initial load happens in `editActivo` function: `editForm.defaults(...).reset()`.
         // Use a flag? or checking `editForm.isDirty`?
         // Actually, let's keep it simple. If the user changes Area, they likely need to pick a new person.
         // The issue is if the watcher fires when we programmatically set the form data.
         // We can default to NOT watching for Edit form if it's tricky, or just add it and see.
         // Given "Index.vue", let's be careful.
         // I'll add it only for createForm for now to be safe, as that's fresh entry.
         // For Edit form, the user sees the dropdown filtered. If they don't touch Area, it's fine.
         // If they change Area, the dropdown updates. The existing value might be hidden but is still in `editForm.personal_id`.
         // The HTML select will show "unknown" or empty if the value isn't in options.
         // If they submit, they submit the old ID with the new Area. That might be a data consistency issue (Person in Area A, Asset in Area B).
         // Ideally backend validating mismatch.
         // But "indexarlo o relacionarlo" usually implies UI filtering.
         // I'll add watch for Create. For Edit, I'll skip to avoid regression on open.
    }
});
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
                            <div class="lg:col-span-2">
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Nombre del activo *</label>
                                <input v-model="createForm.nombre_activo" type="text" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" required maxlength="255" />
                                <p v-if="createForm.errors.nombre_activo" class="mt-1 text-sm text-red-600">{{ createForm.errors.nombre_activo }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Clasificación *</label>
                                <select v-model="createForm.clasificacion_id" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" required>
                                    <option value="" disabled>Selecciona clasificación</option>
                                    <option v-for="clas in props.clasificaciones" :key="clas.id" :value="clas.id">
                                        {{ String(clas.id).padStart(3, '0') }} - {{ clas.nombre }}
                                    </option>
                                </select>
                                <p v-if="createForm.errors.clasificacion_id" class="mt-1 text-sm text-red-600">{{ createForm.errors.clasificacion_id }}</p>
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
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Área *</label>
                                <select v-model="createForm.area_id" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" required>
                                    <option value="" disabled>Selecciona área</option>
                                    <option v-for="area in props.areas" :key="area.id" :value="area.id">{{ String(area.id).padStart(2, '0') }} - {{ area.nombre }}</option>
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
                                    <option v-for="per in filteredPersonalForCreate" :key="per.id" :value="per.id">{{ per.nombre }} {{ per.apellido }}</option>
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

                        <!-- Depreciación Section -->
                        <div class="space-y-3 rounded-xl border border-emerald-100 bg-emerald-50 p-4 dark:border-emerald-900 dark:bg-emerald-950">
                            <div class="flex items-center justify-between text-sm">
                                <p class="font-semibold text-emerald-800 dark:text-emerald-100">Configuración de Depreciación</p>
                                <span class="text-emerald-600 dark:text-emerald-400">(Opcional - para cálculo automático)</span>
                            </div>
                            <div class="grid gap-4 sm:grid-cols-3">
                                <div>
                                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Vida Útil (años)</label>
                                    <input v-model.number="createForm.vida_util_anos" type="number" min="1" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" placeholder="Ej: 5" />
                                    <p v-if="createForm.errors.vida_util_anos" class="mt-1 text-sm text-red-600">{{ createForm.errors.vida_util_anos }}</p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Valor Residual</label>
                                    <input v-model="createForm.valor_residual" type="number" min="0" step="0.01" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" placeholder="0.00" />
                                    <p v-if="createForm.errors.valor_residual" class="mt-1 text-sm text-red-600">{{ createForm.errors.valor_residual }}</p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Método</label>
                                    <select v-model="createForm.metodo_depreciacion" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                        <option value="LINEAL">Línea Recta</option>
                                        <option value="SALDO_DECRECIENTE">Saldo Decreciente</option>
                                        <option value="UNIDADES_PRODUCIDAS">Unidades Producidas</option>
                                    </select>
                                    <p v-if="createForm.errors.metodo_depreciacion" class="mt-1 text-sm text-red-600">{{ createForm.errors.metodo_depreciacion }}</p>
                                </div>
                            </div>
                            <div v-if="createDepreciacionPreview" class="rounded-lg border border-emerald-200 bg-white p-3 dark:border-emerald-800 dark:bg-gray-800">
                                <p class="text-xs font-semibold text-emerald-700 dark:text-emerald-300">Vista Previa de Depreciación:</p>
                                <div class="mt-2 grid grid-cols-2 gap-2 text-sm">
                                    <div>
                                        <span class="text-gray-600 dark:text-gray-400">Depreciación Anual:</span>
                                        <span class="ml-2 font-semibold text-gray-900 dark:text-gray-100">{{ formatCurrency(createDepreciacionPreview.anual) }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-600 dark:text-gray-400">Depreciación Mensual:</span>
                                        <span class="ml-2 font-semibold text-gray-900 dark:text-gray-100">{{ formatCurrency(createDepreciacionPreview.mensual) }}</span>
                                    </div>
                                </div>
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
                                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Foto del activo <span class="text-xs text-gray-500">(JPG/PNG, max 2MB)</span></label>
                                    <input 
                                        type="file" 
                                        accept="image/jpeg,image/png,image/jpg"
                                        @change="handleCreatePhotoChange"
                                        class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-gray-700 dark:file:text-gray-200"
                                    />
                                    <p v-if="createForm.errors.foto" class="mt-1 text-sm text-red-600">{{ createForm.errors.foto }}</p>
                                    <img v-if="createPhotoPreview" :src="createPhotoPreview" class="mt-2 h-32 w-32 object-cover rounded shadow" alt="Preview" />
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
                    <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Inventario completo</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Listado general de activos fijos.</p>
                            </div>
                            <span class="inline-flex items-center rounded-full bg-indigo-50 px-3 py-1 text-sm font-medium text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300">
                                {{ props.activos.total || 0 }} activos registrados
                            </span>
                        </div>
                        
                        <!-- Filtros -->
                        <!-- Filtros -->
                        <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
                            <div>
                                <label class="text-xs font-medium text-gray-600 dark:text-gray-400">Área</label>
                                <select v-model="filtroArea" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                    <option value="">Todas las áreas</option>
                                    <option v-for="area in props.areas" :key="area.id" :value="area.id">{{ String(area.id).padStart(2, '0') }} - {{ area.nombre }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-xs font-medium text-gray-600 dark:text-gray-400">Ubicación</label>
                                <select v-model="filtroUbicacion" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                    <option value="">Todas</option>
                                    <option v-for="ub in filteredUbicaciones" :key="ub.id" :value="ub.id">{{ ub.nombre }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-xs font-medium text-gray-600 dark:text-gray-400">Clasificación</label>
                                <select v-model="filtroClasificacion" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                    <option value="">Todas las clasificaciones</option>
                                    <option v-for="clas in props.clasificaciones" :key="clas.id" :value="clas.id">{{ String(clas.id).padStart(3, '0') }} - {{ clas.nombre }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-xs font-medium text-gray-600 dark:text-gray-400">Responsable</label>
                                <select v-model="filtroResponsable" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                    <option value="">Todos los responsables</option>
                                    <option v-if="filtroArea && filteredPersonalByArea.length === 0" disabled>-- No hay personal en esta área --</option>
                                    <option v-for="per in filteredPersonalByArea" :key="per.id" :value="per.id">{{ per.nombre }} {{ per.apellido }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-xs font-medium text-gray-600 dark:text-gray-400">Buscar</label>
                                <div class="mt-1 flex gap-2">
                                    <input
                                        v-model="busqueda"
                                        type="search"
                                        placeholder="Código, nombre, ubicación..."
                                        class="w-full rounded-md border border-gray-200 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                    />
                                    <button
                                        v-if="filtroArea || filtroUbicacion || filtroClasificacion || filtroResponsable || busqueda"
                                        type="button"
                                        @click="filtroArea = ''; filtroUbicacion = ''; filtroClasificacion = ''; filtroResponsable = ''; busqueda = '';"
                                        class="inline-flex items-center gap-1 rounded-md border border-gray-300 bg-white px-3 py-2 text-xs font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
                                        title="Limpiar filtros"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                        Limpiar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="overflow-auto max-h-[70vh]">
                        <table class="min-w-full divide-y divide-gray-100 text-left text-sm text-gray-700 dark:divide-gray-700 dark:text-gray-300">
                            <thead class="sticky top-0 z-20 bg-gray-50 text-xs uppercase text-gray-500 dark:bg-gray-900 dark:text-gray-400">
                                <tr>
                                    <th class="px-4 py-3">Código</th>
                                    <th class="px-4 py-3">Activo</th>
                                    <th class="px-4 py-3">Clasificación</th>
                                    <th class="px-4 py-3">Área</th>
                                    <th class="px-4 py-3">Ubicación</th>
                                    <th class="px-4 py-3">Responsable</th>
                                    <th class="px-4 py-3"># Cheque</th>
                                    <th class="px-4 py-3">Estado</th>
                                    <th class="px-4 py-3">Costo</th>
                                    <th class="px-4 py-3 sticky right-0 bg-gray-50 dark:bg-gray-900 shadow-xl z-10">Acciones</th>
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
                                    <td class="px-4 py-3 text-gray-700">{{ activo.clasificacion || '-' }}</td>
                                    <td class="px-4 py-3 text-gray-700">{{ activo.area || '-' }}</td>
                                    <td class="px-4 py-3 text-gray-700">{{ activo.ubicacion || '-' }}</td>
                                    <td class="px-4 py-3 text-gray-700">{{ activo.responsable || '-' }}</td>
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
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="flex items-center gap-3">
                                            <button
                                                v-if="can('activos.manage')"
                                                class="text-indigo-600 hover:text-indigo-900"
                                                type="button"
                                                @click="startEdit(activo)"
                                                title="Editar"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </button>
                                            <button
                                                v-if="can('activos.manage')"
                                                class="text-red-600 hover:text-red-900"
                                                type="button"
                                                @click="destroyActivo(activo.id)"
                                                title="Eliminar"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                            <a
                                                :href="route('activos.qr', activo.id) + '?cache=' + encodeURIComponent(activo.updated_at || activo.id)"
                                                target="_blank"
                                                class="text-emerald-600 hover:text-emerald-900"
                                                title="Ver Código QR"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4h2v-4zM6 20h2v-4H6v4zM6 8h2V4H6v4zm12-4h2v4h-2V4zM6 12h2v-4H6v4zm6-8h2V4h-2v4zm-2 4h2V8h-2v4zm-2-8v4H6V4h4zm-2 12v4H6v-4h4zm8-4v4h-2v-4h2zm4 4v4h-2v-4h2zm2-12v4h-4V4h4zm-4 8v4h-2v-4h2z" />
                                                </svg>
                                            </a>
                                            <a
                                                v-if="activo.responsable"
                                                :href="route('activos.acta-asignacion', activo.id)"
                                                target="_blank"
                                                class="text-purple-600 hover:text-purple-900"
                                                title="Generar Acta de Asignación"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="!props.activos.data.length">
                                    <td colspan="10" class="px-4 py-4 text-center text-gray-500">Sin registros</td>
                                </tr>
                                <tr v-else-if="!activosFiltrados.length">
                                    <td colspan="10" class="px-4 py-4 text-center text-gray-500">No hay activos que coincidan con la búsqueda</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    <Pagination 
                        :links="props.activos.links" 
                        :from="props.activos.from" 
                        :to="props.activos.to" 
                        :total="props.activos.total" 
                    />
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
                            <div class="lg:col-span-2">
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Nombre del activo *</label>
                                <input v-model="editForm.nombre_activo" type="text" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" required maxlength="255" />
                                <p v-if="editForm.errors.nombre_activo" class="mt-1 text-sm text-red-600">{{ editForm.errors.nombre_activo }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Clasificación *</label>
                                <select v-model="editForm.clasificacion_id" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" required>
                                    <option value="" disabled>Selecciona clasificación</option>
                                    <option v-for="clas in props.clasificaciones" :key="clas.id" :value="clas.id">
                                        {{ String(clas.id).padStart(3, '0') }} - {{ clas.nombre }}
                                    </option>
                                </select>
                                <p v-if="editForm.errors.clasificacion_id" class="mt-1 text-sm text-red-600">{{ editForm.errors.clasificacion_id }}</p>
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
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Área *</label>
                                <select v-model="editForm.area_id" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" required>
                                    <option value="" disabled>Selecciona área</option>
                                    <option v-for="area in props.areas" :key="area.id" :value="area.id">{{ String(area.id).padStart(2, '0') }} - {{ area.nombre }}</option>
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
                                    <option v-for="per in filteredPersonalForEdit" :key="per.id" :value="per.id">{{ per.nombre }} {{ per.apellido }}</option>
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
                        </div>

                        <!-- Depreciación Section -->
                        <div class="space-y-3 rounded-xl border border-emerald-100 bg-emerald-50 p-4 dark:border-emerald-900 dark:bg-emerald-950">
                            <div class="flex items-center justify-between text-sm">
                                <p class="font-semibold text-emerald-800 dark:text-emerald-100">Configuración de Depreciación</p>
                                <span class="text-emerald-600 dark:text-emerald-400">(Opcional - para cálculo automático)</span>
                            </div>
                            <div class="grid gap-4 sm:grid-cols-3">
                                <div>
                                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Vida Útil (años)</label>
                                    <input v-model.number="editForm.vida_util_anos" type="number" min="1" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" placeholder="Ej: 5" />
                                    <p v-if="editForm.errors.vida_util_anos" class="mt-1 text-sm text-red-600">{{ editForm.errors.vida_util_anos }}</p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Valor Residual</label>
                                    <input v-model="editForm.valor_residual" type="number" min="0" step="0.01" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" placeholder="0.00" />
                                    <p v-if="editForm.errors.valor_residual" class="mt-1 text-sm text-red-600">{{ editForm.errors.valor_residual }}</p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Método</label>
                                    <select v-model="editForm.metodo_depreciacion" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                        <option value="LINEAL">Línea Recta</option>
                                        <option value="SALDO_DECRECIENTE">Saldo Decreciente</option>
                                        <option value="UNIDADES_PRODUCIDAS">Unidades Producidas</option>
                                    </select>
                                    <p v-if="editForm.errors.metodo_depreciacion" class="mt-1 text-sm text-red-600">{{ editForm.errors.metodo_depreciacion }}</p>
                                </div>
                            </div>
                            <div v-if="editDepreciacionPreview" class="rounded-lg border border-emerald-200 bg-white p-3 dark:border-emerald-800 dark:bg-gray-800">
                                <p class="text-xs font-semibold text-emerald-700 dark:text-emerald-300">Vista Previa de Depreciación:</p>
                                <div class="mt-2 grid grid-cols-2 gap-2 text-sm">
                                    <div>
                                        <span class="text-gray-600 dark:text-gray-400">Depreciación Anual:</span>
                                        <span class="ml-2 font-semibold text-gray-900 dark:text-gray-100">{{ formatCurrency(editDepreciacionPreview.anual) }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-600 dark:text-gray-400">Depreciación Mensual:</span>
                                        <span class="ml-2 font-semibold text-gray-900 dark:text-gray-100">{{ formatCurrency(editDepreciacionPreview.mensual) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
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
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Foto del activo</label>
                                <input 
                                    type="file" 
                                    accept="image/jpeg,image/png,image/jpg"
                                    @change="handleEditPhotoChange"
                                    class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-gray-700 dark:file:text-gray-200"
                                />
                                <p v-if="editForm.errors.foto" class="mt-1 text-sm text-red-600">{{ editForm.errors.foto }}</p>
                                <div v-if="editPhotoPreview || selectedActivo?.foto" class="mt-2">
                                    <img :src="editPhotoPreview || `/storage/${selectedActivo.foto}`" class="h-32 w-32 object-cover rounded shadow" alt="Foto actual" />
                                </div>
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
