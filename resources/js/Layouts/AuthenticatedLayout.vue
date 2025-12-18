<script setup>
import { onMounted, ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import SessionTimeout from '@/Components/SessionTimeout.vue';
import { Link, usePage } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);
const page = usePage();
const theme = ref('light');
const system = page.props.system || {};
const displayName = system.nombre_alcaldia || 'SGAF';
const displayLogo = system.logo_url || null;

const can = (permission) => page.props.auth.user?.permissions?.includes(permission);
const canAny = (permissions = []) => permissions.some((permission) => can(permission));

const applyTheme = (value) => {
    const root = document.documentElement;
    if (value === 'dark') {
        root.classList.add('dark');
        root.classList.remove('light');
    } else {
        root.classList.remove('dark');
        root.classList.add('light');
    }
    localStorage.setItem('theme', value);
    theme.value = value;
};

const toggleTheme = () => {
    applyTheme(theme.value === 'dark' ? 'light' : 'dark');
};

onMounted(() => {
    const stored = localStorage.getItem('theme');
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    applyTheme(stored || (prefersDark ? 'dark' : 'light'));
});
</script>

<template>
    <!-- Session Timeout Component -->
    <SessionTimeout />
    
    <div class="min-h-screen bg-gray-100 text-gray-900 transition-colors dark:bg-gray-950 dark:text-gray-100">
        <div class="flex">
            <!-- Sidebar -->
            <aside class="hidden w-64 shrink-0 border-r border-gray-200 bg-white/90 backdrop-blur transition-colors dark:border-gray-800 dark:bg-gray-900/70 sm:flex sm:flex-col">
                <div class="flex h-16 items-center gap-3 border-b border-gray-100 px-6 transition-colors dark:border-gray-800">
                    <Link :href="route('dashboard')" class="flex items-center gap-2">
                        <ApplicationLogo v-if="!displayLogo" class="h-9 w-auto fill-current text-indigo-500 dark:text-indigo-300" />
                        <img v-else :src="displayLogo" alt="Logo" class="h-9 w-auto" />
                        <span class="text-sm font-semibold text-gray-800 dark:text-gray-100">SGAF</span>
                    </Link>
                </div>

                <nav class="flex-1 overflow-y-auto px-3 py-4 text-sm text-gray-700 transition-colors dark:text-gray-200">
                    <p class="px-3 text-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500">General</p>
                    <div class="mt-2 space-y-1">
                        <NavLink :href="route('dashboard')" :active="route().current('dashboard')">
                            <span class="inline-flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-indigo-600 dark:text-indigo-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13h8V5H3v8zm0 6h8v-4H3v4zm10 0h8V11h-8v8zm0-12h8V5h-8v2z" />
                                </svg>
                                <span>Dashboard</span>
                            </span>
                        </NavLink>
                    </div>

                    <template v-if="can('catalogos.manage')">
                        <!-- Sección: Gestionar Catálogos -->
                        <p class="mt-6 px-3 text-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500">Gestionar Catálogos</p>
                        <div class="mt-2 space-y-1">
                            <NavLink :href="route('ubicaciones-vista.index')" :active="route().current('ubicaciones-vista.index')">
                                <span class="inline-flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-teal-600 dark:text-teal-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <span>Ubicación</span>
                                </span>
                            </NavLink>
                            <NavLink :href="route('recursos-humanos.index')" :active="route().current('recursos-humanos.index')">
                                <span class="inline-flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-purple-600 dark:text-purple-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    <span>Recursos Humanos</span>
                                </span>
                            </NavLink>
                        </div>
                    </template>

                    <!-- Sección: Gestionar Activos Fijo -->
                    <template v-if="can('activos.view') || can('catalogos.manage')">
                        <p class="mt-6 px-3 text-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500">Gestionar Activos Fijo</p>
                        <div class="mt-2 space-y-1">
                            <NavLink v-if="can('activos.view')" :href="route('activos.index')" :active="route().current('activos.index') || route().current('activos.resumen')">
                                <span class="inline-flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-emerald-600 dark:text-emerald-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                    <span>Activos</span>
                                </span>
                            </NavLink>
                            <NavLink v-if="can('activos.view')" :href="route('activos.busqueda')" :active="route().current('activos.busqueda')">
                                <span class="inline-flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-indigo-600 dark:text-indigo-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                    <span>Búsqueda Rápida</span>
                                </span>
                            </NavLink>
                            <NavLink v-if="can('catalogos.manage')" :href="route('activos-fijo-vista.index')" :active="route().current('activos-fijo-vista.index')">
                                <span class="inline-flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-amber-600 dark:text-amber-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                    <span>Catálogos de Activos</span>
                                </span>
                            </NavLink>
                            <NavLink v-if="can('activos.manage')" :href="route('reasignaciones.index')" :active="route().current('reasignaciones.*')">
                                <span class="inline-flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-600 dark:text-blue-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                    </svg>
                                    <span>Reasignación de Activos</span>
                                </span>
                            </NavLink>
                            <NavLink :href="route('activos.reportes')" :active="route().current('activos.reportes')">
                                <span class="inline-flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-rose-600 dark:text-rose-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
                                    </svg>
                                    <span>Reportes</span>
                                </span>
                            </NavLink>
                            <NavLink :href="route('activos.depreciacion')" :active="route().current('activos.depreciacion')">
                                <span class="inline-flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-emerald-600 dark:text-emerald-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                    </svg>
                                    <span>Depreciación</span>
                                </span>
                            </NavLink>
                        </div>
                    </template>

                    <!-- Sección: Gestionar Sistema -->
                    <template v-if="canAny(['sistema.manage', 'respaldos.download', 'seguridad.manage'])">
                        <p class="mt-6 px-3 text-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500">Gestionar Sistema</p>
                        <div class="mt-2 space-y-1">
                            <NavLink
                                v-if="can('respaldos.download')"
                                :href="route('sistema.respaldo')"
                                :active="route().current('sistema.respaldo')"
                            >
                                <span class="inline-flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-600 dark:text-green-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                    </svg>
                                    <span>Respaldo</span>
                                </span>
                            </NavLink>
                            <NavLink
                                v-if="can('sistema.manage')"
                                :href="route('sistema.index')"
                                :active="route().current('sistema.index')"
                            >
                                <span class="inline-flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-indigo-600 dark:text-indigo-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                                    </svg>
                                    <span>Parámetros</span>
                                </span>
                            </NavLink>
                            <NavLink
                                v-if="can('sistema.manage')"
                                :href="route('usuarios.index')"
                                :active="route().current('usuarios.index')"
                            >
                                <span class="inline-flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-cyan-600 dark:text-cyan-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                    <span>Usuarios</span>
                                </span>
                            </NavLink>
                            <NavLink
                                v-if="can('seguridad.manage')"
                                :href="route('sistema.seguridad')"
                                :active="route().current('sistema.seguridad')"
                            >
                                <span class="inline-flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-red-600 dark:text-red-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                    <span>Seguridad</span>
                                </span>
                            </NavLink>
                            <NavLink
                                v-if="can('sistema.manage')"
                                :href="route('sistema.auditoria')"
                                :active="route().current('sistema.auditoria')"
                            >
                                <span class="inline-flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-orange-600 dark:text-orange-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                    <span>Auditoría</span>
                                </span>
                            </NavLink>
                        </div>
                    </template>
                </nav>

                <div class="border-t border-gray-100 px-4 py-3 transition-colors dark:border-gray-800">
                    <Dropdown align="right" width="48">
                        <template #trigger>
                            <span class="inline-flex rounded-md">
                                <button
                                    type="button"
                                    class="inline-flex w-full items-center justify-between rounded-md border border-gray-200 bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-700 shadow-sm transition hover:border-indigo-300 hover:text-indigo-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:hover:border-indigo-400"
                                >
                                    {{
                                        $page.props.auth.user?.full_name ||
                                            $page.props.auth.user?.nombre ||
                                            'Usuario'
                                    }}
                                    <svg
                                        class="-me-0.5 ms-2 h-4 w-4"
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 20 20"
                                        fill="currentColor"
                                    >
                                        <path
                                            fill-rule="evenodd"
                                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                </button>
                            </span>
                        </template>

                        <template #content>
                            <DropdownLink :href="route('profile.edit')">
                                Perfil
                            </DropdownLink>
                            <DropdownLink :href="route('logout')" method="post" as="button">
                                Cerrar sesión
                            </DropdownLink>
                        </template>
                    </Dropdown>
                </div>
            </aside>

            <!-- Main content -->
            <div class="flex min-h-screen flex-1 flex-col">
                <nav class="border-b border-gray-100 bg-white transition-colors dark:border-gray-800 dark:bg-gray-900/80">
                    <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                        <div class="flex items-center gap-3">
                            <h1 class="hidden text-lg font-semibold text-indigo-600 dark:text-indigo-400 sm:block" style="font-family: 'Inter', 'Segoe UI', sans-serif; letter-spacing: 0.02em;">
                                {{ displayName }}
                            </h1>
                            <button
                                class="inline-flex items-center justify-center rounded-md p-2 text-gray-400 transition duration-150 ease-in-out hover:bg-gray-100 hover:text-gray-500 focus:bg-gray-100 focus:text-gray-500 focus:outline-none sm:hidden"
                                @click="showingNavigationDropdown = !showingNavigationDropdown"
                            >
                                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path
                                        :class="{ hidden: showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        :class="{ hidden: !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                        <div class="flex items-center gap-3">
                            <!-- Quick Search (Restaurado) -->
                            <div class="hidden md:flex relative items-center group">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <svg class="w-4 h-4 text-gray-500 dark:text-gray-400 group-focus-within:text-indigo-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                                    </svg>
                                </div>
                                <input 
                                    @keyup.enter="$inertia.visit(route('activos.busqueda', { search: $event.target.value }))"
                                    type="text" 
                                    class="block w-full p-2 pl-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-indigo-500 dark:focus:border-indigo-500 transition-all duration-300 w-64 focus:w-80" 
                                    placeholder="Buscar activo (Enter)..." 
                                >
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                    <span class="text-xs text-gray-400 border border-gray-300 rounded px-1.5 dark:border-gray-600">Enter</span>
                                </div>
                            </div>

                            <button
                                type="button"
                                class="inline-flex items-center gap-2 rounded-md border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:border-indigo-300 hover:text-indigo-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:hover:border-indigo-400"
                                @click="toggleTheme"
                            >
                                <svg v-if="theme === 'dark'" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m6.364 1.636l-.707.707M21 12h-1M6.343 5.343l-.707.707M4 12H3m3.343 6.657l-.707-.707M12 20v1m6.364-1.636l-.707-.707M12 8a4 4 0 100 8 4 4 0 000-8z" />
                                </svg>
                                <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z" />
                                </svg>
                                <span class="hidden sm:inline">{{ theme === 'dark' ? 'Claro' : 'Oscuro' }}</span>
                            </button>
                            <Dropdown align="right" width="48">
                                <template #trigger>
                                    <span class="inline-flex rounded-md">
                                        <button
                                            type="button"
                                            class="inline-flex w-full items-center justify-between rounded-md border border-gray-200 bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-700 shadow-sm transition hover:border-indigo-300 hover:text-indigo-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:hover:border-indigo-400"
                                        >
                                            {{
                                                $page.props.auth.user?.full_name ||
                                                    $page.props.auth.user?.nombre ||
                                                    'Usuario'
                                            }}
                                            <svg
                                                class="-me-0.5 ms-2 h-4 w-4"
                                                xmlns="http://www.w3.org/2000/svg"
                                                viewBox="0 0 20 20"
                                                fill="currentColor"
                                            >
                                                <path
                                                    fill-rule="evenodd"
                                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                    clip-rule="evenodd"
                                                />
                                            </svg>
                                        </button>
                                    </span>
                                </template>

                                <template #content>
                                    <DropdownLink :href="route('profile.edit')">
                                        Perfil
                                    </DropdownLink>
                                    <DropdownLink :href="route('logout')" method="post" as="button">
                                        Cerrar sesión
                                    </DropdownLink>
                                </template>
                            </Dropdown>
                        </div>
                    </div>

                    <div :class="{ block: showingNavigationDropdown, hidden: !showingNavigationDropdown }" class="sm:hidden">
                        <div class="space-y-1 pb-3 pt-2">
                            <ResponsiveNavLink :href="route('dashboard')" :active="route().current('dashboard')">Dashboard</ResponsiveNavLink>

                            <!-- Sección: Gestionar Catálogos -->
                            <p v-if="can('catalogos.manage')" class="px-4 pt-4 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Gestionar Catálogos</p>
                            <ResponsiveNavLink
                                v-if="can('catalogos.manage')"
                                :href="route('ubicaciones-vista.index')"
                                :active="route().current('ubicaciones-vista.index')"
                            >
                                Ubicación
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                v-if="can('catalogos.manage')"
                                :href="route('recursos-humanos.index')"
                                :active="route().current('recursos-humanos.index')"
                            >
                                Recursos Humanos
                            </ResponsiveNavLink>

                            <!-- Sección: Gestionar Activos Fijo -->
                            <p v-if="can('activos.view') || can('catalogos.manage')" class="px-4 pt-4 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Gestionar Activos Fijo</p>
                            <ResponsiveNavLink
                                v-if="can('activos.view')"
                                :href="route('activos.index')"
                                :active="route().current('activos.index') || route().current('activos.resumen')"
                            >
                                Activos
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                v-if="can('catalogos.manage')"
                                :href="route('activos-fijo-vista.index')"
                                :active="route().current('activos-fijo-vista.index')"
                            >
                                Catálogos de Activos
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                v-if="can('activos.manage')"
                                :href="route('reasignaciones.index')"
                                :active="route().current('reasignaciones.*')"
                            >
                                Reasignación de Activos
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                :href="route('activos.reportes')"
                                :active="route().current('activos.reportes')"
                            >
                                Reportes
                            </ResponsiveNavLink>

                            <!-- Sección: Gestionar Sistema -->
                            <p v-if="canAny(['sistema.manage', 'respaldos.download', 'seguridad.manage'])" class="px-4 pt-4 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Gestionar Sistema</p>
                            <ResponsiveNavLink
                                v-if="can('respaldos.download')"
                                :href="route('sistema.respaldo')"
                                :active="route().current('sistema.respaldo')"
                            >
                                Respaldo
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                v-if="can('sistema.manage')"
                                :href="route('sistema.index')"
                                :active="route().current('sistema.index')"
                            >
                                Parámetros
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                v-if="can('sistema.manage')"
                                :href="route('usuarios.index')"
                                :active="route().current('usuarios.index')"
                            >
                                Usuarios
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                v-if="can('seguridad.manage')"
                                :href="route('sistema.seguridad')"
                                :active="route().current('sistema.seguridad')"
                            >
                                Seguridad
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                v-if="can('sistema.manage')"
                                :href="route('sistema.auditoria')"
                                :active="route().current('sistema.auditoria')"
                            >
                                Auditoría
                            </ResponsiveNavLink>

                        </div>
                        <div class="border-t border-gray-200 pb-1 pt-4 dark:border-gray-800">
                            <div class="px-4">
                                <div class="flex items-center gap-2">
                                    <ApplicationLogo v-if="!displayLogo" class="h-7 w-auto fill-current text-gray-800 dark:text-gray-100" />
                                    <img v-else :src="displayLogo" alt="Logo" class="h-7 w-auto" />
                                    <div>
                                        <div class="text-base font-medium text-gray-800 dark:text-gray-100">{{ displayName }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $page.props.auth.user?.full_name || $page.props.auth.user?.nombre || 'Usuario' }}</div>
                                    </div>
                                </div>
                                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $page.props.auth.user?.email }}</div>
                            </div>

                            <div class="mt-3 space-y-1">
                                <ResponsiveNavLink :href="route('profile.edit')">Perfil</ResponsiveNavLink>
                                <form method="POST" :action="route('logout')">
                                    <ResponsiveNavLink as="button">Cerrar sesión</ResponsiveNavLink>
                                </form>
                            </div>
                        </div>
                    </div>
                </nav>

                <!-- Page Heading -->
                <header v-if="$slots.header" class="border-b border-gray-200 bg-white/80 backdrop-blur transition-colors dark:border-gray-800 dark:bg-gray-900/70">
                    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                        <slot name="header" />
                    </div>
                </header>

                <!-- Page Content -->
                <main>
                    <slot />
                </main>
            </div>
        </div>
    </div>
</template>
