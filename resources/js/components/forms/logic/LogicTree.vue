<script lang="ts">
export enum TreeDensity {
    Compressed = 'compressed',
    Expanded = 'expanded',
}

export enum SelectionType {
    Field = 'field',
    Page = 'page',
}

export type Selection = {
    type: SelectionType;
    id: string;
};
</script>

<script setup lang="ts">
import LogicTreeField from './LogicTreeField.vue';
import LogicTreeFieldset from './LogicTreeFieldset.vue';
import LayoutPanel from '@/pages/layout/LayoutPanel.vue';
import FieldInspector from '@/components/forms/builder/FieldInspector.vue';
import PageInspector from '@/components/forms/builder/PageInspector.vue';
import { Button, Icon } from '@ui';
import { useSortable } from '@/composables/forms/use-drag-and-drop';
import { computed, ref, useTemplateRef, watch } from 'vue';
import type { PropType } from 'vue';

const emit = defineEmits(['update:pages', 'select']);

const props = defineProps({
    pages: { type: Array, required: true },
    density: { type: String as PropType<TreeDensity>, default: TreeDensity.Compressed },
    selected: { type: Object as PropType<Selection>, default: null },
});

const tree = useTemplateRef('tree');
const isInspectorOpen = ref(false);

const pageAnchor = (pageIndex) => `--page-${pageIndex + 1}`;

const fieldConnections = computed(() => {
    const connections = {};

    props.pages.forEach((page, pageIndex) => {
        (page.rules ?? []).forEach((rule) => {
            if (! rule.destination) return;

            const destinationPageIndex = props.pages.findIndex((page) => page._id === rule.destination);
            if (destinationPageIndex <= pageIndex) return;

            const condition = (rule.conditions ?? []).find((condition) => condition.field && condition.value !== null && condition.value !== '');
            if (! condition?.field) return;

            connections[condition.field] = {
                endConnection: pageAnchor(destinationPageIndex),
                leap: destinationPageIndex - pageIndex > 1,
                destinationPageIndex,
            };
        });
    });

    return connections;
});

const pageTitle = (page, pageIndex) => page.display || __('Page :number', { number: pageIndex + 1 });

const fieldConnection = (field) => (field.type === 'import' ? null : fieldConnections.value[field.handle] ?? null);
const isConnectorDestination = (pageIndex) => connectorDestinationPageIndices.value.has(pageIndex);

const connectorDestinationPageIndices = computed(() => {
    return new Set(Object.values(fieldConnections.value).map((connection) => connection.destinationPageIndex));
});

const hasPageRules = (page) => (page.rules ?? []).some((rule) => {
    if (! rule.destination) {
        return false;
    }

    return (rule.conditions ?? []).some((condition) => condition.field && condition.value !== null && condition.value !== '');
});

const hasPageIndicators = (page, pageIndex) => isConnectorDestination(pageIndex) || hasPageRules(page);
const selectField = (field) => field.type === 'import' || emit('select', { type: SelectionType.Field, id: field._id });
const selectPage = (page) => emit('select', { type: SelectionType.Page, id: page._id });
const isFieldSelected = (field) => props.selected?.type === SelectionType.Field && props.selected.id === field._id;
const isPageSelected = (page) => props.selected?.type === SelectionType.Page && props.selected.id === page._id;

const selectedFieldEndConnectionPageIndex = computed(() => {
    if (props.selected?.type !== SelectionType.Field) {
        return null;
    }

    for (const page of props.pages) {
        for (const section of page.sections) {
            const field = section.fields.find((f) => f._id === props.selected.id);

            if (field) {
                return fieldConnection(field)?.destinationPageIndex ?? null;
            }
        }
    }

    return null;
});

const isEndConnectionColumn = (pageIndex) => selectedFieldEndConnectionPageIndex.value === pageIndex;

const allSections = computed(() => props.pages.flatMap((page) => page.sections));

const moveField = (fromSectionId: string, toSectionId: string, oldIndex: number, newIndex: number) => {
    const pages = props.pages.map((page) => ({
        ...page,
        sections: page.sections.map((section) => ({ ...section, fields: [...section.fields] })),
    }));

    const sections = pages.flatMap((page) => page.sections);
    const fromSection = sections.find((section) => section._id === fromSectionId);
    const toSection = sections.find((section) => section._id === toSectionId);
    if (! fromSection || ! toSection) return;

    const [field] = fromSection.fields.splice(oldIndex, 1);
    if (! field) return;

    toSection.fields.splice(newIndex, 0, field);

    emit('update:pages', pages);
};

const onMirrorCreated = ({ source, mirror }) => {
    mirror.style.width = `${source.offsetWidth}px`;
    mirror.classList.add('linked-list__mirror');
};

useSortable({
    container: tree,
    sections: allSections,
    onFieldMoved: moveField,
    onMirrorCreated,
});

watch(() => props.selected, () => isInspectorOpen.value = false);
</script>

<template>
    <div class="st-full-bleed-content st-separator-on-scroll col-span-full flex flex-col flex-1 min-h-0">
        <div class="flex-1 min-h-0 overflow-y-auto">
            <div ref="tree" class="linked-list-container">
                <div class="linked-list" :class="{ 'linked-list--expanded': density === TreeDensity.Expanded }">
                    <div
                        v-for="(page, pageIndex) in pages"
                        :key="page._id"
                        :data-form-page="page._id"
                        class="linked-list__column"
                        :class="{ 'linked-list__column--has-end-connection': isEndConnectionColumn(pageIndex) }"
                    >
                        <div
                            class="linked-list__page-name cursor-pointer"
                            :style="{ 'anchor-name': pageAnchor(pageIndex) }"
                            @click="selectPage(page)"
                        >
                            <div
                                class="flex w-full min-w-0 flex-nowrap items-center justify-center gap-1.5"
                                :class="{ '-ms-1.5': hasPageIndicators(page, pageIndex) }"
                            >
                                <Icon
                                    v-if="isConnectorDestination(pageIndex)"
                                    name="chevron-right"
                                    class="logic-arrow size-3! shrink-0 -ms-2.5 relative -top-0.25 text-blue-400 dark:text-blue-500"
                                    aria-hidden="true"
                                />
                                <span v-if="hasPageRules(page)" v-tooltip="__('Logic attached')" class="inline-flex shrink-0">
                                    <Icon
                                        name="logic-tree"
                                        class="size-3.5! text-gray-400 dark:text-gray-500"
                                        aria-hidden="true"
                                    />
                                </span>
                                <div
                                    class="mx-auto flex w-full shrink-0 justify-center items-center gap-2 rounded-xl border border-dashed border-gray-300 px-3.5 py-2 text-xs font-medium text-gray-850 bg-white dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200"
                                    :class="{
                                        'w-[85%]!': hasPageIndicators(page, pageIndex),
                                        'ring-1 ring-blue-500 border-transparent': isPageSelected(page),
                                    }"
                                >
                                    <Icon name="page" class="size-4 shrink-0 -ms-1.5 text-gray-500 dark:text-gray-400" aria-hidden="true" />
                                    <span class="line-clamp-1">{{ pageTitle(page, pageIndex) }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="linked-list__sections">
                            <div
                                v-for="section in page.sections"
                                :key="section._id"
                                class="linked-list__section"
                            >
                                <div class="linked-list__section-marker" :aria-label="section.display">
                                    <span class="linked-list__section-marker-label line-clamp-2 text-center">{{ section.display }}</span>
                                </div>
                                <ul class="field-sort-container" :data-sort-section="section._id">
                                    <div v-if="! section.fields.length" class="linked-list__empty-section">
                                        {{ __('No fields') }}
                                    </div>
                                    <li
                                        v-for="field in section.fields"
                                        :key="field._id"
                                        data-field-item
                                        :data-field-item-selected="isFieldSelected(field) ? '' : undefined"
                                        class="cursor-pointer"
                                        :class="{
                                            'linked-list__fieldset': field.type === 'import',
                                            'linked-list__hidden-field': field.config?.hidden,
                                            'linked-list__connector': fieldConnection(field),
                                            'linked-list__page-leap': fieldConnection(field)?.leap,
                                            'ring-1 ring-blue-500': isFieldSelected(field),
                                        }"
                                        :style="fieldConnection(field) ? { '--end-connection': fieldConnection(field).endConnection } : null"
                                        v-tooltip="field.type === 'import' ? __(`Logic can't be added to imported fields. Please edit the fieldset instead.`) : null"
                                        @click="selectField(field)"
                                    >
                                        <LogicTreeFieldset v-if="field.type === 'import'" :field="field" />

                                        <template v-else>
                                            <div v-if="fieldConnection(field)?.leap" class="linked-list__extra-leap-connector" />
                                            <LogicTreeField :field="field" />
                                        </template>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <Button
        v-if="selected"
        class="min-[1000px]:hidden sticky top-3 z-(--z-index-above) sm:translate-x-3 md:translate-x-9 mb-5 col-start-3 row-start-1"
        :text="__('Settings')"
        icon="cog"
        @click="isInspectorOpen = !isInspectorOpen"
    />

    <LayoutPanel v-if="selected" side="right" :mobile-open="isInspectorOpen">
        <PageInspector v-if="selected.type === SelectionType.Page" />
        <FieldInspector v-else-if="selected.type === SelectionType.Field" />
    </LayoutPanel>

    <div
        class="mobile-panel-backdrop"
        :data-visible="isInspectorOpen"
        @click="isInspectorOpen = false"
    />
</template>
