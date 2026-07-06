<script lang="ts">
export enum TreeDensity {
    Compressed = 'compressed',
    Expanded = 'expanded',
}

export enum SelectionType {
    Field = 'field',
    Page = 'page',
}
</script>

<script setup lang="ts">
import LogicTreeField from './LogicTreeField.vue';
import LogicTreeFieldset from './LogicTreeFieldset.vue';
import { Icon } from '@ui';
import { useSortable } from '@/composables/forms/use-drag-and-drop';
import { computed, useTemplateRef } from 'vue';
import type { PropType } from 'vue';

const emit = defineEmits(['update:pages', 'select']);

const props = defineProps({
    pages: { type: Array, required: true },
    density: { type: String as PropType<TreeDensity>, default: TreeDensity.Compressed },
    selected: { type: Object, default: null }, // { type: SelectionType, id }
});

const pageAnchor = (pageIndex) => `--page-${pageIndex + 1}`;

const fieldConnections = computed(() => {
    const connections = {};

    props.pages.forEach((page, pageIndex) => {
        (page.rules ?? []).forEach((rule) => {
            if (! rule.destination) {
                return;
            }

            const destinationPageIndex = props.pages.findIndex((p) => p._id === rule.destination);

            if (destinationPageIndex <= pageIndex) {
                return;
            }

            const condition = (rule.conditions ?? []).find((c) => c.field && c.value !== null && c.value !== '');

            if (! condition?.field) {
                return;
            }

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

const hasPageRules = (page) => (page.rules ?? []).some((rule) => {
    if (! rule.destination) {
        return false;
    }

    return (rule.conditions ?? []).some((condition) => condition.field && condition.value !== null && condition.value !== '');
});

// Only single fields can be logic sources - imported fields can't be conditions.
const fieldConnection = (field) => (field.type === 'import' ? null : fieldConnections.value[field.handle] ?? null);

const connectorDestinationPageIndices = computed(() => {
    return new Set(Object.values(fieldConnections.value).map((connection) => connection.destinationPageIndex));
});

const isConnectorDestination = (pageIndex) => connectorDestinationPageIndices.value.has(pageIndex);

const hasPageNameLeadingIcons = (page, pageIndex) => isConnectorDestination(pageIndex) || hasPageRules(page);

// Selection - clicking a field or page opens its logic in the panel below. Imported
// fields can't hold logic, and the final page has nowhere to route to.
const isLastPage = (pageIndex) => pageIndex === props.pages.length - 1;
const selectField = (field) => field.type === 'import' || emit('select', { type: SelectionType.Field, id: field._id });
const selectPage = (page, pageIndex) => isLastPage(pageIndex) || emit('select', { type: SelectionType.Page, id: page._id });
const isFieldSelected = (field) => props.selected?.type === SelectionType.Field && props.selected.id === field._id;
const isPageSelected = (page) => props.selected?.type === SelectionType.Page && props.selected.id === page._id;

// Drag & drop. A field (or whole fieldset import) moves as one node between sections.
const tree = useTemplateRef('tree');
const allSections = computed(() => props.pages.flatMap((page) => page.sections.map((section) => ({ fields: section.fields }))));

const moveField = (from, to, oldIndex, newIndex) => {
    const [fromPage, fromSection] = from.split(':').map(Number);
    const [toPage, toSection] = to.split(':').map(Number);

    const pages = props.pages.map((page) => ({
        ...page,
        sections: page.sections.map((section) => ({ ...section, fields: [...section.fields] })),
    }));

    const [field] = pages[fromPage].sections[fromSection].fields.splice(oldIndex, 1);
    if (! field) return;

    pages[toPage].sections[toSection].fields.splice(newIndex, 0, field);

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
</script>

<template>
    <div ref="tree" class="linked-list-container">
        <div class="linked-list" :class="{ 'linked-list--expanded': density === TreeDensity.Expanded }">
            <div
                v-for="(page, pageIndex) in pages"
                :key="page._id"
                class="linked-list__column"
            >
                <div
                    class="linked-list__page-name"
                    :class="isLastPage(pageIndex) ? 'cursor-not-allowed' : 'cursor-pointer'"
                    :style="{ 'anchor-name': pageAnchor(pageIndex) }"
                    v-tooltip="isLastPage(pageIndex) ? __(`Logic can't be added to the final page.`) : null"
                    @click="selectPage(page, pageIndex)"
                >
                    <div
                        class="flex w-full min-w-0 flex-nowrap items-center justify-center gap-1.5"
                        :class="{ '-ms-1.5': hasPageNameLeadingIcons(page, pageIndex) }"
                    >
                        <Icon
                            v-if="isConnectorDestination(pageIndex)"
                            name="chevron-right"
                            class="size-3! shrink-0 -ms-2.5 relative -top-0.25 text-blue-400 dark:text-blue-500"
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
                                'w-[85%]!': hasPageNameLeadingIcons(page, pageIndex),
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
                        v-for="(section, sectionIndex) in page.sections"
                        :key="section._id"
                        class="linked-list__section"
                    >
                        <div class="linked-list__section-marker" :aria-label="section.display">
                            <span class="linked-list__section-marker-label line-clamp-2 text-center">{{ section.display }}</span>
                        </div>
                        <ul class="field-sort-container" :data-sort-section="`${pageIndex}:${sectionIndex}`">
                            <div v-if="! section.fields.length" class="linked-list__empty-section">
                                {{ __('No fields') }}
                            </div>
                            <li
                                v-for="field in section.fields"
                                :key="field._id"
                                data-field-item
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
</template>
