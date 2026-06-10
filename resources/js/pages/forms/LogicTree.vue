<script setup>
import { Icon } from '@ui';
import { computed } from 'vue';

const props = defineProps({
    pages: { type: Array, required: true },
    fields: { type: Array, required: true },
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
</script>

<template>
    <div class="linked-list w-full">
        <ul v-for="{ page, pageIndex, fields: pageFields } in fieldsByPage" :key="page._id">
            <li
                class="linked-list__page-name !h-auto items-stretch border-0 bg-transparent px-0 py-0"
                :style="{ 'anchor-name': pageAnchor(pageIndex) }"
            >
                <div class="flex w-full min-w-0 flex-nowrap items-center justify-center gap-1.5">
                    <span v-if="hasPageRules(page)" v-tooltip="__('Logic attached')" class="inline-flex shrink-0">
                        <Icon
                            name="logic-tree"
                            class="size-3.5! text-gray-400 dark:text-gray-500"
                            aria-hidden="true"
                        />
                    </span>
                    <div class="flex shrink-0 items-center gap-2 rounded-xl border border-dashed border-gray-300 px-3.5 py-2 text-sm font-medium text-gray-700 bg-white dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200">
                        <Icon name="page" class="size-4 shrink-0 text-gray-500 dark:text-gray-400" aria-hidden="true" />
                        <span class="st-line-clamp">{{ pageTitle(page, pageIndex) }}</span>
                    </div>
                </div>
            </li>
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
                <span class="st-line-clamp">{{ field.display }}</span>
            </li>
        </ul>
    </div>
</template>
