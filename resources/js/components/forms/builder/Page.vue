<script setup lang="ts">
import { Button, Icon } from '@ui';
import { computed, onMounted, useTemplateRef } from 'vue';
import { injectBuilderContext, InspectorType } from '@/pages/forms/Builder.vue';
import { useSortable } from '../../../composables/forms/use-drag-and-drop';
import Section from './Section.vue';
import { __ } from '@/bootstrap/globals';

const { addSection, clearInspector, dirty, fieldView, formsProInstalled, inspect, inspecting, inspectorType, pages } = injectBuilderContext();

const props = defineProps<{
    page: object;
}>();

const inspectPage = () => inspect(InspectorType.Page, props.page);
const isInspectingPage = computed(() => inspectorType.value === InspectorType.Page && inspecting.value?._id === props.page._id);
const inspectAction = () => inspect(InspectorType.Action, props.page);
const isInspectingAction = computed(() => inspectorType.value === InspectorType.Action && inspecting.value?._id === props.page._id);
const container = useTemplateRef('container');
const sections = computed(() => props.page.sections);
const canDeleteSection = computed(() => sections.value.length > 1);
const isLastPage = computed(() => pages.value.findIndex((p) => p._id === props.page._id) === pages.value.length - 1);

const hasLogic = computed(() => {
    return (props.page.rules ?? []).some((rule) => {
        if (!rule.destination) {
            return false;
        }

        return (rule.conditions ?? []).some((condition) => condition.field && condition.value);
    });
});

const placeholderTitle = computed(() => {
    let pageIndex = pages.value.findIndex((p) => p._id === props.page._id);

    return __('Page :current of :total', { current: pageIndex + 1, total: pages.value.length });
});

const sectionDeleted = (sectionId) => {
    dirty();
    clearInspector();
    props.page.sections = props.page.sections.filter(section => section._id !== sectionId);
};

const moveField = (fromSectionId: string, toSectionId: string, oldIndex: number, newIndex: number) => {
    const fromSection = sections.value.find((s) => s._id === fromSectionId);
    const toSection = sections.value.find((s) => s._id === toSectionId);
    if (!fromSection || !toSection) return;

    const [field] = fromSection.fields.splice(oldIndex, 1);

    toSection.fields.splice(newIndex, 0, field);
};

useSortable({
    container,
    sections,
    fieldView,
    onFieldMoved: moveField,
});

onMounted(() => {
    if (sections.value.length === 0) {
        addSection(props.page._id, 0);
        clearInspector();
    }
});
</script>

<template>
    <div ref="container" :data-form-page="page._id">
        <div
            v-if="formsProInstalled"
            :id="`page-${page._id}`"
            class="mx-auto max-w-5xl max-[600px]:px-5 px-5.75 sm:px-6.25 mb-2 -mt-2"
            role="button"
            tabindex="0"
            :aria-label="page.display ? __(page.display) : placeholderTitle"
            data-form-page-label
            @click="inspectPage"
            @keydown.enter.prevent="inspectPage"
            @keydown.space.prevent="inspectPage"
        >
            <div class="flex items-center gap-4 cursor-pointer">
                <div class="h-px min-w-0 flex-1 bg-gray-200 dark:bg-gray-700" aria-hidden="true" />
                <div
                    class="flex shrink-0 items-center gap-2 rounded-xl border border-dashed border-gray-300 px-3.5 py-2 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-200 scroll-mt-[7rem]"
                    :data-editing-item="isInspectingPage ? '' : undefined"
                    :class="isInspectingPage ? 'bg-blue-50 border-blue-400! dark:bg-blue-950 dark:border-blue-500/75!' : ''"
                >
                    <Icon name="page" class="size-4 shrink-0 text-gray-500 dark:text-gray-400" aria-hidden="true" />
                    {{ page.display ? __(page.display) : placeholderTitle }}
                </div>
                <div class="h-px min-w-0 flex-1 bg-gray-200 dark:bg-gray-700" aria-hidden="true" />
            </div>
        </div>

        <div tabindex="-1" class="section-gap-drop-zone mx-auto max-w-5xl h-6 flex items-center -mt-4" data-section-gap-index="0" />

        <template v-for="(section, sectionIndex) in sections" :key="section._id">
            <Section
                :section
                :can-delete-section
                @deleted="sectionDeleted"
            >
                <template v-if="section.fields.length && sectionIndex === (sections.length - 1)" #footer>
                    <div :id="`actions-${page._id}`" data-pagination class="mt-8">
                        <div class="cursor-pointer flex items-center gap-2.5" @click.prevent="inspectAction">
                            <Icon
                                v-if="hasLogic"
                                data-logic-attached
                                name="logic-tree"
                                class="relative size-3.5! shrink-0 text-gray-400 dark:text-gray-500"
                                :aria-label="__('Logic attached')"
                                v-tooltip="__('Logic attached')"
                            />
                            <Button
                                v-if="page.show_previous_button"
                                variant="filled"
                                icon="chevron-left"
                                :data-editing-field="isInspectingAction ? '' : undefined"
                                :data-editing-item="isInspectingAction ? '' : undefined"
                                class="ps-3"
                                :text="page.previous_page_label?.length ? __(page.previous_page_label) : __('Previous Page')"
                            />
                            <Button
                                variant="primary"
                                :data-editing-field="isInspectingAction ? '' : undefined"
                                :data-editing-item="isInspectingAction ? '' : undefined"
                                class="border-0! dark:border-0! ring-0! shadow-none!"
                                style="--theme-color-primary: var(--theme-color-gray-950)"
                                :text="page.button_label?.length ? page.button_label : (isLastPage ? __('Submit') : __('Next Page'))"
                            />
                        </div>
                    </div>
                </template>
            </Section>

            <div tabindex="-1" class="section-gap-drop-zone mx-auto max-w-5xl h-14 -mt-8 flex items-center" :data-section-gap-index="sectionIndex + 1" />
        </template>
    </div>
</template>
