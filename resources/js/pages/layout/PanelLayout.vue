<script setup>
import { provide, ref, onMounted, onUnmounted, useTemplateRef } from 'vue';
import useResizable from '@/composables/use-resizable.js';

const leftPanelRef = useTemplateRef('leftPanel');
const rightPanelRef = useTemplateRef('rightPanel');

const leftPanelActive = ref(false);
const rightPanelActive = ref(false);

provide('leftPanelActive', leftPanelActive);
provide('rightPanelActive', rightPanelActive);

const { makeResizable } = useResizable();

const sidebarMinWidth = 175;
const sidebarMaxWidth = 450;
const leftSidebarCompactDefaultWidth = 270;
const rightSidebarNarrowDefaultWidth = 300;
const rightSidebarCompactDefaultWidth = 300;
const sidebarWideDefaultWidth = 320;
const sidebarNarrowBreakpoint = 1250;
const sidebarCompactBreakpoint = 1400;

const getLeftSidebarDefaultWidth = () => {
    if (typeof window === 'undefined') return sidebarWideDefaultWidth;
    const w = window.innerWidth;
    if (w < sidebarNarrowBreakpoint) return sidebarMinWidth;
    if (w < sidebarCompactBreakpoint) return leftSidebarCompactDefaultWidth;
    return sidebarWideDefaultWidth;
};

const getRightSidebarDefaultWidth = () => {
    if (typeof window === 'undefined') return sidebarWideDefaultWidth;
    const w = window.innerWidth;
    if (w < sidebarNarrowBreakpoint) return rightSidebarNarrowDefaultWidth;
    if (w < sidebarCompactBreakpoint) return rightSidebarCompactDefaultWidth;
    return sidebarWideDefaultWidth;
};

makeResizable(leftPanelRef, leftPanelActive, { edge: 'right', minWidth: sidebarMinWidth, maxWidth: sidebarMaxWidth, defaultWidth: getLeftSidebarDefaultWidth });
makeResizable(rightPanelRef, rightPanelActive, { edge: 'left', minWidth: sidebarMinWidth, maxWidth: sidebarMaxWidth, defaultWidth: getRightSidebarDefaultWidth });

const applyBreakpointDefaults = () => {
    if (typeof window === 'undefined') return;

    const w = window.innerWidth;
    let leftTarget;
    let rightTarget;
    if (w < sidebarNarrowBreakpoint) {
        leftTarget = sidebarMinWidth;
        rightTarget = rightSidebarNarrowDefaultWidth;
    } else if (w < sidebarCompactBreakpoint) {
        leftTarget = leftSidebarCompactDefaultWidth;
        rightTarget = rightSidebarCompactDefaultWidth;
    } else {
        leftTarget = sidebarWideDefaultWidth;
        rightTarget = sidebarWideDefaultWidth;
    }

    const minPx = `${sidebarMinWidth}px`;
    const leftCompactPx = `${leftSidebarCompactDefaultWidth}px`;
    const rightNarrowPx = `${rightSidebarNarrowDefaultWidth}px`;
    const rightCompactPx = `${rightSidebarCompactDefaultWidth}px`;
    const widePx = `${sidebarWideDefaultWidth}px`;

    const leftPriorDefaults = new Set([minPx, leftCompactPx, widePx]);
    const rightPriorDefaults = new Set([minPx, leftCompactPx, widePx, rightNarrowPx, rightCompactPx]);

    const applyIfUnmodified = (panelEl, target, priorDefaults) => {
        if (!panelEl) return;
        const current = panelEl.style.width;

        if (!current || priorDefaults.has(current)) {
            panelEl.style.width = `${target}px`;
        }
    };

    if (leftPanelActive.value) applyIfUnmodified(leftPanelRef.value, leftTarget, leftPriorDefaults);
    if (rightPanelActive.value) applyIfUnmodified(rightPanelRef.value, rightTarget, rightPriorDefaults);
};

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
            v-show="leftPanelActive"
            :data-left-panel="leftPanelActive ? '' : undefined"
            ref="leftPanel"
            id="left-panel"
            tabindex="-1"
            style="order: -1"
            class="sticky top-0 overflow-y-scroll overscroll-y-contain overflow-x-clip h-full max-[1000px]:!w-0 max-[1000px]:!p-0 grid mx-auto focus:outline-none max-sm:ps-2 pe-2"
        ></div>
    </Teleport>

    <Teleport defer to="#main-content">
        <div v-show="leftPanelActive || rightPanelActive" class="field-to-panel-connector-initial" style="order: 1"></div>
        <div v-show="leftPanelActive || rightPanelActive" class="field-to-panel-connector-scroll-past" style="order: 1"></div>
    </Teleport>

    <Teleport defer to="#main-content">
        <div
            v-show="rightPanelActive"
            :data-right-panel="rightPanelActive ? '' : undefined"
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
