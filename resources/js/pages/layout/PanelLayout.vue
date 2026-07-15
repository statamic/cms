<script setup>
import { provide, ref, watch, onMounted, onUnmounted, useTemplateRef } from 'vue';
import useResizable from '@/composables/use-resizable.js';

const minWidth = 175;
const maxWidth = 450;
const paddingOffset = '0.5rem';
const breakpoints = { narrow: 1250, compact: 1400 };

const left = {
    ref: useTemplateRef('leftPanel'),
    active: ref(false),
    edge: 'right',
    defaults: [minWidth, 262, 320],
    storageKey: 'statamic.panels.left-width',
};

const right = {
    ref: useTemplateRef('rightPanel'),
    active: ref(false),
    edge: 'left',
    defaults: [300, 290, 320],
    storageKey: 'statamic.panels.right-width',
};

provide('leftPanelActive', left.active);
provide('rightPanelActive', right.active);

function formatWidth(panel, width) {
    return panel === left ? `calc(${width}px - ${paddingOffset})` : `${width}px`;
}

function getDefaultWidth(panel) {
    const w = window.innerWidth;
    if (w < breakpoints.narrow) return formatWidth(panel, panel.defaults[0]);
    if (w < breakpoints.compact) return formatWidth(panel, panel.defaults[1]);
    return formatWidth(panel, panel.defaults[2]);
}

const { makeResizable } = useResizable();

[left, right].forEach((panel) => {
    makeResizable(panel.ref, panel.active, {
        edge: panel.edge,
        minWidth,
        maxWidth,
        defaultWidth: () => getDefaultWidth(panel),
        storageKey: panel.storageKey,
    });

    // Pre-compute the set of width strings we treat as "unmodified by the user"
    panel.priorDefaults = new Set(panel.defaults.map((d) => formatWidth(panel, d)));
});

function applyBreakpointDefaults() {
    [left, right].forEach((panel) => {
        if (!panel.active.value) return;
        if (localStorage.getItem(panel.storageKey)) return;

        const el = panel.ref.value;
        if (!el) return;

        const current = el.style.width;
        if (!current || panel.priorDefaults.has(current)) {
            el.style.width = getDefaultWidth(panel);
        }
    });
}

watch(
    [left.active, right.active],
    () => {
        const el = document.getElementById('main-content');
        if (!el) return;

        if (left.active.value || right.active.value) {
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
    document.getElementById('main-content')?.removeAttribute('data-panels-showing');
});
</script>

<template>
    <Teleport defer to="#main-content">
        <div
            v-show="left.active"
            :data-left-panel="left.active ? '' : undefined"
            ref="leftPanel"
            id="left-panel"
            tabindex="-1"
            style="order: -1"
            class="sticky top-0 overflow-y-scroll overscroll-y-contain overflow-x-clip h-full min-[1000px]:grid mx-auto focus:outline-none pe-2"
        ></div>
    </Teleport>

    <Teleport defer to="#main-content">
        <div
            v-show="right.active"
            :data-right-panel="right.active ? '' : undefined"
            ref="rightPanel"
            id="right-panel"
            tabindex="-1"
            style="order: 2"
            class="sticky top-0 overflow-y-scroll overscroll-y-contain h-full min-[1000px]:-mr-2 min-[1000px]:grid mx-auto focus:outline-none ps-2"
        ></div>
    </Teleport>

    <slot />
</template>
