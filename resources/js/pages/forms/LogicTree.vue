<script setup>
import { Icon } from '@ui';
import { categories, categoryColorClasses } from '@/components/forms/builder/categories';
import { computed } from 'vue';

const props = defineProps({
    pages: { type: Array, required: true },
    fields: { type: Array, required: true },
    expanded: { type: Boolean, default: false },
});

const pageAnchor = (pageIndex) => `--page-${pageIndex + 1}`;

const groupPageFields = (pageFields) => {
    const groups = [];
    let fieldsetGroup = null;

    pageFields.forEach((field) => {
        if (field.import) {
            if (fieldsetGroup?.import === field.import) {
                fieldsetGroup.fields.push(field);
            } else {
                fieldsetGroup = {
                    type: 'fieldset',
                    import: field.import,
                    title: field.import_title,
                    fields: [field],
                };
                groups.push(fieldsetGroup);
            }

            return;
        }

        fieldsetGroup = null;
        groups.push({ type: 'field', field });
    });

    return groups;
};

const fieldsByPage = computed(() => {
    return props.pages.map((page, pageIndex) => ({
        page,
        pageIndex,
        groups: groupPageFields(props.fields.filter((field) => field.page_index === pageIndex)),
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
                v-for="{ page, pageIndex, groups } in fieldsByPage"
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
                            class="size-3! shrink-0 -ms-2.5 relative -top-0.25 text-blue-400"
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
                            class="mx-auto flex w-full shrink-0 justify-center items-center gap-2 rounded-xl border border-dashed border-gray-300 px-3.5 py-2 text-xs font-medium text-gray-850 bg-white dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200"
                            :class="{ 'w-[85%]!': hasPageNameLeadingIcons(page, pageIndex) }"
                        >
                            <Icon name="page" class="size-4 shrink-0 -ms-1.5 text-gray-500 dark:text-gray-400" aria-hidden="true" />
                            <span class="line-clamp-1">{{ pageTitle(page, pageIndex) }}</span>
                        </div>
                    </div>
                </div>

                <ul>
                    <template v-for="group in groups" :key="group.type === 'field' ? group.field._id : group.import">
                        <li
                            v-if="group.type === 'field'"
                            :class="{
                                'linked-list__connector': fieldConnection(group.field),
                                'linked-list__page-leap': fieldConnection(group.field)?.leap,
                            }"
                            :style="fieldConnection(group.field) ? { '--end-connection': fieldConnection(group.field).endConnection } : null"
                        >
                            <div v-if="fieldConnection(group.field)?.leap" class="linked-list__extra-leap-connector" />
                            <Icon
                                :name="group.field.icon || 'generic-field'"
                                :class="['size-4 shrink-0', fieldIconClass(group.field)]"
                                aria-hidden="true"
                            />
                            <span class="linked-list__field-name min-w-0 flex-1">{{ group.field.display }}</span>
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
                        </li>
                        <li v-else class="linked-list__fieldset-wrap">
                            <div class="linked-list__fieldset-group">
                                <div
                                    v-for="(field, fieldIndex) in group.fields"
                                    :key="field._id"
                                    class="linked-list__field-row"
                                    :class="{
                                        'linked-list__connector': fieldConnection(field),
                                        'linked-list__page-leap': fieldConnection(field)?.leap,
                                    }"
                                    :style="fieldConnection(field) ? { '--end-connection': fieldConnection(field).endConnection } : null"
                                >
                                    <div v-if="fieldConnection(field)?.leap" class="linked-list__extra-leap-connector" />
                                    <Icon
                                        :name="field.icon || 'generic-field'"
                                        :class="['size-4 shrink-0', fieldIconClass(field)]"
                                        aria-hidden="true"
                                    />
                                    <span class="linked-list__field-name min-w-0 flex-1">{{ field.display }}</span>
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
                                        v-if="fieldIndex === 0"
                                        v-tooltip="group.title ? __('Linked Fieldset: :title', { title: group.title }) : __('Linked Fieldset')"
                                        class="inline-flex size-4 shrink-0"
                                    >
                                        <Icon
                                            name="link"
                                            class="size-4 shrink-0 text-indigo-500 dark:text-indigo-400"
                                            aria-hidden="true"
                                        />
                                    </span>
                                </div>
                            </div>
                        </li>
                    </template>
                </ul>
            </div>
        </div>
    </div>
</template>
