<script setup>
import { sortBy } from 'lodash-es';
import { Combobox, CardPanel } from '@statamic/ui';
import { computed, ref, watch } from 'vue';
import FieldFilterRow from './FieldFilterRow.vue';

const emit = defineEmits(['changed', 'cleared']);

const props = defineProps({
    config: Object,
    values: Object,
});

const rows = ref([]);

watch(
    () => props.values,
    (values) => {
        const newRows = [];

        Object.entries(values).forEach(([handle, value]) => {
            const filter = props.config.extra.find((filter) => filter.handle === handle);
            filter.values = value;
            newRows.push(filter);
        });

        rows.value = newRows;
    },
    { immediate: true },
);

const availableFieldFilters = computed(() => {
    if (!props.config) return [];

    const usedHandles = rows.value.map(row => row.handle);

    return props.config.extra.filter((field) => !usedHandles.includes(field.handle));
});

const hasAvailableFieldFilters = computed(() => {
    return !!availableFieldFilters.value.length;
});

const fieldComboboxOptions = computed(() => {
    let options = availableFieldFilters.value.map((filter) => {
        return {
            value: filter.handle,
            label: filter.display,
        };
    });

    return sortBy(options, (option) => option.label);
});

function createFilter(newField) {
    const filter = availableFieldFilters.value.find((filter) => filter.handle === newField);

    let defaultValues = {};
    filter.fields
        .filter((field) => field.default)
        .forEach((field) => (defaultValues[field.handle] = field.default));
    filter.values = defaultValues;

    rows.value.push(filter);
}

function rowUpdated(handle, newValues) {
    emit('changed', {
        ...props.values,
        [handle]: newValues,
    })
}

function removeRow(handle) {
    rows.value = rows.value.filter(row => row.handle !== handle);
    const newValues = { ...props.values };
    delete newValues[handle];
    emit('changed', newValues);
}
</script>

<template>
    <div v-if="hasAvailableFieldFilters">
        <FieldFilterRow
            v-for="filter in rows"
            :key="filter.handle"
            :display="filter.display"
            :fields="filter.fields"
            :meta="filter.meta"
            :values="filter.values"
            @update:values="rowUpdated(filter.handle, $event)"
            @removed="removeRow(filter.handle)"
        />

        <Combobox
            ref="fieldSelect"
            :placeholder="__('Add Field')"
            :options="fieldComboboxOptions"
            @update:model-value="createFilter"
            class="w-1/4"
        />
    </div>

    <div v-else v-text="__('No available filters')"></div>
</template>
