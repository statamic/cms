<template>
    <div ref="wrapper">
        <div ref="group" :class="groupClasses" data-ui-button-group>
            <slot />
        </div>
    </div>
</template>

<script setup>
import { ref, nextTick, onMounted, onBeforeUnmount } from 'vue';
import { cva } from 'cva'

import debounce from '@/util/debounce';

const props = defineProps({
    orientation: {
        type: String,
        default: 'horizontal',
    },
    gap: {
        type: [String, Boolean],
        default: false,
    },
    justify: {
        type: String,
        default: 'start',
    },
});

const hasOverflow = ref(false);

const groupClasses = computed(() => cva({
    base: [
        'group/button flex flex-wrap relative',
        'dark:[&_button]:ring-0',
    ],
    variants: {
        orientation: {
            vertical: 'flex-col',
        },
        justify: {
            center: 'justify-center',
        },
        gap: {
            false: 'rounded-lg shadow-ui-sm [&_[data-ui-group-target]]:shadow-none',
            true: 'gap-1',
        },
    },
    compoundVariants: [
        { orientation: 'auto', hasOverflow: true, class: 'flex-col' },
        { gap: 'auto', hasOverflow: false, class: 'rounded-lg shadow-ui-sm [&_[data-ui-group-target]]:shadow-none' },
        { gap: 'auto', hasOverflow: true, class: [
            '[>[data-ui-group-target]:not(:first-child):not(:last-child)]:rounded-none',
            '[>:not(:first-child):not(:last-child)_[data-ui-group-target]]:rounded-none',
        ] },
    ],
})({
    gap: props.gap,
    justify: props.justify,
    orientation: props.orientation,
    hasOverflow: hasOverflow.value,
}))

const wrapper = ref(null);
const group = ref(null);
let resizeObserver = null;

function checkOverflow() {
    // Allow natural layout (grow + wrap)
    // ???

    // Force reflow and measure
    const child = group.value.lastElementChild;
    hasOverflow.value = child && child.offsetTop > group.value.clientTop;
}

onMounted(() => {
    if (props.orientation === 'auto' || props.gap === 'auto') {
        resizeObserver = new ResizeObserver(debounce(checkOverflow, 100));
        resizeObserver.observe(wrapper.value);
    }
});

onBeforeUnmount(() => {
    resizeObserver?.disconnect();
});
</script>

<style>
    [data-ui-button-group][data-orientation='horizontal'] [data-ui-group-target]:not(:first-child) {
        border-inline-start: 0;
    }

    [data-ui-button-group][data-orientation='vertical'] [data-ui-group-target]:not(:first-child) {
        border-block-start: 0;
    }

    /* Floating toolbar on small screens: items are split apart, keep borders */
    @media (width < 1024px) {
        [data-floating-toolbar] [data-ui-button-group] [data-ui-group-target]:not(:first-child) {
            border-inline-start: 1px;
        }
    }
</style>
