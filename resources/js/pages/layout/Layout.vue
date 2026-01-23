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

useBodyClasses('bg-global-header-bg font-sans leading-normal text-gray-900 dark:text-white');

const props = defineProps({
    additionalBreadcrumbs: { type: Array, default: () => [] },
});

const additionalBreadcrumbs = ref(props.additionalBreadcrumbs);
watch(() => props.additionalBreadcrumbs, (newVal) => additionalBreadcrumbs.value = newVal);

provide('layout', {
    additionalBreadcrumbs,
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

onMounted(() => {
    navigationListener = router.on('success', focusMain);
    focusMain();
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
                <div id="content-card" tabindex="-1" class="relative content-card grid min-h-full">
                    <div class="w-full">
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
                <ui-input :model-value="$root.copyToClipboardModalUrl" readonly copyable class="font-mono text-sm dark" />
            </div>
        </confirmation-modal>

        <PortalTargets />
        <Tooltips />
    </ConfigProvider>
</template>
