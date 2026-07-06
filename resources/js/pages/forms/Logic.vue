<script setup lang="ts">
import Layout from '@/pages/layout/Layout.vue';
import PanelLayout from '@/pages/layout/PanelLayout.vue';
import FormsLayout from './Layout.vue';
import { Button, Header, Icon, SplitterGroup, SplitterPanel, SplitterResizeHandle, StatusIndicator, ToggleGroup, ToggleItem } from '@ui';
import FieldNumberingToggle from '@/components/forms/FieldNumberingToggle.vue';
import FieldLogic from '@/components/forms/logic/FieldLogic.vue';
import PageLogic from '@/components/forms/logic/PageLogic.vue';
import LogicTree, { TreeDensity, SelectionType } from '../../components/forms/logic/LogicTree.vue';
import FieldLogicPanel from '../../components/forms/logic/FieldLogicPanel.vue';
import PageLogicPanel from '../../components/forms/logic/PageLogicPanel.vue';
import Head from '@/pages/layout/Head.vue';
import { useFieldNumberingPreference } from '@/composables/forms/field-numbering';
import { computed, onMounted, onUnmounted, provide, ref, watch } from 'vue';
import { keys, preferences } from '@api';
import axios from 'axios';

defineOptions({ layout: [Layout, PanelLayout, FormsLayout] });

enum View {
    List = 'list',
    Tree = 'tree',
}

const props = defineProps({
    form: Object,
    formFields: Object,
    action: String,
    fieldtypes: Array,
});

const LOGIC_KEYS = ['hidden', 'if', 'unless', 'if_any', 'unless_any', 'always_save'];

const formFields = ref(props.formFields);
const saving = ref(false);
const saveBinding = ref(null);
const escBinding = ref(null);
const errors = ref({});
const selected = ref<{type: SelectionType, id: string}>(null);
const view = ref<View>(preferences.get('forms.logic.view', View.List));
const treeDensity = ref<TreeDensity>(preferences.get('forms.logic.tree.density', TreeDensity.Compressed));

provide('fieldtypes', props.fieldtypes);

const pages = computed({
    get: () => formFields.value.pages,
    set: (pages) => (formFields.value.pages = pages),
});

const fieldCategory = (field) => props.fieldtypes.find((fieldtype) => fieldtype.handle === field.fieldtype)?.categories?.[0] ?? 'other';

const allFields = () => pages.value.flatMap((page) => page.sections).flatMap((section) => section.fields);
const findField = (id) => allFields().find((field) => field._id === id) ?? null;
const findFieldByHandle = (handle) => allFields().find((field) => field.handle === handle) ?? null;

// The tree and save work off the nested structure directly, but the list view,
// numbering and suggestable list want a flat projection of the editable fields.
const flatFields = computed(() => pages.value.flatMap((page, pageIndex) =>
    page.sections.flatMap((section) => section.fields
        .filter((field) => field.type !== 'import')
        .map((field) => ({ field, pageIndex })),
    ),
));

const { showFieldNumbers } = useFieldNumberingPreference();
const fieldNumbers = computed(() => {
    if (!showFieldNumbers.value) return new Map();

    let number = 0;
    const map = new Map();

    pages.value.forEach((page) => page.sections.forEach((section) => section.fields.forEach((field) => {
        if (field.type === 'import') {
            Object.keys(field.previews ?? {}).forEach((handle) => map.set(`${field._id}:${handle}`, ++number));

            return;
        }

        if (field.config?.hidden || fieldCategory(field) === 'information') return;

        map.set(field.handle, ++number);
    })));

    return map;
});
provide('fieldNumbers', fieldNumbers);

const suggestableFields = computed(() => flatFields.value
    .filter(({ field }) => !['information', 'structure'].includes(fieldCategory(field)))
    .map(({ field, pageIndex }) => ({
        handle: field.handle,
        icon: field.icon,
        category: fieldCategory(field),
        pageIndex,
        config: {
            type: field.fieldtype,
            display: field.config?.display,
            options: field.config?.options,
        },
    })),
);

// Write a field's logic conditions into its config, keeping reference fields'
// `config_overrides` in sync so the transformer persists them.
const writeConditions = (field, conditions) => {
    const config = { ...field.config };

    // hidden + always_save are booleans that may be absent from a partial update,
    // so fall back to the existing value; the rest are replaced outright.
    const merged = {
        hidden: conditions.hidden ?? config.hidden ?? false,
        if: conditions.if || null,
        unless: conditions.unless || null,
        if_any: conditions.if_any || null,
        unless_any: conditions.unless_any || null,
        always_save: conditions.always_save ?? config.always_save ?? false,
    };

    LOGIC_KEYS.forEach((key) => {
        if (merged[key]) {
            config[key] = merged[key];
        } else {
            delete config[key];
        }
    });

    field.config = config;

    if (field.type === 'reference') {
        const overrides = new Set((field.config_overrides ?? []).filter((key) => !LOGIC_KEYS.includes(key)));
        LOGIC_KEYS.forEach((key) => key in config && overrides.add(key));
        field.config_overrides = [...overrides];
    }
};

// The list view edits a flat array of fields; sync any condition changes back
// onto the matching nested field.
const fields = computed({
    get: () => flatFields.value.map(({ field, pageIndex }) => ({
        _id: field.handle,
        handle: field.handle,
        display: field.config?.display ?? field.handle,
        icon: field.icon,
        category: fieldCategory(field),
        fieldtype: field.fieldtype,
        page_index: pageIndex,
        hidden: field.config?.hidden ?? false,
        if: field.config?.if ?? null,
        unless: field.config?.unless ?? null,
        if_any: field.config?.if_any ?? null,
        unless_any: field.config?.unless_any ?? null,
        always_save: field.config?.always_save ?? false,
        options: field.config?.options,
    })),
    set: (fields) => fields.forEach((field) => {
        const node = findFieldByHandle(field.handle);
        if (node) writeConditions(node, field);
    }),
});

const selectedPage = computed(() => selected.value?.type === SelectionType.Page ? pages.value.find((page) => page._id === selected.value.id) ?? null : null);
const selectedField = computed(() => selected.value?.type === SelectionType.Field ? findField(selected.value.id) : null);
const selectedFieldPageIndex = computed(() => flatFields.value.find(({ field }) => field._id === selected.value?.id)?.pageIndex ?? 0);

const dirty = () => Statamic.$dirty.add('form-logic');
const clearDirtyState = () => Statamic.$dirty.remove('form-logic');

const save = () => {
    if (saving.value) return;

    errors.value = {};
    saving.value = true;

    axios.patch(props.action, { pages: pages.value })
        .then((response) => {
            clearDirtyState();
            Statamic.$toast.success(__('Saved'));
        })
        .catch((e) => {
            if (e.response?.status === 422) {
                errors.value = e.response.data.errors;
                Statamic.$toast.error(e.response.data.message);
            } else {
                Statamic.$toast.error(__('Something went wrong'));
            }
        })
        .finally(() => saving.value = false);
};

watch(formFields, dirty, { deep: true });
watch(view, (view: View) => preferences.set('forms.logic.view', view));
watch(treeDensity, (density: TreeDensity) => preferences.set('forms.logic.tree.density', density));

onMounted(() => {
    saveBinding.value = keys.bindGlobal(['return', 'mod+s'], (e) => {
        e.preventDefault();
        save();
    });

    escBinding.value = keys.bindGlobal(['esc'], () => (selected.value = null));
});

onUnmounted(() => {
    clearDirtyState();
    saveBinding.value?.destroy();
    escBinding.value?.destroy();
});
</script>

<template>
    <Head :title="[__('Logic'), __(form.title), __('Forms')]" />

    <Teleport to="#form-layout-actions">
        <Button variant="primary" :aria-label="__('Save')" :disabled="saving" @click="save">
            <Icon name="save" class="sm:hidden" />
            <span class="hidden sm:inline">{{ __('Save') }}</span>
        </Button>
    </Teleport>

    <div class="mx-auto w-full max-w-5xl min-w-0 shrink-0">
        <Header class="mb-2 md:py-9">
            <template #title>
                <StatusIndicator status="published" />
                {{ __(form.title) }}
            </template>
            <template #actions>
                <div class="flex items-center gap-2.5">
                    <FieldNumberingToggle />
                    <ToggleGroup v-if="view === View.Tree" v-model="treeDensity" size="xs">
                        <ToggleItem
                            :value="TreeDensity.Expanded"
                            icon="expand"
                            :aria-label="__('Expanded view')"
                            v-tooltip="__('Expanded view')"
                        />
                        <ToggleItem
                            :value="TreeDensity.Compressed"
                            icon="collapse"
                            :aria-label="__('Collapsed view')"
                            v-tooltip="__('Collapsed view')"
                        />
                    </ToggleGroup>
                    <ToggleGroup v-model="view" size="sm">
                        <ToggleItem :value="View.List" icon="layout-list" :label="__('List')" />
                        <ToggleItem :value="View.Tree" icon="logic-tree" :label="__('Tree')" />
                    </ToggleGroup>
                </div>
            </template>
        </Header>

        <template v-if="view === View.List">
            <PageLogic
                v-if="pages.length > 1"
                class="mb-6"
                v-model:pages="pages"
                :suggestable-fields
                :fieldtypes
            />

            <FieldLogic
                v-model:fields="fields"
                :suggestable-fields
                :fieldtypes
            />
        </template>
    </div>

    <div v-if="view === View.Tree" class="st-full-bleed-content flex flex-col flex-1 min-h-0">
        <div class="flex-1 min-h-0 overflow-hidden pb-2!">
            <SplitterGroup direction="vertical">
                <SplitterPanel>
                    <div class="h-full overflow-y-auto">
                        <LogicTree
                            v-model:pages="pages"
                            :density="treeDensity"
                            :selected
                            @select="selected = $event"
                        />
                    </div>
                </SplitterPanel>

                <template v-if="selected">
                    <SplitterResizeHandle class="mx-auto my-1.5 h-1.5 w-16 shrink-0 rounded-full bg-gray-300 transition-colors hover:bg-gray-400 dark:bg-gray-700 dark:hover:bg-gray-600" />

                    <SplitterPanel
                        :default-size="35"
                        :min-size="20"
                        collapsible
                        class="rounded-lg border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900"
                    >
                        <div class="h-full overflow-y-auto">
                            <div class="mx-auto max-w-3xl p-6">
                                <FieldLogicPanel
                                    v-if="selectedField"
                                    :field="selectedField"
                                    :page-index="selectedFieldPageIndex"
                                    :suggestable-fields="suggestableFields"
                                    :fieldtypes
                                    @update:conditions="writeConditions(selectedField, $event)"
                                />

                                <PageLogicPanel
                                    v-else-if="selectedPage"
                                    :page="selectedPage"
                                    v-model:pages="pages"
                                    :suggestable-fields="suggestableFields"
                                    :fieldtypes
                                />
                            </div>
                        </div>
                    </SplitterPanel>
                </template>
            </SplitterGroup>
        </div>
    </div>
</template>
