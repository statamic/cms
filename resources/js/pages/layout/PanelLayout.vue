<script setup>
import { computed, provide, ref, watch, onMounted, onUnmounted, useTemplateRef } from 'vue';
import useResizable from '@/composables/use-resizable.js';

const minWidth = 175;
const maxWidth = 450;
const breakpoints = { narrow: 1250, compact: 1400 };

const left = {
    ref: useTemplateRef('leftPanel'),
    active: ref(false),
    edge: 'right',
    defaults: [minWidth, 270, 320],
};

const right = {
    ref: useTemplateRef('rightPanel'),
    active: ref(false),
    edge: 'left',
    defaults: [300, 300, 320],
};

provide('leftPanelActive', left.active);
provide('rightPanelActive', right.active);

function getDefaultWidth(panel) {
    const w = window.innerWidth;
    if (w < breakpoints.narrow) return panel.defaults[0];
    if (w < breakpoints.compact) return panel.defaults[1];
    return panel.defaults[2];
}

const { makeResizable } = useResizable();

[left, right].forEach((panel) => {
    makeResizable(panel.ref, panel.active, {
        edge: panel.edge,
        minWidth,
        maxWidth,
        defaultWidth: () => getDefaultWidth(panel),
    });

    // Pre-compute the set of pixel strings we treat as "unmodified by the user"
    panel.priorDefaults = new Set(panel.defaults.map((d) => `${d}px`));
});

const leftHasContent = computed(() => (left.ref.value?.childElementCount || 0) > 0);
const rightHasContent = computed(() => (right.ref.value?.childElementCount || 0) > 0);
const leftVisible = computed(() => left.active.value && leftHasContent.value);
const rightVisible = computed(() => right.active.value && rightHasContent.value);

function applyBreakpointDefaults() {
    [left, right].forEach((panel) => {
        if (!panel.active.value) return;

        const el = panel.ref.value;
        if (!el) return;

        const current = el.style.width;
        if (!current || panel.priorDefaults.has(current)) {
            el.style.width = `${getDefaultWidth(panel)}px`;
        }
    });
}

watch(
    [leftVisible, rightVisible],
    () => {
        const el = document.getElementById('main-content');
        if (!el) return;

        if (leftVisible.value || rightVisible.value) {
            el.setAttribute('data-panels-showing', '');
        } else {
            el.removeAttribute('data-panels-showing');
        }
    },
);

onMounted(() => {
    applyBreakpointDefaults();
    window.addEventListener('resize', applyBreakpointDefaults);
});

onUnmounted(() => {
    window.removeEventListener('resize', applyBreakpointDefaults);
});
</script>

<template>
    <Teleport defer to="#main-content">
        <div
            v-show="leftVisible"
            :data-left-panel="leftVisible ? '' : undefined"
            ref="leftPanel"
            id="left-panel"
            tabindex="-1"
            style="order: -1"
            class="sticky top-0 overflow-y-scroll overscroll-y-contain overflow-x-clip h-full max-[1000px]:!w-0 max-[1000px]:!p-0 grid mx-auto focus:outline-none max-sm:ps-2 pe-2"
        ></div>
    </Teleport>

    <Teleport defer to="#main-content">
        <div
            v-show="rightVisible"
            :data-right-panel="rightVisible ? '' : undefined"
            ref="rightPanel"
            id="right-panel"
            tabindex="-1"
            style="order: 2"
            class="sticky top-0 overflow-y-scroll overscroll-y-contain h-full max-[1000px]:!w-0 max-[1000px]:!p-0 min-[1000px]:-mr-2 grid mx-auto focus:outline-none max-sm:ps-2 ps-2"
        ></div>
    </Teleport>

    <slot />
</template>
