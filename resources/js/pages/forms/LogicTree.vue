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

const fieldsByPage = computed(() => {
    return props.pages.map((page, pageIndex) => ({
        page,
        pageIndex,
        fields: props.fields.filter((field) => field.page_index === pageIndex),
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

const fieldIconClass = (category) => {
    const color = categories[category]?.color || 'gray';

    return categoryColorClasses[color]?.icon || 'text-gray-600 dark:text-gray-400';
};
</script>

<template>
    <div class="linked-list w-full" :class="{ 'linked-list--expanded': expanded }">
        <div
            v-for="{ page, pageIndex, fields: pageFields } in fieldsByPage"
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
                <li
                    v-for="field in pageFields"
                    :key="field._id"
                    :class="{
                        'linked-list__connector': fieldConnection(field),
                        'linked-list__page-leap': fieldConnection(field)?.leap,
                    }"
                    :style="fieldConnection(field) ? { '--end-connection': fieldConnection(field).endConnection } : null"
                >
                    <div v-if="fieldConnection(field)?.leap" class="linked-list__extra-leap-connector" />
                    <Icon
                        :name="field.icon || 'generic-field'"
                        :class="['size-4 shrink-0', fieldIconClass(field.category)]"
                        aria-hidden="true"
                    />
                    <span class="linked-list__field-name">{{ field.display }}</span>
                </li>
            </ul>
        </div>
    </div>
</template>
