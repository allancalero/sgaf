<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: {
        type: String,
        required: true,
    },
    active: {
        type: Boolean,
    },
});

const classes = computed(() =>
    props.active
        ? 'flex w-full items-center gap-2 rounded-md border border-indigo-100 bg-indigo-50 px-3 py-2 text-sm font-semibold text-indigo-700 shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2'
        : 'flex w-full items-center gap-2 rounded-md border border-transparent px-3 py-2 text-sm font-medium text-gray-700 transition duration-150 ease-in-out hover:border-gray-200 hover:bg-gray-50 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2',
);
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>
