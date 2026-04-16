<script setup>
import { provide, ref, onMounted, onUnmounted, useTemplateRef } from 'vue';
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
            v-show="left.active"
            :data-left-panel="left.active ? '' : undefined"
            ref="leftPanel"
            id="left-panel"
            tabindex="-1"
            style="order: -1"
            class="sticky top-0 overflow-y-scroll overscroll-y-contain overflow-x-clip h-full max-[1000px]:!w-0 max-[1000px]:!p-0 grid mx-auto focus:outline-none max-sm:ps-2 pe-2"
        ></div>
    </Teleport>

    <Teleport defer to="#main-content">
        <div v-show="left.active || right.active" class="field-to-panel-connector-initial" style="order: 1"></div>
        <div v-show="left.active || right.active" class="field-to-panel-connector-scroll-past" style="order: 1"></div>
    </Teleport>

    <Teleport defer to="#main-content">
        <div
            v-show="right.active"
            :data-right-panel="right.active ? '' : undefined"
            ref="rightPanel"
            id="right-panel"
            tabindex="-1"
            style="order: 2"
            class="sticky top-0 overflow-y-scroll overscroll-y-contain h-full max-[1000px]:!w-0 max-[1000px]:!p-0 grid mx-auto focus:outline-none max-sm:ps-2 ps-2"
        ></div>
    </Teleport>

    <slot />
</template>

<style>
#main-content:has([data-left-panel], [data-right-panel]) {
    display: flex;
}

#main-content:has([data-left-panel], [data-right-panel]) > #content-card {
    flex: 1;
}

@media (max-width: 1000px) {
    #main-content:has([data-left-panel], [data-right-panel]) [data-max-width-wrapper] {
        display: grid;
        grid-template-columns: auto 1fr auto;
    }
}
</style>
