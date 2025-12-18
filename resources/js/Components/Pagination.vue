<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    links: {
        type: Array,
        required: true,
    },
    from: Number,
    to: Number,
    total: Number,
});
</script>

<template>
    <div v-if="links && links.length > 3" class="border-t border-gray-100 px-6 py-4">
        <div class="flex flex-col items-center justify-between gap-4 sm:flex-row">
            <div class="text-sm text-gray-500">
                Mostrando <span class="font-medium text-gray-700">{{ from }}</span> a 
                <span class="font-medium text-gray-700">{{ to }}</span> de 
                <span class="font-medium text-gray-700">{{ total }}</span> registros
            </div>
            <div class="flex flex-wrap items-center justify-center gap-1">
                <template v-for="(link, index) in links" :key="index">
                    <div
                        v-if="link.url === null"
                        class="px-3 py-1.5 text-sm rounded-md border border-gray-200 bg-gray-50 text-gray-400 cursor-not-allowed"
                        v-html="link.label"
                    />
                    <Link
                        v-else
                        :href="link.url"
                        :class="[
                            'px-3 py-1.5 text-sm border rounded-md transition-all duration-200',
                            link.active
                                ? 'bg-indigo-600 text-white border-indigo-600 font-bold shadow-sm'
                                : 'bg-white text-gray-700 border-gray-300 hover:bg-indigo-50 hover:text-indigo-600 hover:border-indigo-300'
                        ]"
                        v-html="link.label"
                        :preserve-scroll="true"
                    />
                </template>
            </div>
        </div>
    </div>
</template>
