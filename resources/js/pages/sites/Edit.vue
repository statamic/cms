<script setup>
import Head from '@/pages/layout/Head.vue';
import { Header, CommandPaletteItem, Button, PublishContainer, DocsCallout } from '@ui';
import { computed, onMounted, onUnmounted, ref, useTemplateRef } from 'vue';
import { Pipeline, Request } from '@ui/Publish/SavePipeline.js';
import { deepClone } from '@/util/clone.js';

const props = defineProps({
    blueprint: Object,
    initialValues: Object,
    meta: Object,
    updateUrl: String,
});

const isMultisite = computed(() => Statamic.$config.get('multisiteEnabled'));

function siteHandlesFromValues(values) {
    if (! isMultisite.value) {
        return [values.handle];
    }

    return Object.entries(values)
        .filter(([key, sites]) => key.endsWith('_sites') && Array.isArray(sites))
        .flatMap(([, sites]) => sites.map((site) => site.handle));
}

const container = useTemplateRef('container');
const values = ref(props.initialValues);
const errors = ref({});
const saving = ref(false);
const editingGroups = ref(false);
const groupsSnapshot = ref(null);
const containerKey = ref(0);

const groupsDirty = computed(() => {
    if (!editingGroups.value || !groupsSnapshot.value) return false;

    return JSON.stringify(values.value) !== JSON.stringify(groupsSnapshot.value);
});

const pageTitle = computed(() => isMultisite.value ? __('Configure Sites') : __('Configure Site'));

const initialSiteHandles = computed(() => siteHandlesFromValues(props.initialValues));

const currentSiteHandles = computed(() => siteHandlesFromValues(values.value));

const initialHandleChanged = computed(() => initialSiteHandles.value.filter((handle) => !currentSiteHandles.value.includes(handle)).length > 0);
const initialHandleChangedWarning = computed(() => __('Warning! Changing a site handle may break existing site content!'));

const hasOnlyOtherGroup = computed(() => {
    if (! isMultisite.value || ! props.blueprint?.tabs) {
        return false;
    }

    const sections = props.blueprint.tabs.flatMap((tab) => tab.sections ?? []);

    return sections.length > 0 && ! sections.some((section) => section.reorderable);
});

const editSiteGroupsLabel = computed(() => hasOnlyOtherGroup.value ? __('Add Site Groups') : __('Edit Site Groups'));

function startEditingGroups() {
    groupsSnapshot.value = deepClone(values.value);
    editingGroups.value = true;
}

function stopEditingGroups() {
    editingGroups.value = false;
    groupsSnapshot.value = null;
}

function discardGroupChanges() {
    values.value = deepClone(groupsSnapshot.value);
    containerKey.value++;
    stopEditingGroups();
}

function groupNamesFromValues(formValues) {
    return Object.fromEntries(
        Object.entries(formValues).filter(([key, value]) => /^group_.+_name$/.test(key) && typeof value === 'string'),
    );
}

function save() {
    if (editingGroups.value && !groupsDirty.value) {
        return;
    }

    if (initialHandleChanged.value && !confirm(initialHandleChangedWarning.value)) {
        return;
    }

    new Pipeline()
        .provide({ container, errors, saving })
        .through([
            new Request(props.updateUrl, 'patch', groupNamesFromValues(values.value))
        ])
        .then((response) => {
            Statamic.$toast.success(__('Saved'));

            if (isMultisite.value) {
                window.location.reload();
            }
        });
}

let saveKeyBinding;

onMounted(() => {
    saveKeyBinding = Statamic.$keys.bindGlobal(['mod+s'], (e) => {
        if (editingGroups.value && !groupsDirty.value) return;

        e.preventDefault();
        save();
    });
});

onUnmounted(() => saveKeyBinding.destroy());
</script>

<template>
    <Head :title="pageTitle" />

    <div class="max-w-page mx-auto">
        <Header :title="pageTitle" icon="site">
            <CommandPaletteItem
                v-if="!editingGroups && isMultisite"
                :category="$commandPalette.category.Actions"
                :text="editSiteGroupsLabel"
                icon="navigation"
                :action="startEditingGroups"
                v-slot="{ text, action }"
            >
                <Button icon="navigation" :text="text" @click="action" />
            </CommandPaletteItem>
            <Button
                v-if="editingGroups && !groupsDirty"
                :text="__('Cancel')"
                :disabled="saving"
                @click="stopEditingGroups"
            />
            <Button
                v-if="editingGroups && groupsDirty"
                :text="__('Discard Changes')"
                variant="filled"
                :disabled="saving"
                @click="discardGroupChanges"
            />
            <CommandPaletteItem
                :category="$commandPalette.category.Actions"
                :text="__('Save')"
                icon="save"
                :action="save"
                prioritize
                v-slot="{ text, action }"
            >
                <Button
                    type="submit"
                    variant="primary"
                    :text="text"
                    :loading="saving"
                    :disabled="editingGroups && !groupsDirty"
                    @click="action"
                />
            </CommandPaletteItem>
        </Header>

        <PublishContainer
            v-if="blueprint"
            :key="containerKey"
            ref="container"
            name="sites"
            reference="sites"
            :blueprint
            v-model="values"
            :meta
            :errors
            :provide="{ editingSections: editingGroups, canAddSections: isMultisite }"
        />

        <DocsCallout :topic="__('Multi-Site')" url="multi-site" />
    </div>
</template>
