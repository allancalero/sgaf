<script setup>
import { onMounted, ref, computed } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue'; // We might want to customize these for the modern look later
import { Link, usePage } from '@inertiajs/vue3';

// State
const sidebarOpen = ref(true); // Default open on desktop
const showingNavigationDropdown = ref(false); // Mobile menu state
const page = usePage();
const theme = ref('light');

// System Info
const system = page.props.system || {};
const displayName = system.nombre_alcaldia || 'SGAF';
const displayLogo = system.logo_url || null;

// Permissions Helper
const can = (permission) => page.props.auth.user?.permissions?.includes(permission);
const canAny = (permissions = []) => permissions.some((permission) => can(permission));

// Theme Management
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
    
    // Auto-close sidebar on mobile
    if (window.innerWidth < 1024) {
        sidebarOpen.value = false;
    }
});

// Navigation Items Structure for Cleaner Template
const navigationGroups = computed(() => [
    {
        title: 'General',
        show: true,
        items: [
            { label: 'Dashboard', route: 'dashboard', icon: 'M3 13h8V5H3v8zm0 6h8v-4H3v4zm10 0h8V11h-8v8zm0-12h8V5h-8v2z' }
        ]
    },
    {
        title: 'Gestionar Catálogos',
        show: can('catalogos.manage'),
        items: [
            { label: 'Recursos Humanos', route: 'recursos-humanos.index', icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z', color: 'text-purple-500' }
        ]
    },
    {
        title: 'Gestionar Activos Fijo',
        show: can('activos.view') || can('catalogos.manage'),
        items: [
            { label: 'Activos', route: 'activos.index', show: can('activos.view'), active: ['activos.index', 'activos.resumen'], icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', color: 'text-emerald-500' },
            { label: 'Catálogos de Activos', route: 'activos-fijo-vista.index', show: can('catalogos.manage'), icon: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4', color: 'text-amber-500' },
            { label: 'Reasignación de Activos', route: 'reasignaciones.index', show: can('activos.manage'), active: ['reasignaciones.*'], icon: 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4', color: 'text-blue-500' },
            { label: 'Reportes', route: 'activos.reportes', show: true, icon: 'M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z', color: 'text-rose-500' }
        ]
    },
    {
        title: 'Gestionar Sistema',
        show: canAny(['sistema.manage', 'respaldos.download', 'seguridad.manage']),
        items: [
            { label: 'Respaldo', route: 'sistema.respaldo', show: can('respaldos.download'), icon: 'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12', color: 'text-green-500' },
            { label: 'Parámetros', route: 'sistema.index', show: can('sistema.manage'), icon: 'M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4', color: 'text-indigo-500' },
            { label: 'Usuarios', route: 'usuarios.index', show: can('sistema.manage'), icon: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z', color: 'text-cyan-500' },
            { label: 'Seguridad', route: 'sistema.seguridad', show: can('seguridad.manage'), icon: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z', color: 'text-red-500' },
            { label: 'Auditoría', route: 'sistema.auditoria', show: can('sistema.manage'), icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', color: 'text-orange-500' }
        ]
    }
]);

const isActive = (item) => {
    if (item.active) {
        return item.active.some(r => route().current(r));
    }
    return route().current(item.route);
};
</script>

<template>
    <div class="min-h-screen bg-gray-50 text-gray-900 font-sans dark:bg-slate-900 dark:text-gray-100 transition-colors duration-300">
        
        <!-- Background Decor -->
        <div class="fixed inset-0 z-0 pointer-events-none">
            <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-indigo-500/10 rounded-full blur-[100px] -translate-y-1/2 translate-x-1/2 dark:bg-indigo-500/20"></div>
            <div class="absolute bottom-0 left-0 w-[500px] h-[500px] bg-blue-500/10 rounded-full blur-[100px] translate-y-1/2 -translate-x-1/2 dark:bg-blue-500/20"></div>
        </div>

        <!-- Sidebar -->
        <aside 
            class="fixed top-0 left-0 z-40 h-screen transition-all duration-300 ease-in-out border-r border-gray-200/50 bg-white/80 backdrop-blur-xl dark:bg-slate-900/80 dark:border-white/10"
            :class="[sidebarOpen ? 'w-72' : 'w-20']"
        >
            <!-- Logo Area -->
            <div class="flex h-20 items-center justify-between px-6">
                <Link :href="route('dashboard')" class="flex items-center gap-3 overflow-hidden">
                     <ApplicationLogo v-if="!displayLogo" class="h-8 w-8 shrink-0 fill-current text-indigo-600 dark:text-indigo-400" />
                     <img v-else :src="displayLogo" alt="Logo" class="h-8 w-auto shrink-0" />
                     
                     <span 
                        class="text-lg font-bold tracking-tight text-gray-800 transition-opacity duration-300 whitespace-nowrap dark:text-white"
                        :class="[sidebarOpen ? 'opacity-100' : 'opacity-0 w-0']"
                    >
                        {{ displayName }}
                     </span>
                </Link>
            </div>

            <!-- Navigation -->
            <nav class="h-[calc(100vh-80px)] overflow-y-auto px-4 pb-4 space-y-6 scrollbar-hide">
                <template v-for="(group, idx) in navigationGroups" :key="idx">
                    <div v-if="group.show">
                        <p 
                            class="px-2 mb-2 text-xs font-semibold uppercase tracking-wider text-gray-400 transition-opacity duration-300 dark:text-gray-500"
                            :class="[sidebarOpen ? 'opacity-100' : 'opacity-0 text-center']"
                        >
                            {{ sidebarOpen ? group.title : '•' }}
                        </p>
                        
                        <ul class="space-y-1">
                            <li v-for="item in group.items" :key="item.route">
                                <Link 
                                    v-if="item.show !== false"
                                    :href="route(item.route)" 
                                    class="group relative flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-all duration-200 hover:bg-gray-100 dark:hover:bg-white/5"
                                    :class="{ 
                                        'bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400': isActive(item),
                                        'text-gray-600 dark:text-gray-300': !isActive(item)
                                    }"
                                >
                                    <div class="relative flex items-center justify-center">
                                         <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transition-colors" 
                                            :class="[
                                                isActive(item) ? 'text-indigo-600 dark:text-indigo-400' : (item.color || 'text-gray-400 group-hover:text-gray-600 dark:text-gray-500 dark:group-hover:text-gray-300')
                                            ]"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                            <path stroke-linecap="round" stroke-linejoin="round" :d="item.icon" />
                                        </svg>
                                        <span v-if="isActive(item)" class="absolute -left-[14px] top-1/2 block h-8 w-1 -translate-y-1/2 rounded-r-full bg-indigo-600 dark:bg-indigo-400"></span>
                                    </div>

                                    <span 
                                        class="whitespace-nowrap transition-all duration-300"
                                        :class="[sidebarOpen ? 'opacity-100 translate-x-0' : 'opacity-0 -translate-x-4 absolute pointer-events-none']"
                                    >
                                        {{ item.label }}
                                    </span>

                                    <!-- Tooltip for collapsed state -->
                                    <div 
                                        v-if="!sidebarOpen"
                                        class="absolute left-full ml-4 hidden rounded-md bg-gray-900 px-2 py-1 text-xs text-white opacity-0 transition-opacity group-hover:opacity-100 group-hover:block z-50 whitespace-nowrap"
                                    >
                                        {{ item.label }}
                                    </div>
                                </Link>
                            </li>
                        </ul>
                    </div>
                </template>
            </nav>
        </aside>

        <!-- Main Content Wrapper -->
        <div 
            class="min-h-screen transition-all duration-300"
            :class="[sidebarOpen ? 'lg:ml-72' : 'lg:ml-20']"
        >
            <!-- Top Navbar -->
            <header class="sticky top-0 z-30 mb-6 flex h-20 items-center justify-between bg-white/70 px-6 backdrop-blur-md transition-all dark:bg-slate-900/60 border-b border-gray-100/50 dark:border-white/5 mx-4 mt-4 rounded-2xl shadow-sm">
                
                <!-- Toggle Sidebar & Search -->
                <div class="flex items-center gap-4">
                    <button @click="sidebarOpen = !sidebarOpen" class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-white/5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
                        </svg>
                    </button>
                    <!-- Search could go here -->
                </div>

                <!-- Right Actions -->
                <div class="flex items-center gap-4">
                    <!-- Theme Toggle -->
                    <button @click="toggleTheme" class="rounded-full p-2 text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-white/5 transition-colors">
                        <svg v-if="theme === 'dark'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m6.364 1.636l-.707.707M21 12h-1M6.343 5.343l-.707.707M4 12H3m3.343 6.657l-.707-.707M12 20v1m6.364-1.636l-.707-.707M12 8a4 4 0 100 8 4 4 0 000-8z" />
                        </svg>
                        <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                    </button>
                    
                    <!-- Notification Bell (Static for now) -->
                    <button class="relative rounded-full p-2 text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-white/5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        <span class="absolute top-2 right-2 h-2 w-2 rounded-full bg-red-500 ring-2 ring-white dark:ring-gray-900"></span>
                    </button>

                    <!-- Profile Dropdown -->
                    <Dropdown align="right" width="48">
                        <template #trigger>
                            <button class="flex items-center gap-3 rounded-full border border-gray-200 bg-gray-50 pl-1 pr-3 py-1 transition hover:bg-gray-100 dark:border-white/10 dark:bg-white/5 dark:hover:bg-white/10">
                                <div class="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-sm">
                                    {{ ($page.props.auth.user?.nombre?.[0] || 'U') }}
                                </div>
                                <div class="hidden md:block text-left">
                                    <div class="text-xs font-semibold text-gray-700 dark:text-gray-200">
                                        {{ $page.props.auth.user?.nombre || 'Usuario' }}
                                    </div>
                                    <div class="text-[10px] text-gray-500 dark:text-gray-400 leading-none">
                                        {{ $page.props.auth.user?.email }}
                                    </div>
                                </div>
                                <svg class="h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </template>

                        <template #content>
                            <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700">
                                <span class="block text-sm text-gray-900 dark:text-white">{{ $page.props.auth.user?.full_name }}</span>
                                <span class="block text-sm font-medium text-gray-500 truncate dark:text-gray-400">{{ $page.props.auth.user?.email }}</span>
                            </div>
                            <DropdownLink :href="route('profile.edit')">
                                Perfil
                            </DropdownLink>
                            <DropdownLink :href="route('logout')" method="post" as="button">
                                Cerrar sesión
                            </DropdownLink>
                        </template>
                    </Dropdown>
                </div>
            </header>

            <!-- Page Heading (Optional) -->
            <header v-if="$slots.header" class="mb-8 px-8">
                <h1 class="text-2xl font-bold tracking-tight text-gray-800 dark:text-white">
                    <slot name="header" />
                </h1>
            </header>

            <!-- Page Content -->
            <main class="px-4 pb-12 sm:px-6 lg:px-8">
                <slot />
            </main>
        </div>
    </div>
</template>

<style scoped>
/* Custom Scrollbar for Sidebar */
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
.scrollbar-hide {
    -ms-overflow-style: none; /* IE and Edge */
    scrollbar-width: none; /* Firefox */
}
</style>
