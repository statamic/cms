<template>
    <div
        ref="el"
        :class="[
            'group/button flex flex-wrap relative [[data-floating-toolbar]_&]:justify-center [[data-floating-toolbar]_&]:gap-1 [[data-floating-toolbar]_&]:lg:gap-x-0',
            'dark:[&_button]:ring-0',
            'max-lg:[[data-floating-toolbar]_&_button]:rounded-md!',
            'shadow-ui-sm rounded-lg',

            // Horizontal orientation
            '[&[data-orientation=horizontal]>[data-ui-group-target]:not(:first-child):not(:last-child)]:rounded-none',
            '[&[data-orientation=horizontal]>[data-ui-group-target]:first-child:not(:last-child)]:rounded-e-none',
            '[&[data-orientation=horizontal]>[data-ui-group-target]:last-child:not(:first-child)]:rounded-s-none',
            '[&[data-orientation=horizontal]>*:not(:first-child):not(:last-child):not(:only-child)_[data-ui-group-target]]:rounded-none',
            '[&[data-orientation=horizontal]>*:first-child:not(:last-child)_[data-ui-group-target]]:rounded-e-none',
            '[&[data-orientation=horizontal]>*:last-child:not(:first-child)_[data-ui-group-target]]:rounded-s-none',

            // Vertical orientation
            '[&[data-orientation=vertical]]:flex-col',
            '[&[data-orientation=vertical]>[data-ui-group-target]:not(:first-child):not(:last-child)]:rounded-none',
            '[&[data-orientation=vertical]>[data-ui-group-target]:first-child:not(:last-child)]:rounded-b-none',
            '[&[data-orientation=vertical]>[data-ui-group-target]:last-child:not(:first-child)]:rounded-t-none',
            '[&[data-orientation=vertical]>*:not(:first-child):not(:last-child):not(:only-child)_[data-ui-group-target]]:rounded-none',
            '[&[data-orientation=vertical]>*:first-child:not(:last-child)_[data-ui-group-target]]:rounded-b-none',
            '[&[data-orientation=vertical]>*:last-child:not(:first-child)_[data-ui-group-target]]:rounded-t-none',
        ]"
        data-ui-button-group
    >
        <slot />
    </div>
</template>

<script setup>
import { ref, nextTick, onMounted, onBeforeUnmount } from 'vue';

const props = defineProps({
    orientation: {
        type: String,
        default: 'horizontal',
    },
});

const el = ref(null);
let resizeObserver = null;

function observeOrientation() {
    resizeObserver = new ResizeObserver(() => checkOrientation());
    resizeObserver.observe(el.value);
}

function unobserveOrientation() {
    resizeObserver?.disconnect();
}

async function checkOrientation() {
    const node = el.value;
    if (!node) return;

    // 1) Remove orientation to allow natural layout (grow + wrap)
    delete node.dataset.orientation;

    // 2) Wait for Vue to commit DOM updates
    await nextTick();

    // 3) Force reflow and measure
    const child = node.lastElementChild;
    const orientation = child && child.offsetTop > node.clientTop
        ? 'vertical'
        : 'horizontal';

    // 4) Apply the final orientation
    node.dataset.orientation = orientation;
}

onMounted(() => {
    el.value.dataset.orientation = props.orientation;
    if (props.orientation === 'auto') {
        observeOrientation();
    }
});

onBeforeUnmount(() => {
    unobserveOrientation();
});
</script>

<style>
    [data-ui-button-group] [data-ui-group-target] {
        @apply shadow-none;
    }

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
