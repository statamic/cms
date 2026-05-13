<script setup lang="ts">
import Layout from '@/pages/layout/Layout.vue';
import PanelLayout from '@/pages/layout/PanelLayout.vue';
import FormsLayout from './Layout.vue';
import { Button, Header, Icon, StatusIndicator, ToggleGroup, ToggleItem } from '@ui';
import LayoutPanel from '@/pages/layout/LayoutPanel.vue';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import FieldtypeSelector from '@/components/forms/Builder/FieldtypeSelector.vue';
import Section from '@/components/forms/Builder/Section.vue';
import { useDragAndDrop } from '@/components/forms/Builder/use-drag-and-drop';
import { uniqid } from '@/bootstrap/globals.js';
import Head from '@/pages/layout/Head.vue';
import FieldtypeHint from '@/components/forms/Builder/FieldtypeHint.vue';

defineOptions({ layout: [Layout, PanelLayout, FormsLayout] });

const props = defineProps<{
    form: Object,
    fieldtypes: Array,
}>();

enum FieldView {
    Expanded = 'expanded',
    Collapsed = 'collapsed',
}

// todo: the original value should come from a prop (initialFormFields)
const formFields = ref({
    sections: [
        {
            _id: 'abc',
            collapsed: false,
            title: 'Section 1',
            fields: [],
            values: {},
            meta: {},
        },
    ],
});

const inspecting = ref(null);
const inspectorType = ref(null);
const editingField = ref(null);
const fieldView = ref<FieldView>(FieldView.Expanded);
const sectionRefs = ref({});
const sections = computed(() => formFields.value.sections);
const shouldShowViewSelector = computed(() => sections.value.some(section => section.fields.length > 0));
const fieldsCount = computed(() => sections.value.flatMap(section => section.fields).length);
const canDeleteSection = computed(() => sections.value.length > 1);

const addSection = (atIndex, fields = []) => {
    const section = {
        _id: uniqid(),
        collapsed: false,
        title: __('Section'),
        fields,
        values: {},
        meta: {},
    };

    formFields.value.sections.splice(atIndex, 0, section);

    return section;
};

const addSectionAt = (sourceSectionId, fieldIndex) => {
    const sourceSection = sections.value.find((s) => s._id === sourceSectionId);
    if (!sourceSection) return;

    const sourceSectionIndex = sections.value.indexOf(sourceSection);
    const movedFields = sourceSection.fields.splice(fieldIndex);
    const newSection = addSection(sourceSectionIndex + 1, movedFields);

    movedFields.forEach((field) => {
        newSection.values[field.handle] = sourceSection.values[field.handle];
        newSection.meta[field.handle] = sourceSection.meta[field.handle];
        delete sourceSection.values[field.handle];
        delete sourceSection.meta[field.handle];
    });
};

const sectionDeleted = (sectionId) => formFields.value['sections'] = formFields.value['sections'].filter(section => section._id !== sectionId);

const addField = (sectionId, fieldtypeHandle, atIndex) => {
    sectionRefs.value[sectionId]?.addField(fieldtypeHandle, atIndex);
};

const moveField = (fromSectionId, toSectionId, oldIndex, newIndex) => {
    const fromSection = sections.value.find((s) => s._id === fromSectionId);
    const toSection = sections.value.find((s) => s._id === toSectionId);
    if (!fromSection || !toSection) return;

    const [field] = fromSection.fields.splice(oldIndex, 1);

    if (fromSectionId !== toSectionId) {
        toSection.values[field.handle] = fromSection.values[field.handle];
        toSection.meta[field.handle] = fromSection.meta[field.handle];
        delete fromSection.values[field.handle];
        delete fromSection.meta[field.handle];
    }

    toSection.fields.splice(newIndex, 0, field);
};

useDragAndDrop({
    sections,
    onSectionAdded: addSection,
    onSectionAddedWithinSection: addSectionAt,
    onFieldAdded: addField,
    onFieldMoved: moveField,
});

const inspect = (type, data) => {
    inspecting.value = data;
    inspectorType.value = type;

    // todo: remove this when we do away with the editingField state
    editingField.value = null;
};

const onEscape = (event) => {
    if (event.key === 'Escape' && editingField.value) {
        editingField.value = null;
    }
};

onMounted(() => document.addEventListener('keydown', onEscape));
onUnmounted(() => document.removeEventListener('keydown', onEscape));

// TODO: Refactor everything below this line
const formPageTotal = 1;
const inspectorTarget = ref('field');
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
        <FieldtypeSelector
            :fieldtypes
            :field-view
            @inspect="inspect('fieldtype_hint', $event)"
        />
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

<!--        <div-->
<!--            id="form-page-1"-->
<!--            class="mx-auto max-w-5xl max-[600px]:px-5 px-5.75 sm:px-6.25 mb-4 -mt-2"-->
<!--            role="button"-->
<!--            tabindex="0"-->
<!--            :aria-label="__('Page :current of :total', { current: 1, total: formPageTotal })"-->
<!--            data-form-page-label-->
<!--            data-form-page="1"-->
<!--            @click="inspectorTarget = 'page_1'"-->
<!--            @keydown.enter.prevent="inspectorTarget = 'page_1'"-->
<!--            @keydown.space.prevent="inspectorTarget = 'page_1'"-->
<!--        >-->
<!--            <div class="flex items-center gap-4 cursor-pointer">-->
<!--                <div class="flex items-center gap-2 flex-1">-->
<!--                    <div class="h-px min-w-0 flex-1 bg-gray-200 dark:bg-gray-700" aria-hidden="true" />-->
<!--                    <span v-tooltip="__('Logic attached')">-->
<!--                        <Icon data-logic-attached name="logic-tree" class="size-3.5! shrink-0 text-gray-400 dark:text-gray-600" aria-hidden="true" />-->
<!--                    </span>-->
<!--                </div>-->
<!--                <div-->
<!--                    class="flex shrink-0 items-center gap-2 rounded-xl border border-dashed border-gray-300 px-3.5 py-2 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-200 scroll-mt-[7rem]"-->
<!--                    :data-editing-item="inspectorTarget === 'page_1' ? '' : undefined"-->
<!--                    :class="inspectorTarget === 'page_1' ? 'bg-blue-50 border-blue-400! dark:bg-blue-950 dark:border-blue-700!' : ''"-->
<!--                >-->
<!--                    <Icon name="page" class="size-4 shrink-0 text-gray-500 dark:text-gray-400" aria-hidden="true" />-->
<!--                    {{ __('Page :current of :total', { current: 1, total: formPageTotal }) }}-->
<!--                </div>-->
<!--                <div class="h-px min-w-0 flex-1 bg-gray-200 dark:bg-gray-700" aria-hidden="true" />-->
<!--            </div>-->
<!--        </div>-->

        <div class="section-gap-drop-zone mx-auto max-w-5xl h-6 flex items-center" data-section-gap-index="0" />

        <template v-for="(section, sectionIndex) in sections" :key="section._id">
            <Section
                :ref="(el) => sectionRefs[section._id] = el"
                :section
                :fieldtypes
                :field-view
                :can-delete-section
                v-model:editing-field="editingField"
                @deleted="sectionDeleted"
            />

            <div class="section-gap-drop-zone mx-auto max-w-5xl h-14 -mt-8 flex items-center" :data-section-gap-index="sectionIndex + 1" />
        </template>

        <p
            v-if="fieldView === FieldView.Collapsed"
            class="mx-auto text-center max-w-5xl max-[600px]:p-5 px-5.75 sm:px-6.25 mb-5 text-sm text-gray-600 dark:text-gray-300"
        >
            <strong>{{ fieldsCount }}</strong> {{ __n('field on this form|fields on this form', fieldsCount) }}
        </p>
    </div>

    <Button
        class="min-[1000px]:hidden sticky top-3 mt-3 z-(--z-index-above) sm:translate-x-3 md:translate-x-9 mb-5 col-start-3 row-start-1"
        popovertarget="popover-right-panel"
        :text="__('Settings')"
        icon="cog"
    />

    <LayoutPanel side="right">
        <FieldtypeHint
            v-if="inspectorType === 'fieldtype_hint'"
            :fieldtype="inspecting"
        />

        <!-- TODO: Wire up field/page/action settings -->
    </LayoutPanel>
</template>
