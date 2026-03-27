<script setup>
import Header from '@/components/global-header/Header.vue';
import Nav from '@/components/nav/Nav.vue';
import { ConfigProvider } from 'reka-ui';
import SessionExpiry from '@/components/SessionExpiry.vue';
import LicensingAlert from '@/components/LicensingAlert.vue';
import PortalTargets from '@/components/portals/PortalTargets.vue';
import Tooltips from '@/components/Tooltips.vue';
import { provide, watch, ref, onMounted, onUnmounted, nextTick, computed, useTemplateRef } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import useBodyClasses from './body-classes.js';
import useMaxWidthToggle from '@/composables/use-max-width-toggle.js';

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

// Resizable sidebar (handle lives on the left edge of the content card)
const page = usePage();
const isEditingForm = computed(() => page.url.includes('/forms/'));

const navWidthStorageKey = computed(() => isEditingForm.value ? 'statamic.nav.width.forms' : 'statamic.nav.width');
const minNavWidth = computed(() => isEditingForm.value ? 480 : 150);
const maxNavWidth = computed(() => isEditingForm.value ? 1000 : 300);
const mainContentRef = useTemplateRef('mainContent');
const contentCardRef = useTemplateRef('contentCard');

watch(navWidthStorageKey, restoreSavedNavWidth);

let isResizing = false;
let currentWidthPx = null;
let contentInsetPx = 0;
let pointerMoveListener = null;
let pointerUpListener = null;

function clampNavWidthPx(widthPx) {
    return Math.min(Math.max(widthPx, minNavWidth.value), maxNavWidth.value);
}

function setNavWidthPx(widthPx) {
    document.documentElement.style.setProperty('--nav-width', `${widthPx}px`);
}

function restoreSavedNavWidth() {
    const saved = localStorage.getItem(navWidthStorageKey.value);

    if (!saved) {
        document.documentElement.style.removeProperty('--nav-width');
        return;
    }

    const widthPx = Number(saved);

    if (!Number.isFinite(widthPx)) {
        document.documentElement.style.removeProperty('--nav-width');
        return;
    }

    setNavWidthPx(clampNavWidthPx(widthPx));
}

function stopResize({ persist = true } = {}) {
    if (!isResizing) return;

    isResizing = false;
    document.documentElement.classList.remove('nav-resizing');

    if (pointerMoveListener) document.removeEventListener('pointermove', pointerMoveListener);
    if (pointerUpListener) document.removeEventListener('pointerup', pointerUpListener);
    pointerMoveListener = null;
    pointerUpListener = null;

    if (persist && currentWidthPx !== null) {
        localStorage.setItem(navWidthStorageKey.value, Math.round(currentWidthPx));
    }

    currentWidthPx = null;
}

function startResize(event) {
    if (isResizing || !mainContentRef.value || !contentCardRef.value) return;

    isResizing = true;
    document.documentElement.classList.add('nav-resizing');

    // Prevent losing the drag if the pointer leaves the handle.
    event?.currentTarget?.setPointerCapture?.(event.pointerId);

    const dir = getComputedStyle(document.documentElement).direction;
    const isRtl = dir === 'rtl';

    const mainContentRect = mainContentRef.value.getBoundingClientRect();
    const contentCardRect = contentCardRef.value.getBoundingClientRect();
    contentInsetPx = isRtl
        ? (mainContentRect.right - contentCardRect.right)
        : (contentCardRect.left - mainContentRect.left);

    pointerMoveListener = (e) => {
        const proposedWidth = isRtl
            ? (window.innerWidth - e.clientX - contentInsetPx)
            : (e.clientX - contentInsetPx);

        currentWidthPx = clampNavWidthPx(proposedWidth);
        setNavWidthPx(currentWidthPx);
    };

    pointerUpListener = () => stopResize({ persist: true });

    document.addEventListener('pointermove', pointerMoveListener);
    document.addEventListener('pointerup', pointerUpListener);
}

function resetNavWidth() {
    stopResize({ persist: false });
    localStorage.removeItem(navWidthStorageKey.value);
    document.documentElement.style.removeProperty('--nav-width');
}

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

onMounted(() => {
    navigationListener = router.on('success', focusMain);
    restoreSavedNavWidth();
    focusMain();
});

onUnmounted(() => {
    if (navigationListener) {
        navigationListener();
    }

    stopResize({ persist: false });
});
</script>

<template>
    <ConfigProvider>
        <SessionExpiry />
        <LicensingAlert />
        <Header />

        <main id="main" class="flex bg-body-bg dark:border-t dark:border-body-border rounded-t-2xl fixed top-14 inset-x-0 bottom-0 min-h-[calc(100vh-3.5rem)]">
            <Nav />
            <!-- The data attribute allows CSS to target elements when max-width is disabled. -->
            <div ref="mainContent" id="main-content" class="main-content sm:p-2 h-full flex-1 overflow-y-auto focus:outline-none rounded-t-2xl" :data-max-width-enabled="isMaxWidthEnabled">
                <div ref="contentCard" id="content-card" tabindex="-1" class="focus:outline-none relative content-card grid min-h-full mx-auto">
                    <div
                        class="content-card-resize-handle"
                        @pointerdown.prevent="startResize"
                        @dblclick="resetNavWidth"
                    />
                    <!-- Data attribute used by the CSS style tag below to override max-width when disabled.-->
                    <div class="w-full min-w-0 mx-auto max-w-page" data-max-width-wrapper>
                        <slot />
                    </div>
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
