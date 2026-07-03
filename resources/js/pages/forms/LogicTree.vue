<script setup lang="ts">
import LogicTreeField from './LogicTreeField.vue';
import LogicTreeFieldset from './LogicTreeFieldset.vue';
import { Icon } from '@ui';
import { useSortable } from '@/components/forms/builder/use-drag-and-drop';
import { computed, ref, useTemplateRef } from 'vue';
import type { PropType } from 'vue';

enum TreeDensity {
    Compressed = 'compressed',
    Expanded = 'expanded',
}

const emit = defineEmits(['update:fields']);

const props = defineProps({
    pages: { type: Array, required: true },
    fields: { type: Array, required: true },
    density: { type: String as PropType<TreeDensity>, default: TreeDensity.Compressed },
});

const pageAnchor = (pageIndex) => `--page-${pageIndex + 1}`;

// Group a page's fields into sections, and each section's fields into items. A
// fieldset import becomes one item - a group holding its fields; every other
// field is just the field itself. So the tree renders and drags at the item level.
const buildSections = (pageFields) => {
    const sections = [];
    let section = null;

    pageFields.forEach((field) => {
        if (! section || field.section_start) {
            section = { display: field.section_display || __('Section'), items: [] };
            sections.push(section);
        }

        if (! field.import) {
            section.items.push(field);
            return;
        }

        const last = section.items.at(-1);

        if (last?.import === field.import) {
            last.fields.push(field);
        } else {
            section.items.push({
                _id: field._id,
                import: field.import,
                import_title: field.import_title ?? null,
                fields: [field],
            });
        }
    });

    return sections;
};

// The tree's working structure (pages -> sections -> items). It's stateful
// rather than re-derived from props so that dragging the last field out of a
// section keeps the (now empty) section around, and so drag can move items
// between sections directly.
const columns = ref(
    props.pages.map((page, pageIndex) => ({
        page,
        pageIndex,
        sections: buildSections(props.fields.filter((field) => field.page_index === pageIndex)),
    })),
);

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
const itemConnection = (item) => (item.import ? null : fieldConnections.value[item.handle] ?? null);

// A single-field item is the field itself; a fieldset item is a group of fields.
const itemFields = (item) => (item.import ? item.fields : [item]);

const connectorDestinationPageIndices = computed(() => {
    return new Set(Object.values(fieldConnections.value).map((connection) => connection.destinationPageIndex));
});

const isConnectorDestination = (pageIndex) => connectorDestinationPageIndices.value.has(pageIndex);

const hasPageNameLeadingIcons = (page, pageIndex) => isConnectorDestination(pageIndex) || hasPageRules(page);

// Drag & drop. Each item (single field or whole fieldset) moves as one unit.
const tree = useTemplateRef('tree');
const allSections = computed(() =>
    columns.value.flatMap((column) => column.sections.map((section) => ({
        fields: section.items.flatMap(itemFields),
    }))),
);

const moveField = (from, to, oldIndex, newIndex) => {
    const [fromPage, fromSection] = from.split(':').map(Number);
    const [toPage, toSection] = to.split(':').map(Number);

    const [item] = columns.value[fromPage].sections[fromSection].items.splice(oldIndex, 1);
    if (! item) return;

    columns.value[toPage].sections[toSection].items.splice(newIndex, 0, item);

    // Flatten back to fields, re-tagging page + section so numbering stays correct.
    const fields = columns.value.flatMap((column, pageIndex) =>
        column.sections.flatMap((section) =>
            section.items
                .flatMap(itemFields)
                .map((field, index) => ({
                    ...field,
                    page_index: pageIndex,
                    section_start: index === 0,
                    section_display: section.display,
                })),
        ),
    );

    emit('update:fields', fields);
};

// The mirror is a clone of the dragged <li> - which now already contains a whole
// fieldset's rows - appended to <body>, outside the scoped tree styles.
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
                v-for="{ page, pageIndex, sections } in columns"
                :key="page._id"
                class="linked-list__column"
            >
                <div
                    class="linked-list__page-name"
                    :style="{ 'anchor-name': pageAnchor(pageIndex) }"
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
                            :class="{ 'w-[85%]!': hasPageNameLeadingIcons(page, pageIndex) }"
                        >
                            <Icon name="page" class="size-4 shrink-0 -ms-1.5 text-gray-500 dark:text-gray-400" aria-hidden="true" />
                            <span class="line-clamp-1">{{ pageTitle(page, pageIndex) }}</span>
                        </div>
                    </div>
                </div>

                <div class="linked-list__sections">
                    <div
                        v-for="(section, sectionIndex) in sections"
                        :key="`${page._id}-section-${sectionIndex}`"
                        class="linked-list__section"
                    >
                        <div class="linked-list__section-marker" :aria-label="section.display">
                            <span class="linked-list__section-marker-label line-clamp-2 text-center">{{ section.display }}</span>
                        </div>
                        <ul class="field-sort-container" :data-sort-section="`${pageIndex}:${sectionIndex}`">
                            <div v-if="! section.items.length" class="linked-list__empty-section">
                                {{ __('No fields') }}
                            </div>
                            <li
                                v-for="field in section.items"
                                :key="field._id"
                                data-field-item
                                class="cursor-grab"
                                :class="{
                                    'linked-list__fieldset': field.import,
                                    'linked-list__connector': itemConnection(field),
                                    'linked-list__page-leap': itemConnection(field)?.leap,
                                }"
                                :style="itemConnection(field) ? { '--end-connection': itemConnection(field).endConnection } : null"
                                v-tooltip="field.import ? __('Logic can\'t be added to imported fields. Edit the fieldset instead.') : null"
                            >
                                <LogicTreeFieldset v-if="field.import" :item="field" />

                                <template v-else>
                                    <div v-if="itemConnection(field)?.leap" class="linked-list__extra-leap-connector" />
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
