<script setup lang="ts">
import Layout from '@/pages/layout/Layout.vue';
import PanelLayout from '@/pages/layout/PanelLayout.vue';
import FormsLayout from './Layout.vue';
import { Button, Header, Icon, ToggleGroup, ToggleItem } from '@ui';
import FormStatusIndicator from '@/components/forms/FormStatusIndicator.vue';
import FieldNumberingToggle from '@/components/forms/FieldNumberingToggle.vue';
import LogicList from '@/components/forms/logic/LogicList.vue';
import LogicTree, { TreeDensity, SelectionType, type Selection } from '@/components/forms/logic/LogicTree.vue';
import { provideBuilderContext } from '@/pages/forms/Builder.vue';
import { collectsValue } from '@/components/forms/builder/categories';
import Head from '@/pages/layout/Head.vue';
import { useFieldNumberingPreference } from '@/composables/forms/field-numbering';
import { computed, nextTick, onMounted, onUnmounted, provide, ref, watch } from 'vue';
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

const errors = ref({});
const saving = ref(false);
const saveBinding = ref(null);
const formFields = ref(props.formFields);
const selected = ref<Selection>(null);
const view = ref<View>(preferences.get('forms.logic.view', View.List));
const treeDensity = ref<TreeDensity>(preferences.get('forms.logic.tree.density', TreeDensity.Compressed));

provide('fieldtypes', props.fieldtypes);

const pages = computed({
    get: () => formFields.value.pages,
    set: (pages) => (formFields.value.pages = pages),
});

const fieldCategory = (field) => props.fieldtypes.find((fieldtype) => fieldtype.handle === field.fieldtype)?.categories?.[0] ?? 'other';

const findField = (id) => pages.value
    .flatMap((page) => page.sections)
    .flatMap((section) => section.fields)
    .find((field) => field._id === id) ?? null;

const flattenedFields = computed(() => pages.value.flatMap((page, pageIndex) =>
    page.sections.flatMap((section) => section.fields
        .filter((field) => field.type !== 'import')
        .map((field) => ({ field, pageIndex })),
    ),
));

const selectedPage = computed(() => selected.value?.type === SelectionType.Page ? pages.value.find((page) => page._id === selected.value.id) ?? null : null);
const selectedField = computed(() => selected.value?.type === SelectionType.Field ? findField(selected.value.id) : null);

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

const suggestableFields = computed(() => flattenedFields.value
    .filter(({ field }) => collectsValue(fieldCategory(field)))
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

const inspecting = computed(() => selectedField.value ?? selectedPage.value);

const avoidTrackingDirtyState = ref(false);

const dirty = () => {
    if (!avoidTrackingDirtyState.value) Statamic.$dirty.add('form-logic');
};

const clearDirtyState = () => Statamic.$dirty.remove('form-logic');

const withoutDirtying = (callback: () => void) => {
    const previous = avoidTrackingDirtyState.value;
    avoidTrackingDirtyState.value = true;
    callback();
    nextTick(() => avoidTrackingDirtyState.value = previous);
};

const deletePage = (pageId: string) => {
    if (pages.value.length <= 1) return;

    const pageIndex = pages.value.findIndex((page) => page._id === pageId);
    if (pageIndex === -1) return;

    formFields.value.pages.splice(pageIndex, 1);
    selected.value = null;
    dirty();
};

provideBuilderContext({
    deletePage,
    dirty,
    errors,
    fieldtypes: props.fieldtypes,
    form: props.form,
    inspecting,
    pages,
    showFieldDirection: false,
    withoutDirtying,
});

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
watch(view, (view: View) => {
    preferences.set('forms.logic.view', view);

    if (view !== View.Tree) selected.value = null;
});
watch(treeDensity, (density: TreeDensity) => preferences.set('forms.logic.tree.density', density));

onMounted(() => {
    saveBinding.value = keys.bindGlobal(['return', 'mod+s'], (e) => {
        e.preventDefault();
        save();
    });
});

onUnmounted(() => {
    clearDirtyState();
    saveBinding.value?.destroy();
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
                <FormStatusIndicator :status="form.status" />
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

        <LogicList
            v-if="view === View.List"
            v-model:pages="pages"
            :suggestable-fields
            :fieldtypes
        />
    </div>

    <LogicTree
        v-if="view === View.Tree"
        v-model:pages="pages"
        :density="treeDensity"
        :selected
        @select="selected = $event"
    />
</template>
