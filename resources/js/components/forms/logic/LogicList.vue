<script setup lang="ts">
import PageLogic from './PageLogic.vue';
import FieldLogic from './FieldLogic.vue';
import { writeFieldConditions } from '@/composables/forms/field-conditions';
import { computed } from 'vue';

const emit = defineEmits(['update:pages']);

const props = defineProps({
    pages: { type: Array, required: true },
    suggestableFields: { type: Array, required: true },
    fieldtypes: Array,
});

const fieldCategory = (field) => props.fieldtypes.find((fieldtype) => fieldtype.handle === field.fieldtype)?.categories?.[0] ?? 'other';

const fields = computed({
    get: () => props.pages.flatMap((page, pageIndex) => page.sections.flatMap((section) => section.fields
        .filter((field) => field.type !== 'import')
        .map((field) => ({
            _id: field.handle,
            handle: field.handle,
            display: field.config?.display ?? field.handle,
            icon: field.icon,
            category: fieldCategory(field),
            fieldtype: field.fieldtype,
            pageIndex,
            hidden: field.config?.hidden ?? false,
            if: field.config?.if ?? null,
            unless: field.config?.unless ?? null,
            if_any: field.config?.if_any ?? null,
            unless_any: field.config?.unless_any ?? null,
            always_save: field.config?.always_save ?? false,
            options: field.config?.options,
        })))),
    set: (fields) => {
        const nodes = props.pages.flatMap((page) => page.sections).flatMap((section) => section.fields);

        fields.forEach((field) => {
            const node = nodes.find((node) => node.handle === field.handle);
            if (node) writeFieldConditions(node, field);
        });
    },
});
</script>

<template>
    <PageLogic
        v-if="pages.length > 1"
        class="mb-6"
        :pages="pages"
        :suggestable-fields
        :fieldtypes
        @update:pages="emit('update:pages', $event)"
    />

    <FieldLogic
        v-model:fields="fields"
        :suggestable-fields
        :fieldtypes
    />
</template>
