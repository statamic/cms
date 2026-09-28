<script setup>
import Head from '@/pages/layout/Head.vue';
import { Header, CommandPaletteItem, Button, PublishContainer, DocsCallout } from '@ui';
import { computed, onMounted, onUnmounted, ref, useTemplateRef } from 'vue';
import { Pipeline, Request } from '@ui/Publish/SavePipeline.js';

const props = defineProps({
    blueprint: Object,
    initialValues: Object,
    meta: Object,
    updateUrl: String,
});

const container = useTemplateRef('container');
const values = ref(props.initialValues);
const errors = ref({});
const saving = ref(false);

const pageTitle = computed(() => Statamic.$config.get('multisiteEnabled') ? __('Configure Sites') : __('Configure Site'));

const initialSiteHandles = computed(() => {
    return Statamic.$config.get('multisiteEnabled')
        ? props.initialValues.sites.map((site) => site.handle)
        : [props.initialValues.handle];
});

const currentSiteHandles = computed(() => {
    return Statamic.$config.get('multisiteEnabled')
        ? values.value.sites.map((site) => site.handle)
        : [values.value.handle];
});

const initialHandleChanged = computed(() => initialSiteHandles.value.filter((handle) => !currentSiteHandles.value.includes(handle)).length > 0);
const initialHandleChangedWarning = computed(() => __('Warning! Changing a site handle may break existing site content!'));

function save() {
    if (initialHandleChanged.value && !confirm(initialHandleChangedWarning.value)) {
        return;
    }

    new Pipeline()
        .provide({ container, errors, saving })
        .through([
            new Request(props.updateUrl, 'patch')
        ])
        .then((response) => {
            Statamic.$toast.success(__('Saved'));

            if (Statamic.$config.get('multisiteEnabled')) {
                window.location.reload();
            }
        });
}

let saveKeyBinding;

onMounted(() => {
    saveKeyBinding = Statamic.$keys.bindGlobal(['mod+s'], (e) => {
        e.preventDefault();
        save();
    });
});

onUnmounted(() => saveKeyBinding.destroy());
</script>

<template>
    <Head :title="__('Configure Sites')" />

    <div class="max-w-page mx-auto">
        <Header :title="pageTitle" icon="site">
            <CommandPaletteItem
                :category="$commandPalette.category.Actions"
                :text="__('Save')"
                icon="save"
                :action="save"
                prioritize
                v-slot="{ text, action }"
            >
                <Button type="submit" variant="primary" @click="action">{{ text }}</Button>
            </CommandPaletteItem>
        </Header>

        <PublishContainer
            v-if="blueprint"
            ref="container"
            name="sites"
            reference="sites"
            :blueprint
            v-model="values"
            :meta
            :errors
        />

        <DocsCallout :topic="__('Multi-Site')" url="multi-site" />
    </div>
</template>
