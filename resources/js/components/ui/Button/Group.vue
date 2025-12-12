<template>
    <div ref="wrapper" :class="{ invisible: measuringOverflow }">
        <div ref="group" :class="groupClasses" :data-measuring="measuringOverflow || undefined" data-ui-button-group>
            <slot />
        </div>
    </div>
</template>

<script setup>
import { ref, computed, nextTick, onMounted, onBeforeUnmount } from 'vue';
import { cva } from 'cva';

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
const needsOverflowObserver = props.orientation === 'auto' || props.gap === 'auto';
const measuringOverflow = ref(needsOverflowObserver);

const groupClasses = computed(() => {
    const collapseHorizontally = [
        'rounded-lg shadow-ui-sm [&_[data-ui-group-target]]:shadow-none',
        '[&>[data-ui-group-target]:not(:first-child):not(:last-child)]:rounded-none',
        '[&>:not(:first-child):not(:last-child)_[data-ui-group-target]]:rounded-none',
        '[&>[data-ui-group-target]:first-child:not(:last-child)]:rounded-e-none',
        '[&>:first-child:not(:last-child)_[data-ui-group-target]]:rounded-e-none',
        '[&>[data-ui-group-target]:last-child:not(:first-child)]:rounded-s-none',
        '[&>:last-child:not(:first-child)_[data-ui-group-target]]:rounded-s-none',
        '[&>[data-ui-group-target]:not(:first-child)]:border-s-0',
        '[&>:not(:first-child)_[data-ui-group-target]]:border-s-0',
    ];

    const collapseVertically = [
        'flex-col',
        'rounded-lg shadow-ui-sm [&_[data-ui-group-target]]:shadow-none',
        '[&>[data-ui-group-target]:not(:first-child):not(:last-child)]:rounded-none',
        '[&>:not(:first-child):not(:last-child)_[data-ui-group-target]]:rounded-none',
        '[&>[data-ui-group-target]:first-child:not(:last-child)]:rounded-b-none',
        '[&>:first-child:not(:last-child)_[data-ui-group-target]]:rounded-b-none',
        '[&>[data-ui-group-target]:last-child:not(:first-child)]:rounded-t-none',
        '[&>:last-child:not(:first-child)_[data-ui-group-target]]:rounded-t-none',
        '[&>[data-ui-group-target]:not(:first-child)]:border-t-0',
        '[&>:not(:first-child)_[data-ui-group-target]]:border-t-0',
    ];

    return cva({
        base: [
            'group/button flex flex-wrap relative',
            'dark:[&_button]:ring-0',
        ],
        variants: {
            orientation: {
                vertical: collapseVertically,
            },
            justify: {
                center: 'justify-center',
            },
        },
        compoundVariants: [
            { orientation: 'auto', hasOverflow: false, class: collapseHorizontally },
            { orientation: 'auto', hasOverflow: true, class: collapseVertically },
            { orientation: 'horizontal', gap: 'auto', hasOverflow: true, class: 'gap-1' },
            { orientation: 'horizontal', gap: 'auto', hasOverflow: false, class: collapseHorizontally },
        ],
    })({
        gap: props.gap,
        justify: props.justify,
        orientation: props.orientation,
        hasOverflow: hasOverflow.value,
    });
});

const wrapper = ref(null);
const group = ref(null);
let resizeObserver = null;

async function checkOverflow() {
    if (!group.value?.children.length) return;

    // Enter measuring mode: force horizontal wrap
    measuringOverflow.value = true;
    await nextTick();

    // Check if any child has wrapped to a new line
    const children = Array.from(group.value.children);
    const firstTop = children[0].offsetTop;
    const lastTop = children[children.length - 1].offsetTop;
    hasOverflow.value = lastTop > firstTop;

    // Exit measuring mode
    measuringOverflow.value = false;
}

onMounted(() => {
    if (needsOverflowObserver) {
        checkOverflow();
        resizeObserver = new ResizeObserver(debounce(checkOverflow, 50));
        resizeObserver.observe(wrapper.value);
    }
});

onBeforeUnmount(() => {
    resizeObserver?.disconnect();
});
</script>

<style>
    /* Force horizontal wrap layout during measurement to detect overflow */
    [data-ui-button-group][data-measuring] {
        flex-direction: row !important;
        flex-wrap: wrap !important;
    }
</style>
