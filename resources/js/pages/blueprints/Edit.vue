<script setup>
import Head from '@/pages/layout/Head.vue';
import { DocsCallout } from '@ui';

defineProps({
	blueprint: Object,
	action: String,
	showTitle: Boolean,
	useTabs: { type: Boolean, default: true },
	canDefineLocalizable: Boolean,
	resetRoute: String,
	isResettable: Boolean,
	isFormBlueprint: Boolean,
});
</script>

<template>
    <Head :title="__('Edit Blueprint')" />

    <div class="max-w-5xl mx-auto">
        <blueprint-builder
            :show-title="showTitle"
            :action="action"
            :initial-blueprint="blueprint"
            :use-tabs="useTabs"
            :can-define-localizable="canDefineLocalizable"
            :is-form-blueprint="isFormBlueprint"
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
    </div>
</template>
