<script setup>
import Header from '@/components/global-header/Header.vue';
import Nav from '@/components/nav/Nav.vue';
import { ConfigProvider } from 'reka-ui';
import SessionExpiry from '@/components/SessionExpiry.vue';
import LicensingAlert from '@/components/LicensingAlert.vue';
import PortalTargets from '@/components/portals/PortalTargets.vue';
import Tooltips from '@/components/Tooltips.vue';
import { provide, watch, ref, onMounted, onUnmounted, nextTick, useTemplateRef } from 'vue';
import { router } from '@inertiajs/vue3';
import useBodyClasses from './body-classes.js';
import useMaxWidthToggle from '@/composables/use-max-width-toggle.js';
import useResizable from '@/composables/use-resizable.js';

useBodyClasses('bg-global-header-bg font-sans leading-normal text-gray-900 dark:text-white');

const props = defineProps({
    additionalBreadcrumbs: { type: Array, default: () => [] },
});

const additionalBreadcrumbs = ref(props.additionalBreadcrumbs);
watch(() => props.additionalBreadcrumbs, (newVal) => additionalBreadcrumbs.value = newVal);

// Function to toggle the max-width state
const { isMaxWidthEnabled, toggle } = useMaxWidthToggle();
provide('layout', {
    additionalBreadcrumbs,
    isMaxWidthEnabled,
    toggleMaxWidth: toggle,
});

// Focus management: focus main element if no input has auto-focus
let navigationListener = null;

function focusMain() {
    // Wait for components to mount and autofocus to process
    nextTick(() => {
        requestAnimationFrame(() => {
            setTimeout(() => {
                // If an input is already focused, we're done
                if (document.activeElement?.matches('input, textarea, select, [contenteditable]')) {
                    return;
                }

                // Find any input with autofocus attribute (including nested in UI components)
                const autofocusInput = document.querySelector('input[autofocus], textarea[autofocus], select[autofocus]') ||
                    document.querySelector('[data-ui-input] input[autofocus]');

                // If autofocus input exists but isn't focused, focus it manually
                if (autofocusInput && document.activeElement !== autofocusInput) {
                    autofocusInput.focus();
                    return;
                }

                // Otherwise, focus the content card
                if (!autofocusInput) {
                    document.querySelector('#content-card')?.focus();
                }
            }, 100);
        });
    });
}

const leftPanelRef = useTemplateRef('leftPanel');
const rightPanelRef = useTemplateRef('rightPanel');

const leftPanelActive = ref(false);
const rightPanelActive = ref(false);

provide('leftPanelActive', leftPanelActive);
provide('rightPanelActive', rightPanelActive);

const { makeResizable } = useResizable();

const sidebarMinWidth = 175;
const sidebarMaxWidth = 450;
const desktopSidebarDefaultWidth = 320;

const getSidebarDefaultWidth = () => {
    // When the viewport is narrow, start the sidebar at its minimum width.
    if (typeof window === 'undefined') return desktopSidebarDefaultWidth;
    return window.innerWidth < 1200 ? sidebarMinWidth : desktopSidebarDefaultWidth;
};

makeResizable(leftPanelRef, leftPanelActive, { edge: 'right', minWidth: sidebarMinWidth, maxWidth: sidebarMaxWidth, defaultWidth: getSidebarDefaultWidth });
makeResizable(rightPanelRef, rightPanelActive, { edge: 'left', minWidth: sidebarMinWidth, maxWidth: sidebarMaxWidth, defaultWidth: getSidebarDefaultWidth });

const applyBreakpointDefaults = () => {
    if (typeof window === 'undefined') return;

    const narrow = window.innerWidth < 1200;
    const target = narrow ? sidebarMinWidth : desktopSidebarDefaultWidth;
    const narrowPx = `${sidebarMinWidth}px`;
    const desktopPx = `${desktopSidebarDefaultWidth}px`;

    const applyIfUnmodified = (panelEl) => {
        if (!panelEl) return;
        const current = panelEl.style.width;

        // Only apply if it currently equals our prior "default", meaning the user hasn't dragged it.
        if (!current || current === narrowPx || current === desktopPx) {
            panelEl.style.width = `${target}px`;
        }
    };

    if (leftPanelActive.value) applyIfUnmodified(leftPanelRef.value);
    if (rightPanelActive.value) applyIfUnmodified(rightPanelRef.value);
};

onMounted(() => {
    navigationListener = router.on('success', focusMain);
    focusMain();

    // Apply once immediately, then on resize.
    applyBreakpointDefaults();
    window.addEventListener('resize', applyBreakpointDefaults);
});

onUnmounted(() => {
    if (navigationListener) {
        navigationListener();
    }
    window.removeEventListener('resize', applyBreakpointDefaults);
});

// TODO: Make the nav an overlay
</script>

<template>
    <ConfigProvider>
        <SessionExpiry />
        <LicensingAlert />
        <Header />

        <main id="main" class="flex bg-body-bg dark:border-t dark:border-body-border rounded-t-2xl fixed top-14 inset-x-0 bottom-0 min-h-[calc(100vh-3.5rem)]">
            <Nav />
            <!-- The data attribute allows CSS to target elements when max-width is disabled. -->
            <div
                id="main-content"
                class="main-content sm:p-2 h-full flex-1 overflow-y-auto focus:outline-none rounded-t-2xl"
                :class="{
                    'flex': leftPanelActive || rightPanelActive,
                }"
                :data-max-width-enabled="isMaxWidthEnabled"
            >
                <!-- Panel contents will be injected via <Teleport> -->
                <div v-show="leftPanelActive" data-left-panel ref="leftPanel" id="left-panel" tabindex="-1" class="max-[1000px]:!w-0 max-[1000px]:!p-0 focus:outline-none relative layout-left-panel overflow-y-scroll overflow-x-clip grid max-sm:ps-2 pe-2 min-h-full mx-auto">
                </div>
                <div id="content-card" tabindex="-1" class="focus:outline-none relative content-card grid min-h-full mx-auto" :class="{ 'flex-1': leftPanelActive || rightPanelActive }">
                    <!-- Data attribute used by the CSS style tag below to override max-width when disabled.-->
                    <div class="w-full min-w-0 mx-auto max-w-page max-[1220px]:mb-18" data-max-width-wrapper>
                        <slot />
                    </div>
                </div>
                <div v-show="rightPanelActive" ref="rightPanel" data-right-panel id="right-panel" tabindex="-1" class="focus:outline-none relative layout-right-panel grid min-h-full mx-auto">
                    <!-- Panel contents will be injected via <Teleport> -->
                </div>
            </div>
        </main>

        <Component
            v-for="component in $root.appendedComponents"
            :key="component.id"
            :is="component.name"
            v-bind="component.props"
            v-on="component.events"
        />

        <confirmation-modal
            :open="$root.copyToClipboardModalUrl !== null"
            :cancellable="false"
            :button-text="__('OK')"
            :title="__('Copy to clipboard')"
            @confirm="$root.copyToClipboardModalUrl = null"
        >
            <div class="prose">
                <ui-input :model-value="$root.copyToClipboardModalUrl" readonly copyable class="font-mono text-sm" />
            </div>
        </confirmation-modal>

        <PortalTargets />
        <Tooltips />
    </ConfigProvider>
</template>

<style>
/*
    Max-width override CSS:
    When max-width is disabled (data-max-width-enabled="false"),
    this rule removes the max-width constraint from elements tagged with data-max-width-wrapper.

    This allows the content to expand to full width when the toggle is disabled,
    overriding Tailwind max-width class constraints.
*/
[data-max-width-enabled="false"] [data-max-width-wrapper] {
    width: 100%;
    max-width: none;
}
</style>
