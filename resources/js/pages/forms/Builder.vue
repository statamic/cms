<script lang="ts">
import createContext from '@/util/createContext.js';

export enum InspectorType {
    Page = 'page',
    Section = 'section',
    Field = 'field',
    Action = 'action',
    FieldtypeHint = 'fieldtype_hint',
}

export enum FieldView {
    Expanded = 'expanded',
    Collapsed = 'collapsed',
}

export const [injectBuilderContext, provideBuilderContext] = createContext('FormBuilder');
</script>

<script setup lang="ts">
import Layout from '@/pages/layout/Layout.vue';
import PanelLayout from '@/pages/layout/PanelLayout.vue';
import FormsLayout from './Layout.vue';
import { Button, Header, Icon, StatusIndicator, ToggleGroup, ToggleItem } from '@ui';
import LayoutPanel from '@/pages/layout/LayoutPanel.vue';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import FieldtypeSelector from '@/components/forms/Builder/FieldtypeSelector.vue';
import Head from '@/pages/layout/Head.vue';
import FieldtypeHint from '@/components/forms/Builder/FieldtypeHint.vue';
import SectionInspector from '@/components/forms/Builder/SectionInspector.vue';
import FieldInspector from '@/components/forms/Builder/FieldInspector.vue';
import Page from '@/components/forms/Builder/Page.vue';
import PageInspector from '@/components/forms/Builder/PageInspector.vue';

defineOptions({ layout: [Layout, PanelLayout, FormsLayout] });

const props = defineProps<{
    form: object,
    formsProInstalled: boolean,
    fieldtypes: array,
}>();

// todo: the original value should come from a prop (initialFormFields)
const formFields = ref({
    pages: [
        {
            _id: 'page-1',
            title: null,
            instructions: null,
            sections: [],
        },
    ],
});

const inspecting = ref<object | null>(null);
const inspectorType = ref<InspectorType | null>(null);
const fieldView = ref<FieldView>(FieldView.Expanded);
const pages = computed(() => formFields.value.pages);
const allSections = computed(() => pages.value.flatMap(page => page.sections));
const shouldShowViewSelector = computed(() => allSections.value.some(section => section.fields.length > 0));
const fieldCount = computed(() => allSections.value.flatMap(section => section.fields).length);

const inspect = (type: string | null, data: any = null) => {
    inspecting.value = data;
    inspectorType.value = type;
};

const clearInspector = () => inspect(null);

provideBuilderContext({
    form: props.form,
    formsProInstalled: props.formsProInstalled,
    fieldtypes: props.fieldtypes,
    fieldView,
    pages,
    inspecting,
    inspectorType,
    inspect,
    clearInspector,
});

const onEscape = (event: KeyboardEvent) => {
    if (event.key === 'Escape' && inspecting.value) {
        clearInspector();
    }
};

onMounted(() => document.addEventListener('keydown', onEscape));
onUnmounted(() => document.removeEventListener('keydown', onEscape));
</script>

<template>
    <Head :title="[form.title, __('Forms')]" />

    <Teleport to="#form-layout-actions">
        <Button variant="primary" :aria-label="__('Save')">
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

        <Page
            v-for="(page, index) in pages"
            :key="page._id"
            :page="page"
            :page-index="index"
        />

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
        <PageInspector v-if="inspectorType === InspectorType.Page" />
        <SectionInspector v-if="inspectorType === InspectorType.Section" />
        <FieldInspector v-if="inspectorType === InspectorType.Field" />
        <FieldtypeHint v-if="inspectorType === InspectorType.FieldtypeHint" />
    </LayoutPanel>
</template>
