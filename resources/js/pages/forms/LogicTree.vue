<script setup>
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

const fieldConnection = (field) => fieldConnections.value[field.handle] ?? null;
</script>

<template>
    <div class="linked-list w-full">
        <ul v-for="{ page, pageIndex, fields: pageFields } in fieldsByPage" :key="page._id">
            <li
                class="linked-list__page-label"
                :style="{ 'anchor-name': pageAnchor(pageIndex) }"
            >
                {{ pageTitle(page, pageIndex) }}
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
