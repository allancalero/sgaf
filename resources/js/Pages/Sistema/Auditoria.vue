<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, Link } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

// Let's implement simple pagination links inside this file to avoid dependency issues for now, or check for it later.
// Actually, looking at Usuarios/Index.vue, it didn't use Pagination component, it just showed "All items" or filtered list.
// But AuditController uses paginate(20). So I need pagination links.

const props = defineProps({
    audits: Object,
    filters: Object,
    users: Array,
});

const form = ref({
    user_id: props.filters.user_id || '',
    event: props.filters.event || '',
    date_from: props.filters.date_from || '',
    date_to: props.filters.date_to || '',
});

const search = () => {
    router.get(route('audits.index'), form.value, { preserveState: true, preserveScroll: true });
};

const reset = () => {
    form.value = { user_id: '', event: '', date_from: '', date_to: '' };
    search();
};

watch(form, () => {
    // Optional: debounce search? For now manual button or enter is fine.
}, { deep: true });

const formatDate = (dateString) => {
    if (!dateString) return '';
    return new Date(dateString).toLocaleString();
};
</script>

<template>
    <Head title="Auditoría del Sistema" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Auditoría del Sistema</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <!-- Filters -->
                <div class="bg-white p-4 rounded-lg shadow space-y-4 md:space-y-0 md:flex md:gap-4 items-end">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700">Usuario</label>
                        <select v-model="form.user_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="">Todos</option>
                            <option v-for="user in users" :key="user.id" :value="user.id">{{ user.full_name || user.nombre + ' ' + user.apellido }}</option>
                        </select>
                    </div>
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700">Evento</label>
                        <input v-model="form.event" type="text" placeholder="Ej: login, created" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Desde</label>
                        <input v-model="form.date_from" type="date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                     <div>
                        <label class="block text-sm font-medium text-gray-700">Hasta</label>
                        <input v-model="form.date_to" type="date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                    <div class="flex gap-2">
                        <button @click="search" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 text-sm">Filtrar</button>
                        <button @click="reset" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300 text-sm">Limpiar</button>
                    </div>
                </div>

                <!-- Table -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha/Hora</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Evento</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP / Agente</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Detalles</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="audit in audits.data" :key="audit.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ formatDate(audit.created_at) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ audit.user?.full_name || (audit.user ? audit.user.nombre + ' ' + audit.user.apellido : 'Sistema/Desconocido') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" 
                                            :class="{
                                                'bg-green-100 text-green-800': audit.event === 'login',
                                                'bg-blue-100 text-blue-800': audit.event !== 'login'
                                            }">
                                            {{ audit.event }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">
                                        <div>{{ audit.ip_address }}</div>
                                        <div class="text-xs text-gray-400 truncate" :title="audit.user_agent">{{ audit.user_agent }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        <div v-if="audit.url" class="text-xs text-gray-400 mb-1">{{ audit.url }}</div>
                                        <div v-if="audit.new_values" class="truncate max-w-xs" :title="JSON.stringify(audit.new_values)">
                                            {{ JSON.stringify(audit.new_values).substring(0, 50) }}...
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="audits.data.length === 0">
                                    <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">No se encontraron registros.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div v-if="audits.links.length > 3" class="px-6 py-4 border-t border-gray-200">
                        <div class="flex flex-wrap gap-1 justify-center">
                            <Link v-for="(link, k) in audits.links" 
                                :key="k" 
                                :href="link.url || '#'" 
                                v-html="link.label"
                                class="px-3 py-1 border rounded text-sm"
                                :class="{ 'bg-indigo-600 text-white': link.active, 'bg-white text-gray-700': !link.active, 'opacity-50 cursor-not-allowed': !link.url }"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
