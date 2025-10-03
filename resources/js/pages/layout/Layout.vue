<script setup>
import Header from '@/components/global-header/Header.vue';
import Nav from '@/components/nav/Nav.vue';
import { ConfigProvider } from 'reka-ui';
import SessionExpiry from '@/components/SessionExpiry.vue';
import LicensingAlert from '@/components/LicensingAlert.vue';
import PortalTargets from '@/components/portals/PortalTargets.vue';
import { provide, watch, ref } from 'vue';
import useBodyClasses from './body-classes.js';
import useStatamicPageProps from '@/composables/page-props.js';

useBodyClasses('bg-global-header-bg dark:bg-dark-global-header-bg font-sans leading-normal text-gray-900 dark:text-white');

const props = defineProps({
    additionalBreadcrumbs: { type: Array, default: () => [] },
});

const additionalBreadcrumbs = ref(props.additionalBreadcrumbs);
watch(() => props.additionalBreadcrumbs, (newVal) => additionalBreadcrumbs.value = newVal);

provide('layout', {
    additionalBreadcrumbs,
});
</script>

<template>
    <ConfigProvider>
        <SessionExpiry />
        <LicensingAlert />
        <Header />

        <main id="main" class="flex bg-body-bg dark:bg-dark-body-bg dark:border-t rounded-t-2xl dark:border-dark-body-border fixed top-14 inset-x-0 bottom-0 min-h-[calc(100vh-3.5rem)]">
            <Nav />
            <div id="main-content" class="main-content p-2 h-full flex-1 overflow-y-auto rounded-t-2xl">
                <div id="content-card" class="relative content-card min-h-full">
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
