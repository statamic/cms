<script setup>
import Header from '@/components/global-header/Header.vue';
import Nav from '@/components/nav/Nav.vue';
import { ConfigProvider } from 'reka-ui';
import SessionExpiry from '@/components/SessionExpiry.vue';
import LicensingAlert from '@/components/LicensingAlert.vue';
import PortalTargets from '@/components/portals/PortalTargets.vue';
import Tooltips from '@/components/Tooltips.vue';
import { provide, watch, ref, onMounted, onUnmounted, nextTick, computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import useBodyClasses from './body-classes.js';
import useStatamicPageProps from '@/composables/page-props.js';
import useMaxWidthToggle from '@/composables/use-max-width-toggle.js';

useBodyClasses('bg-global-header-bg font-sans leading-normal text-gray-900 dark:text-white');

const props = defineProps({
    additionalBreadcrumbs: { type: Array, default: () => [] },
});

const page = usePage();
const isFormsRoute = computed(() => (page.url || '').startsWith('/cp/forms'));
const enableCpSidebarEnd = computed(() => Boolean(page.props.enableCpSidebarEnd ?? page.props.enableRightSidebar) || isFormsRoute.value);
const cpSidebarEnd = computed(() => page.props.cpSidebarEnd ?? page.props.rightSidebar ?? null);

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
const navWidthStorageKey = 'statamic.nav.width';
const cpSidebarEndWidthStorageKey = 'statamic.cp-sidebar-end.width';
const legacyRightSidebarWidthStorageKey = 'statamic.right-sidebar.width';
const MIN_NAV_WIDTH = 150;
const MAX_NAV_WIDTH = 400;
const MIN_CP_SIDEBAR_END_WIDTH = MIN_NAV_WIDTH;
const MAX_CP_SIDEBAR_END_WIDTH = MAX_NAV_WIDTH;
const mainContentRef = ref(null);
const contentCardRef = ref(null);
const cpSidebarEndRef = ref(null);

let isResizing = false;
let currentWidthPx = null;
let contentInsetPx = 0;
let pointerMoveListener = null;
let pointerUpListener = null;

function clampNavWidthPx(widthPx) {
    return Math.min(Math.max(widthPx, MIN_NAV_WIDTH), MAX_NAV_WIDTH);
}

function setNavWidthPx(widthPx) {
    document.documentElement.style.setProperty('--nav-width', `${widthPx}px`);
}

function setCpSidebarEndWidthPx(widthPx) {
    document.documentElement.style.setProperty('--sidebar-end-width', `${widthPx}px`);
}

function restoreSavedNavWidth() {
    const saved = localStorage.getItem(navWidthStorageKey);
    if (!saved) return;

    const widthPx = Number(saved);
    if (!Number.isFinite(widthPx)) return;

    setNavWidthPx(clampNavWidthPx(widthPx));
}

function restoreSavedCpSidebarEndWidth() {
    const saved = localStorage.getItem(cpSidebarEndWidthStorageKey) ?? localStorage.getItem(legacyRightSidebarWidthStorageKey);
    if (!saved) return;

    const widthPx = Number(saved);
    if (!Number.isFinite(widthPx)) return;

    const clamped = Math.min(Math.max(widthPx, MIN_CP_SIDEBAR_END_WIDTH), MAX_CP_SIDEBAR_END_WIDTH);
    setCpSidebarEndWidthPx(clamped);
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
        localStorage.setItem(navWidthStorageKey, Math.round(currentWidthPx));
    }

    currentWidthPx = null;
}

let isCpSidebarEndResizing = false;
let cpSidebarEndWidthPx = null;
let cpSidebarEndPointerMoveListener = null;
let cpSidebarEndPointerUpListener = null;
let cpSidebarEndInsetPx = 0;

function stopCpSidebarEndResize({ persist = true } = {}) {
    if (!isCpSidebarEndResizing) return;

    isCpSidebarEndResizing = false;
    document.documentElement.classList.remove('nav-resizing');

    if (cpSidebarEndPointerMoveListener) document.removeEventListener('pointermove', cpSidebarEndPointerMoveListener);
    if (cpSidebarEndPointerUpListener) document.removeEventListener('pointerup', cpSidebarEndPointerUpListener);
    cpSidebarEndPointerMoveListener = null;
    cpSidebarEndPointerUpListener = null;

    if (persist && cpSidebarEndWidthPx !== null) {
        localStorage.setItem(cpSidebarEndWidthStorageKey, Math.round(cpSidebarEndWidthPx));
    }

    cpSidebarEndWidthPx = null;
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
    localStorage.removeItem(navWidthStorageKey);
    document.documentElement.style.removeProperty('--nav-width');
}

function startCpSidebarEndResize(event) {
    if (!enableCpSidebarEnd.value || isCpSidebarEndResizing || !mainContentRef.value || !contentCardRef.value) return;

    isCpSidebarEndResizing = true;
    document.documentElement.classList.add('nav-resizing');
    event?.currentTarget?.setPointerCapture?.(event.pointerId);

    const dir = getComputedStyle(document.documentElement).direction;
    const isRtl = dir === 'rtl';

    const mainContentRect = mainContentRef.value.getBoundingClientRect();
    const contentCardRect = contentCardRef.value.getBoundingClientRect();
    cpSidebarEndInsetPx = isRtl
        ? (contentCardRect.left - mainContentRect.left)
        : (mainContentRect.right - contentCardRect.right);

    cpSidebarEndPointerMoveListener = (e) => {
        const proposedWidth = isRtl
            ? (e.clientX - cpSidebarEndInsetPx)
            : (window.innerWidth - e.clientX - cpSidebarEndInsetPx);
        cpSidebarEndWidthPx = Math.min(Math.max(proposedWidth, MIN_CP_SIDEBAR_END_WIDTH), MAX_CP_SIDEBAR_END_WIDTH);
        setCpSidebarEndWidthPx(cpSidebarEndWidthPx);
    };

    cpSidebarEndPointerUpListener = () => stopCpSidebarEndResize({ persist: true });

    document.addEventListener('pointermove', cpSidebarEndPointerMoveListener);
    document.addEventListener('pointerup', cpSidebarEndPointerUpListener);
}

function resetCpSidebarEndWidth() {
    stopCpSidebarEndResize({ persist: false });
    localStorage.removeItem(cpSidebarEndWidthStorageKey);
    localStorage.removeItem(legacyRightSidebarWidthStorageKey);
    document.documentElement.style.removeProperty('--sidebar-end-width');
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
    restoreSavedCpSidebarEndWidth();
    focusMain();
});

onUnmounted(() => {
    if (navigationListener) {
        navigationListener();
    }

    stopResize({ persist: false });
    stopCpSidebarEndResize({ persist: false });
});
</script>

<template>
    <ConfigProvider>
        <SessionExpiry />
        <LicensingAlert />
        <Header />

        <main id="main" class="flex bg-body-bg dark:border-t dark:border-body-border rounded-t-2xl fixed top-14 inset-x-0 bottom-0 min-h-[calc(100vh-3.5rem)]" :class="{ 'cp-sidebar-end-is-open': enableCpSidebarEnd }">
            <Nav />
            <!-- The data attribute allows CSS to target elements when max-width is disabled. -->
            <div ref="mainContentRef" id="main-content" class="main-content sm:p-2 h-full flex-1 overflow-y-auto focus:outline-none rounded-t-2xl" :data-max-width-enabled="isMaxWidthEnabled">
                <div ref="contentCardRef" id="content-card" tabindex="-1" class="focus:outline-none relative content-card grid min-h-full mx-auto">
                    <div
                        class="content-card-resize-handle"
                        @pointerdown.prevent="startResize"
                        @dblclick="resetNavWidth"
                    />
                    <div
                        v-if="enableCpSidebarEnd"
                        class="cp-sidebar-end-resize-handle"
                        @pointerdown.prevent="startCpSidebarEndResize"
                        @dblclick="resetCpSidebarEndWidth"
                    />
                    <!-- Data attribute used by the CSS style tag below to override max-width when disabled.-->
                    <div class="w-full min-w-0 mx-auto max-w-page" data-max-width-wrapper>
                        <slot />
                    </div>
                </div>
            </div>
            <aside v-if="enableCpSidebarEnd" ref="cpSidebarEndRef" class="cp-sidebar-end">
                <div id="right-sidebar-content" class="cp-sidebar-end-content">
                    <div
                        v-if="cpSidebarEnd"
                        class="rounded-lg border border-gray-300/80 dark:border-gray-700 bg-white dark:bg-gray-925 p-3"
                    >
                        <h2
                            class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2"
                            v-text="cpSidebarEnd.title"
                        />
                        <ul class="flex flex-col gap-1.5">
                            <li v-for="(item, i) in cpSidebarEnd.links ?? []" :key="i">
                                <Link
                                    v-if="item.url"
                                    :href="item.url"
                                    class="flex items-center gap-2 rounded-md px-2 py-1 text-sm text-gray-700 hover:bg-gray-400/15 dark:text-gray-300"
                                >
                                    <span v-text="item.text" />
                                </Link>
                                <span
                                    v-else
                                    class="flex items-center gap-2 rounded-md px-2 py-1 text-sm text-gray-500 dark:text-gray-400"
                                    v-text="item.text"
                                />
                            </li>
                        </ul>
                    </div>
                </div>
            </aside>
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
