<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';

const groupEl = ref(null);
let resizeObserver = null;

function checkWrapping() {
    const el = groupEl.value;
    if (!el) return;

    el.removeAttribute('data-wrapped');

    const firstChild = el.firstElementChild;
    const lastChild = el.lastElementChild;

    if (firstChild && lastChild && firstChild !== lastChild && lastChild.offsetTop > firstChild.offsetTop) {
        el.setAttribute('data-wrapped', '');
    }
}

onMounted(() => {
    resizeObserver = new ResizeObserver(checkWrapping);
    if (groupEl.value) {
        resizeObserver.observe(groupEl.value);
    }
});

onBeforeUnmount(() => {
    resizeObserver?.disconnect();
});
</script>

<template>
    <div
        ref="groupEl"
        :class="[
            'group/button inline-flex flex-wrap [[data-floating-toolbar]_&]:justify-center',
            '[&>[data-ui-group-target]:not(:first-child):not(:last-child)]:rounded-none',
            '[&>[data-ui-group-target]:first-child:not(:last-child)]:rounded-e-none',
            '[&>[data-ui-group-target]:last-child:not(:first-child)]:rounded-s-none',
            '[&>*:not(:first-child):not(:last-child):not(:only-child)_[data-ui-group-target]]:rounded-none',
            '[&>*:first-child:not(:last-child)_[data-ui-group-target]]:rounded-e-none',
            '[&>*:last-child:not(:first-child)_[data-ui-group-target]]:rounded-s-none',
            'dark:[&_button]:ring-0',
            'shadow-ui-sm rounded-lg'
        ]"
        data-ui-button-group
    >
        <slot />
    </div>
</template>

<style>
    /* GROUP BUTTON GROUP BORDERS
    =================================================== */
    [data-ui-button-group] [data-ui-group-target] {
        @apply shadow-none;

        &:not(:first-child):not([data-floating-toolbar] &) {
            border-inline-start: 0;
        }
    }
    /* GROUP BUTTON GROUP BORDERS / FLOATING TOOLBAR
    =================================================== */
    [data-floating-toolbar] [data-ui-button-group] {
        /* Connected look when not wrapped */
        &:not([data-wrapped]) [data-ui-group-target]:not(:first-child) {
            border-inline-start: 0;
        }

        /* Split buttons apart when wrapped */
        &[data-wrapped] {
            gap: 0.25rem;
            box-shadow: none;

            button {
                border-radius: 0.375rem !important;
            }
        }
    }
</style>
