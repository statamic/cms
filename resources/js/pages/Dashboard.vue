<script setup>
import Head from '@/pages/layout/Head.vue';
import DynamicHtmlRenderer from '@/components/DynamicHtmlRenderer.vue';

defineProps(['widgets'])

function classes(widget) {
    return `${widget.classes} ${tailwindWidthClass(widget.width)}`;
}

function tailwindWidthClass(width) {
    const sizes = {
        sm: 'w-full @2xl:w-1/2 @4xl:w-1/3 @8xl:w-1/4',
        md: 'w-full @2xl:w-1/2 @4xl:w-1/2 @8xl:w-1/3',
        lg: 'w-full @2xl:w-full @4xl:w-2/3 @8xl:w-3/4',
        full: 'w-full',
    };

    // For backward compatibility, map old numeric widths to new sizes
    const legacyMap = {
        25: 'sm',
        33: 'sm',
        50: 'md',
        66: 'md',
        75: 'lg',
        100: 'full'
    };

    const size = typeof width === 'number' ? (legacyMap[width] ?? 'full') : width;

    return sizes[size] ?? sizes.md;
}
</script>

<template>
    <Head :title="__('Dashboard')" />

    <ui-header :title="__('Dashboard')" icon="dashboard" />

    <div class="widgets @container/widgets flex flex-wrap gap-y-6 -mx-2 sm:-mx-3">
        <div
            v-for="widget in widgets"
            class="min-h-54 px-3 starting-style-transition starting-style-transition--siblings"
            :class="classes(widget)"
        >
            <DynamicHtmlRenderer :html="widget.html" />
        </div>
    </div>

    <ui-docs-callout :topic="__('Widgets')" url="widgets" />
</template>
