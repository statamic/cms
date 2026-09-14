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
    /* When 'stack', switch to vertical layout when overflowing. When 'gap', switch to normal buttons with gaps when overflowing. */
    overflow: {
        type: String,
        default: null,
        validator: (v) => [null, 'stack', 'gap'].includes(v),
    },
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
const needsOverflowObserver = computed(() => props.overflow === 'stack' || props.overflow === 'gap');
const measuringOverflow = ref(false);

const groupClasses = computed(() => {
    const groupShadow = 'rounded-lg shadow-ui-sm [&_[data-ui-group-target]]:shadow-none';

    const collapseHorizontally = [
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
        '[&>[data-ui-group-target]:not(:first-child):not(:last-child)]:rounded-none',
        '[&>:not(:first-child):not(:last-child)_[data-ui-group-target]]:rounded-none',
        '[&>[data-ui-group-target]:first-child:not(:last-child)]:rounded-b-none',
        '[&>:first-child:not(:last-child)_[data-ui-group-target]]:rounded-b-none',
        '[&>[data-ui-group-target]:last-child:not(:first-child)]:rounded-t-none',
        '[&>:last-child:not(:first-child)_[data-ui-group-target]]:rounded-t-none',
        '[&>[data-ui-group-target]:not(:last-child)]:border-b-0',
        '[&>:not(:last-child)_[data-ui-group-target]]:border-b-0',
    ];

    return cva({
        base: [
            'group/button inline-flex flex-wrap relative',
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
            { overflow: 'stack', hasOverflow: false, class: [...collapseHorizontally, groupShadow] },
            { overflow: 'stack', hasOverflow: true, class: [...collapseVertically, groupShadow] },
            { overflow: 'gap', hasOverflow: true, class: 'gap-1' },
            { overflow: 'gap', hasOverflow: false, class: [...collapseHorizontally, groupShadow] },
            { overflow: null, orientation: 'horizontal', gap: false, class: [...collapseHorizontally, groupShadow] },
        ],
    })({
        gap: props.gap,
        justify: props.justify,
        orientation: props.orientation,
        overflow: props.overflow,
        hasOverflow: hasOverflow.value,
    });
});

const wrapper = ref(null);
const group = ref(null);
let resizeObserver = null;

async function checkOverflow() {
    if (!group.value?.children.length) return;

    // Measure in collapsed state to avoid hysteresis from gap spacing
    hasOverflow.value = false;
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
    if (needsOverflowObserver.value) {
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
        @apply flex! flex-row!;
    }
</style>
