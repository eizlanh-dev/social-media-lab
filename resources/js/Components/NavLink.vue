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
    ? 'inline-flex items-center rounded-lg border border-blue-600 bg-blue-600 px-3 py-1.5 text-[13px] font-semibold text-white shadow-sm transition'
    : 'inline-flex items-center rounded-lg border border-transparent px-3 py-1.5 text-[13px] font-semibold text-slate-600 transition hover:border-slate-200 hover:bg-slate-50 hover:text-slate-900',
);
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>
