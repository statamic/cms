<script setup>
import Header from '@/components/global-header/Header.vue';
import Nav from '@/components/nav/Nav.vue';
import { ConfigProvider } from 'reka-ui';
import SessionExpiry from '@/components/SessionExpiry.vue';
import LicensingAlert from '@/components/LicensingAlert.vue';
import PortalTargets from '@/components/portals/PortalTargets.vue';
import Tooltips from '@/components/Tooltips.vue';
import { provide, watch, ref, onMounted, onUnmounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import useBodyClasses from './body-classes.js';
import useStatamicPageProps from '@/composables/page-props.js';
import useMaxWidthToggle from '@/composables/use-max-width-toggle.js';

useBodyClasses('bg-global-header-bg font-sans leading-normal text-gray-900 dark:text-white');

const props = defineProps({
    additionalBreadcrumbs: { type: Array, default: () => [] },
});

const additionalBreadcrumbs = ref(props.additionalBreadcrumbs);
watch(() => props.additionalBreadcrumbs, (newVal) => additionalBreadcrumbs.value = newVal);

const { isMaxWidthEnabled, toggle } = useMaxWidthToggle();
const page = usePage();

// Add route-based body class for page-specific CSS overrides
let navigationListener = null;

function updateBodyClass() {
    const path = page.url.replace(/^\//, '').replace(/\//g, '-') || 'home';
    document.body.className = document.body.className.replace(/\bpage-\S+/g, '');
    document.body.classList.add(`page-${path}`);
}

onMounted(() => {
    updateBodyClass();
    navigationListener = router.on('success', updateBodyClass);
});

onUnmounted(() => {
    if (navigationListener) {
        navigationListener();
    }
});

provide('layout', {
    additionalBreadcrumbs,
    isMaxWidthEnabled,
    toggleMaxWidth: toggle,
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
                <div id="content-card" class="relative content-card grid min-h-full mx-auto">
                    <div :class="['w-full mx-auto', { 'max-w-page': isMaxWidthEnabled }]">
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
