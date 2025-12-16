<script setup>
import Header from '@/components/global-header/Header.vue';
import Nav from '@/components/nav/Nav.vue';
import { ConfigProvider } from 'reka-ui';
import SessionExpiry from '@/components/SessionExpiry.vue';
import LicensingAlert from '@/components/LicensingAlert.vue';
import PortalTargets from '@/components/portals/PortalTargets.vue';
import { provide, watch, ref, onMounted, onUnmounted, nextTick } from 'vue';
import { router } from '@inertiajs/vue3';
import useBodyClasses from './body-classes.js';
import useStatamicPageProps from '@/composables/page-props.js';

useBodyClasses('bg-global-header-bg font-sans leading-normal text-gray-900 dark:text-white');

const props = defineProps({
    additionalBreadcrumbs: { type: Array, default: () => [] },
});

const additionalBreadcrumbs = ref(props.additionalBreadcrumbs);
watch(() => props.additionalBreadcrumbs, (newVal) => additionalBreadcrumbs.value = newVal);

provide('layout', {
    additionalBreadcrumbs,
});

// Focus management: if no Input with :focus="true" is present, focus the main element
let navigationListener = null;

function checkAndFocusMain() {
    // Wait for all components to mount and any auto-focus to complete
    nextTick(() => {
        setTimeout(() => {
            const activeElement = document.activeElement;
            const isInputFocused = activeElement && (
                activeElement.matches('input, textarea, select, [contenteditable]') ||
                activeElement.closest('[role="combobox"], [role="textbox"]')
            );

            // If no input is focused, focus the main element
            if (!isInputFocused) {
                const mainElement = document.querySelector('#content-card');
                if (mainElement && typeof mainElement.focus === 'function') {
                    mainElement.focus();
                }
            }
        }, 100); // Small delay to allow any auto-focus to complete
    });
}

onMounted(() => {
    navigationListener = router.on('success', () => {
        checkAndFocusMain();
    });
    
    // Also check on initial mount
    checkAndFocusMain();
});

onUnmounted(() => {
    if (navigationListener) {
        navigationListener();
    }
});
</script>

<template>
    <ConfigProvider>
        <SessionExpiry />
        <LicensingAlert />
        <Header />

        <main id="main" class="flex bg-body-bg dark:border-t dark:border-body-border rounded-t-2xl fixed top-14 inset-x-0 bottom-0 min-h-[calc(100vh-3.5rem)]">
            <Nav />
            <div id="main-content" class="main-content sm:p-2 h-full flex-1 overflow-y-auto rounded-t-2xl">
                <div id="content-card" class="relative content-card min-h-full focus:outline-none" tabindex="-1">
                    <slot />
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
            v-if="$root.copyToClipboardModalUrl"
            :cancellable="false"
            :button-text="__('OK')"
            :title="__('Copy to clipboard')"
            @confirm="$root.copyToClipboardModalUrl = null"
        >
            <div class="prose">
                <ui-input :model-value="$root.copyToClipboardModalUrl" readonly copyable class="font-mono text-sm dark" />
            </div>
        </confirmation-modal>

        <PortalTargets />
    </ConfigProvider>
</template>
