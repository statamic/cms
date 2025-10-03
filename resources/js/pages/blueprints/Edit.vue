<script setup>
import Head from '@/pages/layout/Head.vue';
import { DocsCallout } from '@ui';

defineProps([
    'blueprint',
    'action',
    'showTitle',
    'useTabs',
    'canDefineLocalizable',
    'resetRoute',
    'isResettable',
]);
</script>

<template>
    <Head :title="__('Edit Blueprint')" />

    <blueprint-builder
        :show-title="showTitle"
        :action="action"
        :initial-blueprint="blueprint"
        :use-tabs="useTabs"
        :can-define-localizable="canDefineLocalizable"
    >
        <template v-if="isResettable" #actions>
            <ui-dropdown>
                <ui-dropdown-menu>
                    <ui-dropdown-item
                        :text="__('Reset')"
                        variant="destructive"
                        @click="$refs.resetter.confirm()"
                    />
                </ui-dropdown-menu>
            </ui-dropdown>
            <blueprint-resetter
                ref="resetter"
                :route="resetRoute"
                :resource="blueprint"
                reload
            />
        </template>
    </blueprint-builder>

    <DocsCallout :topic="__('Blueprints')" url="blueprints" />
</template>