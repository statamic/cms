<script lang="ts">
import createContext from '@/util/createContext';

export enum InspectorType {
    Page = 'page',
    Section = 'section',
    Field = 'field',
    Action = 'action',
    FieldtypeHint = 'fieldtype_hint',
    LinkFields = 'link_fields',
    FieldsetImport = 'import',
}

export enum FieldView {
    Expanded = 'expanded',
    Collapsed = 'collapsed',
}

export const [injectBuilderContext, provideBuilderContext] = createContext('FormBuilder');
</script>

<script setup lang="ts">
import { Button, Header, Icon, StatusIndicator, ToggleGroup, ToggleItem } from '@ui';
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';
import axios from 'axios';
import FormsLayout from './Layout.vue';
import Head from '@/pages/layout/Head.vue';
import Layout from '@/pages/layout/Layout.vue';
import LayoutPanel from '@/pages/layout/LayoutPanel.vue';
import PanelLayout from '@/pages/layout/PanelLayout.vue';
import ActionInspector from '@/components/forms/Builder/ActionInspector.vue';
import FieldInspector from '@/components/forms/Builder/FieldInspector.vue';
import FieldsetInspector from '@/components/forms/Builder/FieldsetInspector.vue';
import FieldtypeHint from '@/components/forms/Builder/FieldtypeHint.vue';
import FieldtypeSelector from '@/components/forms/Builder/FieldtypeSelector.vue';
import LinkFieldsInspector from '@/components/forms/Builder/LinkFieldsInspector.vue';
import Page from '@/components/forms/Builder/Page.vue';
import PageInspector from '@/components/forms/Builder/PageInspector.vue';
import SectionInspector from '@/components/forms/Builder/SectionInspector.vue';
import { useFieldtypeDraggable } from '@/components/forms/Builder/use-drag-and-drop';
import { __, uniqid } from '@/bootstrap/globals';

defineOptions({ layout: [Layout, PanelLayout, FormsLayout] });

const props = defineProps<{
    form: object,
    initialFormFields: object,
    formsProInstalled: boolean,
    fieldtypes: array,
    action: string,
}>();

const formFields = ref<object>(props.initialFormFields);
const saving = ref<boolean>(false);
const inspecting = ref<object | null>(null);
const inspectorType = ref<InspectorType | null>(null);
const fieldView = ref<FieldView>(FieldView.Expanded);
const pages = computed(() => formFields.value.pages);
const allSections = computed(() => pages.value.flatMap(page => page.sections));
const shouldShowViewSelector = computed(() => allSections.value.some(section => section.fields.length > 0));
const fieldCount = computed(() => allSections.value.flatMap(section => section.fields).length);
const errors = ref<Record<string, Record<string, string[]>>>({});

const inspect = (type: InspectorType, data: object): void => {
    inspecting.value = data;
    inspectorType.value = type;
};

const clearInspector = (): void => {
    inspecting.value = null;
    inspectorType.value = null;
};

const addPage = (atIndex: number | null = null, sections = []) => {
    const page = {
        _id: uniqid(),
        display: null,
        instructions: null,
        button_label: null,
        previous_page_label: null,
        sections,
    };

    formFields.value.pages.splice(atIndex ?? formFields.value.pages.length, 0, page);

    dirty();

    return page;
};

const addPageAt = (pageId: string, sectionIndex: number, fieldIndex: number | null = null) => {
    const pageIndex = pages.value.findIndex(p => p._id === pageId);
    if (pageIndex === -1) return;

    const page = pages.value[pageIndex];
    const sections = page.sections;

    let sectionsForNewPage = [];

    if (fieldIndex !== null && sectionIndex < sections.length) {
        const section = sections[sectionIndex];
        const remainingFields = section.fields.splice(fieldIndex);

        if (remainingFields.length > 0) {
            sectionsForNewPage.push({
                _id: uniqid(),
                collapsed: false,
                display: __('Section'),
                fields: remainingFields,
            });
        }

        sectionsForNewPage.push(...sections.splice(sectionIndex + 1));
    } else {
        sectionsForNewPage = sections.splice(sectionIndex);
    }

    if (sectionsForNewPage.length === 0) {
        sectionsForNewPage.push({
            _id: uniqid(),
            collapsed: false,
            display: __('Section'),
            fields: [],
        });
    }

    const newPage = addPage(pageIndex + 1, sectionsForNewPage);

    inspect(InspectorType.Page, newPage);
};

const addSection = (pageId: string, atIndex: number, fields = []) => {
    const page = pages.value.find((p) => p._id === pageId);
    if (!page) return;

    const section = {
        _id: uniqid(),
        collapsed: false,
        display: __('Section'),
        fields,
    };

    page.sections.splice(atIndex, 0, section);

    dirty();

    return section;
};

const addSectionAt = (pageId: string, sourceSectionId: string, fieldIndex: number) => {
    const page = pages.value.find((p) => p._id === pageId);
    if (!page) return;

    const sourceSection = page.sections.find((s) => s._id === sourceSectionId);
    if (!sourceSection) return;

    const sourceSectionIndex = page.sections.indexOf(sourceSection);
    const movedFields = sourceSection.fields.splice(fieldIndex);

    addSection(pageId, sourceSectionIndex + 1, movedFields);
};

const addField = (pageId: string, sectionId: string, fieldtypeHandle: string, atIndex: number) => {
    const page = pages.value.find((p) => p._id === pageId);
    if (!page) return;

    const section = page.sections.find((s) => s._id === sectionId);
    if (!section) return;

    const fieldtype = props.fieldtypes.find((f) => f.handle === fieldtypeHandle);
    if (!fieldtype) return;

    const handle = uniqid();

    const field = {
        _id: handle,
        config: {
            type: fieldtypeHandle,
            display: __(fieldtype.title),
            hidden: false,
        },
        fieldtype: fieldtypeHandle,
        handle,
        icon: fieldtype?.icon || 'fieldtype-generic',
        type: 'inline',
        preview: {
            config: { ...fieldtype.preview?.config, handle },
            value: fieldtype.preview?.value,
            meta: fieldtype.preview?.meta,
        },
    };

    section.fields.splice(atIndex, 0, field);

    inspect(InspectorType.Field, field);

    dirty();
};

const onFieldtypeDrop = ({ pageId, fieldtypeHandle, sectionId, sectionIndex, fieldIndex }) => {
    if (fieldtypeHandle === 'page_break') {
        addPageAt(pageId, sectionIndex, fieldIndex);
        return;
    }

    if (fieldtypeHandle === 'section') {
        fieldIndex !== null && sectionId
            ? addSectionAt(pageId, sectionId, fieldIndex)
            : addSection(pageId, sectionIndex);

        return;
    }

    if (fieldtypeHandle === 'fieldset') {
        const page = pages.value.find((p) => p._id === pageId);
        if (!page) return;

        const section = page.sections.find((s) => s._id === sectionId);
        if (!section) return;

        let fieldsetImport = {
            _id: uniqid(),
            type: 'link_fields',
        };

        section.fields.splice(fieldIndex, 0, fieldsetImport);

        inspect(InspectorType.LinkFields, fieldsetImport);

        return;
    }

    addField(pageId, sectionId, fieldtypeHandle, fieldIndex);
};

useFieldtypeDraggable({
    pages,
    onDrop: onFieldtypeDrop,
});

const avoidTrackingDirtyState = ref(false);
const trackingDirtyState = computed(() => !avoidTrackingDirtyState.value);
const isDirty = computed(() => Statamic.$dirty.has('form-builder'));

function dirty() {
    if (trackingDirtyState.value) Statamic.$dirty.add('form-builder');
}

function clearDirtyState() {
    Statamic.$dirty.remove('form-builder');
}

function withoutDirtying(callback: () => void) {
    const previous = avoidTrackingDirtyState.value;
    avoidTrackingDirtyState.value = true;
    callback();
    nextTick(() => avoidTrackingDirtyState.value = previous);
}

const save = () => {
    errors.value = {};
    saving.value = true;

    axios.patch(props.action, formFields.value)
        .then((response) => {
            clearDirtyState();
            Statamic.$toast.success(__('Saved'));
        })
        .catch((e) => {
            if (e.response?.status === 422) {
                errors.value = e.response.data.errors;
                Statamic.$toast.error(e.response.data.message);
            }
        })
        .finally(() => saving.value = false);
};

provideBuilderContext({
    addSection,
    clearInspector,
    dirty,
    errors,
    fieldtypes: props.fieldtypes,
    fieldView,
    form: props.form,
    formsProInstalled: props.formsProInstalled,
    inspect,
    inspecting,
    inspectorType,
    isDirty,
    pages,
    withoutDirtying,
});

const onEscape = (event: KeyboardEvent) => {
    if (event.key === 'Escape' && inspecting.value) {
        clearInspector();
    }
};

watch(saving, (saving) => Statamic.$progress.loading('form-builder', saving));

onMounted(() => {
    if (formFields.value.pages.length === 0) addPage();
    document.addEventListener('keydown', onEscape);
});

onUnmounted(() => {
    clearDirtyState();
    document.removeEventListener('keydown', onEscape);
});
</script>

<template>
    <Head :title="[form.title, __('Forms')]" />

    <Teleport to="#form-layout-actions">
        <Button variant="primary" :aria-label="__('Save')" @click="save">
            <Icon name="save" class="sm:hidden" />
            <span class="hidden sm:inline">{{ __('Save') }}</span>
        </Button>
    </Teleport>

    <Button
        class="min-[1000px]:hidden sticky top-3 mt-3 z-(--z-index-above) sm:-translate-x-3 md:-translate-x-9 col-start-1 row-start-1"
        popovertarget="popover-left-panel"
        :text="__('Form Builder')"
        icon="bar-sidebar-left-panel-open"
    />

    <LayoutPanel side="left">
        <FieldtypeSelector />
    </LayoutPanel>

    <div class="col-span-full row-start-1 max-[1000px]:pt-14">
        <Header class="mx-auto max-w-5xl">
            <template #title>
                <StatusIndicator status="published" />
                {{ form.title }}
            </template>
            <template #actions>
                <ToggleGroup v-if="shouldShowViewSelector" v-model="fieldView" size="xs">
                    <ToggleItem
                        :value="FieldView.Expanded"
                        icon="expand"
                        :aria-label="__('Expanded view')"
                        v-tooltip="__('Expanded view')"
                    />
                    <ToggleItem
                        :value="FieldView.Collapsed"
                        icon="collapse"
                        :aria-label="__('Collapsed view')"
                        v-tooltip="__('Collapsed view')"
                    />
                </ToggleGroup>
            </template>
        </Header>

        <Page v-for="page in pages" :key="page._id" :page />

        <p
            v-if="fieldView === FieldView.Collapsed"
            class="mx-auto text-center max-w-5xl max-[600px]:p-5 px-5.75 sm:px-6.25 mb-5 text-sm text-gray-600 dark:text-gray-300"
        >
            <strong>{{ fieldCount }}</strong> {{ __n('field on this form|fields on this form', fieldCount) }}
        </p>
    </div>

    <Button
        v-if="inspectorType"
        class="min-[1000px]:hidden sticky top-3 mt-3 z-(--z-index-above) sm:translate-x-3 md:translate-x-9 mb-5 col-start-3 row-start-1"
        popovertarget="popover-right-panel"
        :text="__('Settings')"
        icon="cog"
    />

    <LayoutPanel side="right">
        <ActionInspector v-if="inspectorType === InspectorType.Action" />
        <PageInspector v-if="inspectorType === InspectorType.Page" />
        <SectionInspector v-if="inspectorType === InspectorType.Section" />
        <FieldInspector v-if="inspectorType === InspectorType.Field" />
        <FieldtypeHint v-if="inspectorType === InspectorType.FieldtypeHint" />
        <LinkFieldsInspector v-if="inspectorType === InspectorType.LinkFields" />
        <FieldsetInspector v-if="inspectorType === InspectorType.FieldsetImport" />
    </LayoutPanel>
</template>
