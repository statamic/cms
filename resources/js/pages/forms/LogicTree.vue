<script setup lang="ts">
import FieldNumber from '@/components/forms/FieldNumber.vue';
import { Icon } from '@ui';
import { categories, categoryColorClasses } from '@/components/forms/builder/categories';
import { computed } from 'vue';

enum TreeDensity {
    Compressed = 'compressed',
    Expanded = 'expanded',
}

const props = defineProps({
    pages: { type: Array, required: true },
    fields: { type: Array, required: true },
    density: { type: String as PropType<TreeDensity>, default: TreeDensity.Compressed },
});

const pageAnchor = (pageIndex) => `--page-${pageIndex + 1}`;

const isFirstInFieldset = (field, index, fields) => {
    if (! field.import) {
        return false;
    }

    const previous = fields[index - 1];

    return ! previous || previous.import !== field.import;
};

const isLastInFieldset = (field, index, fields) => {
    if (! field.import) {
        return false;
    }

    const next = fields[index + 1];

    return ! next || next.import !== field.import;
};

const isFieldsetField = (field, index, fields) => field.import && ! isFirstInFieldset(field, index, fields);

const groupPageSections = (pageFields) => {
    const sections = [];
    let currentSection = null;

    pageFields.forEach((field) => {
        if (! currentSection || field.section_start) {
            currentSection = {
                title: field.section_display || __('Section'),
                fields: [],
            };
            sections.push(currentSection);
        }

        currentSection.fields.push(field);
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
        <div class="linked-list" :class="{ 'linked-list--expanded': density === TreeDensity.Expanded }">
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
                                v-for="(field, fieldIndex) in section.fields"
                                :key="field._id"
                                v-tooltip="field.import ? __('Logic can\'t be added to imported fields. Edit the fieldset instead.') : null"
                                :class="{
                                    'linked-list__connector': fieldConnection(field),
                                    'linked-list__page-leap': fieldConnection(field)?.leap,
                                    'linked-list__fieldset-start': isFirstInFieldset(field, fieldIndex, section.fields),
                                    'linked-list__fieldset-field': isFieldsetField(field, fieldIndex, section.fields),
                                    'linked-list__fieldset-end': isLastInFieldset(field, fieldIndex, section.fields),
                                }"
                                :style="fieldConnection(field) ? { '--end-connection': fieldConnection(field).endConnection } : null"
                            >
                                <div v-if="fieldConnection(field)?.leap" class="linked-list__extra-leap-connector" />
                                <Icon
                                    :name="field.icon || 'generic-field'"
                                    :class="['size-4 shrink-0', fieldIconClass(field)]"
                                    aria-hidden="true"
                                />
                                <span class="linked-list__field-name min-w-0 flex-1">
                                    <FieldNumber :field-key="field._id" />
                                    {{ field.display }}
                                </span>
                                <span
                                    v-if="field.type === 'reference'"
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
                                    v-if="isFirstInFieldset(field, fieldIndex, section.fields)"
                                    v-tooltip="field.import_title ? __('Linked Fieldset: :title', { title: field.import_title }) : __('Linked Fieldset')"
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
