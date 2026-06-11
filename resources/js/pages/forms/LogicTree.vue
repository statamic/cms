<script setup>
import FieldNumber from '@/components/forms/FieldNumber.vue';
import { Icon } from '@ui';
import { categories, categoryColorClasses } from '@/components/forms/builder/categories';
import { fieldNumberFromMap } from '@/composables/forms/field-numbering';
import { computed } from 'vue';

const props = defineProps({
    pages: { type: Array, required: true },
    fields: { type: Array, required: true },
    expanded: { type: Boolean, default: false },
    showFieldNumbers: { type: Boolean, default: false },
    fieldNumbers: { type: Map, default: () => new Map() },
});

const fieldNumber = (field) => {
    if (! props.showFieldNumbers) {
        return null;
    }

    return fieldNumberFromMap(props.fieldNumbers, field.handle, field._id);
};

const pageAnchor = (pageIndex) => `--page-${pageIndex + 1}`;

const groupPageFields = (pageFields) => pageFields.map((field) => ({ field }));

const firstFieldInGroup = (group) => group.field;

const isFirstInFieldset = (field, index, groups) => {
    if (! field.import) {
        return false;
    }

    const previous = groups[index - 1]?.field;

    return ! previous || previous.import !== field.import;
};

const isLastInFieldset = (field, index, groups) => {
    if (! field.import) {
        return false;
    }

    const next = groups[index + 1]?.field;

    return ! next || next.import !== field.import;
};

const isFieldsetField = (field, index, groups) => field.import && ! isFirstInFieldset(field, index, groups);

const groupPageSections = (pageFields) => {
    const groups = groupPageFields(pageFields);

    if (groups.length === 0) {
        return [];
    }

    const sections = [];
    let currentSection = null;

    groups.forEach((group) => {
        const field = firstFieldInGroup(group);

        if (! currentSection || field.section_start) {
            currentSection = {
                title: field.section_display || __('Section'),
                groups: [],
            };
            sections.push(currentSection);
        }

        currentSection.groups.push(group);
    });

    return sections;
};

const fieldsByPage = computed(() => {
    return props.pages.map((page, pageIndex) => ({
        page,
        pageIndex,
        sections: groupPageSections(props.fields.filter((field) => field.page_index === pageIndex)),
    }));
});

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

const fieldConnection = (field) => fieldConnections.value[field.handle] ?? null;

const connectorDestinationPageIndices = computed(() => {
    const indices = new Set();

    Object.values(fieldConnections.value).forEach((connection) => {
        const pageNumber = Number(connection.endConnection.replace('--page-', ''));

        if (! Number.isNaN(pageNumber)) {
            indices.add(pageNumber - 1);
        }
    });

    return indices;
});

const isConnectorDestination = (pageIndex) => connectorDestinationPageIndices.value.has(pageIndex);

const hasPageNameLeadingIcons = (page, pageIndex) => isConnectorDestination(pageIndex) || hasPageRules(page);

const mutedIconClass = 'text-gray-600 dark:text-gray-400';

const fieldIconClass = (field) => {
    if (field.type === 'reference' || field.import) {
        return mutedIconClass;
    }

    const color = categories[field.category]?.color || 'gray';

    return categoryColorClasses[color]?.icon || mutedIconClass;
};

</script>

<template>
    <div class="linked-list-container">
        <div class="linked-list" :class="{ 'linked-list--expanded': expanded }">
            <div
                v-for="{ page, pageIndex, sections } in fieldsByPage"
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
                        <div class="linked-list__section-marker" :aria-label="section.title">
                            <span class="linked-list__section-marker-label line-clamp-2 text-center">{{ section.title }}</span>
                        </div>
                        <ul>
                            <li
                                v-for="(group, groupIndex) in section.groups"
                                :key="group.field._id"
                                :class="{
                                    'linked-list__connector': fieldConnection(group.field),
                                    'linked-list__page-leap': fieldConnection(group.field)?.leap,
                                    'linked-list__fieldset-start': isFirstInFieldset(group.field, groupIndex, section.groups),
                                    'linked-list__fieldset-field': isFieldsetField(group.field, groupIndex, section.groups),
                                    'linked-list__fieldset-end': isLastInFieldset(group.field, groupIndex, section.groups),
                                }"
                                :style="fieldConnection(group.field) ? { '--end-connection': fieldConnection(group.field).endConnection } : null"
                            >
                                <div v-if="fieldConnection(group.field)?.leap" class="linked-list__extra-leap-connector" />
                                <Icon
                                    :name="group.field.icon || 'generic-field'"
                                    :class="['size-4 shrink-0', fieldIconClass(group.field)]"
                                    aria-hidden="true"
                                />
                                <span class="linked-list__field-name min-w-0 flex-1">
                                    <FieldNumber :number="fieldNumber(group.field)" />
                                    {{ group.field.display }}
                                </span>
                                <span
                                    v-if="group.field.type === 'reference'"
                                    v-tooltip="__('Linked Field')"
                                    class="inline-flex size-4 shrink-0"
                                >
                                    <Icon
                                        name="link"
                                        class="size-4 shrink-0 text-indigo-500 dark:text-indigo-400"
                                        aria-hidden="true"
                                    />
                                </span>
                                <span
                                    v-if="isFirstInFieldset(group.field, groupIndex, section.groups)"
                                    v-tooltip="group.field.import_title ? __('Linked Fieldset: :title', { title: group.field.import_title }) : __('Linked Fieldset')"
                                    class="inline-flex size-4 shrink-0"
                                >
                                    <Icon
                                        name="link"
                                        class="size-4 shrink-0 text-indigo-500 dark:text-indigo-400"
                                        aria-hidden="true"
                                    />
                                </span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
