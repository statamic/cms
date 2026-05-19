<script setup lang="ts">
import { Icon } from '@ui';
import Section from './Section.vue';
import { computed, onMounted, ref } from 'vue';
import { injectBuilderContext, InspectorType } from '@/pages/forms/Builder.vue';
import { useDragAndDrop } from './use-drag-and-drop';
import { uniqid } from '@/bootstrap/globals.js';

const { inspecting, inspectorType, inspect } = injectBuilderContext();

const props = defineProps<{
    page: object;
    pageIndex: number;
    totalPages: number;
}>();

const inspectPage = () => inspect(InspectorType.Page, props.page);
const isEditing = computed(() => inspectorType.value === InspectorType.Page && inspecting.value?._id === props.page._id);
const sectionRefs = ref({});
const sections = computed(() => props.page.sections);
const canDeleteSection = computed(() => sections.value.length > 1);

const addSection = (atIndex, fields = []) => {
    const section = {
        _id: uniqid(),
        collapsed: false,
        title: __('Section'),
        fields,
    };

    props.page.sections.splice(atIndex, 0, section);

    return section;
};

const addSectionAt = (sourceSectionId, fieldIndex) => {
    const sourceSection = sections.value.find((s) => s._id === sourceSectionId);
    if (!sourceSection) return;

    const sourceSectionIndex = sections.value.indexOf(sourceSection);
    const movedFields = sourceSection.fields.splice(fieldIndex);

    addSection(sourceSectionIndex + 1, movedFields);
};

const sectionDeleted = (sectionId) => {
    props.page.sections = props.page.sections.filter(section => section._id !== sectionId);
};

const addField = (sectionId, fieldtypeHandle, atIndex) => {
    sectionRefs.value[sectionId]?.addField(fieldtypeHandle, atIndex);
};

const moveField = (fromSectionId, toSectionId, oldIndex, newIndex) => {
    const fromSection = sections.value.find((s) => s._id === fromSectionId);
    const toSection = sections.value.find((s) => s._id === toSectionId);
    if (!fromSection || !toSection) return;

    const [field] = fromSection.fields.splice(oldIndex, 1);

    toSection.fields.splice(newIndex, 0, field);
};

useDragAndDrop({
    sections,
    onSectionAdded: addSection,
    onSectionAddedWithinSection: addSectionAt,
    onFieldAdded: addField,
    onFieldMoved: moveField,
});

onMounted(() => {
    if (sections.value.length === 0) {
        addSection(0);
    }
});
</script>

<template>
    <div :data-form-page="page._id">
        <div
            :id="`form-page-${page._id}`"
            class="mx-auto max-w-5xl max-[600px]:px-5 px-5.75 sm:px-6.25 mb-4 -mt-2"
            role="button"
            tabindex="0"
            :aria-label="page.title ? __(page.title) : __('Page :current of :total', { current: pageIndex + 1, total: totalPages })"
            data-form-page-label
            @click="inspectPage"
            @keydown.enter.prevent="inspectPage"
            @keydown.space.prevent="inspectPage"
        >
            <div class="flex items-center gap-4 cursor-pointer">
                <div class="flex items-center gap-2 flex-1">
                    <div class="h-px min-w-0 flex-1 bg-gray-200 dark:bg-gray-700" aria-hidden="true" />
<!--                    <span v-tooltip="__('Logic attached')">-->
<!--                        <Icon data-logic-attached name="logic-tree" class="size-3.5! shrink-0 text-gray-400 dark:text-gray-600" aria-hidden="true" />-->
<!--                    </span>-->
                </div>
                <div
                    class="flex shrink-0 items-center gap-2 rounded-xl border border-dashed border-gray-300 px-3.5 py-2 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-200 scroll-mt-[7rem]"
                    :data-editing-item="isEditing ? '' : undefined"
                    :class="isEditing ? 'bg-blue-50 border-blue-400! dark:bg-blue-950 dark:border-blue-700!' : ''"
                >
                    <Icon name="page" class="size-4 shrink-0 text-gray-500 dark:text-gray-400" aria-hidden="true" />
                    {{ page.title ? __(page.title) : __('Page :current of :total', { current: pageIndex + 1, total: totalPages }) }}
                </div>
                <div class="h-px min-w-0 flex-1 bg-gray-200 dark:bg-gray-700" aria-hidden="true" />
            </div>
        </div>

        <div class="section-gap-drop-zone mx-auto max-w-5xl h-6 flex items-center" data-section-gap-index="0" />

        <template v-for="(section, sectionIndex) in sections" :key="section._id">
            <Section
                :ref="(el) => sectionRefs[section._id] = el"
                :section
                :can-delete-section
                @deleted="sectionDeleted"
            />

            <div class="section-gap-drop-zone mx-auto max-w-5xl h-14 -mt-8 flex items-center" :data-section-gap-index="sectionIndex + 1" />
        </template>
    </div>
</template>
