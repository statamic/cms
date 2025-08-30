<script setup>
import { sortBy } from 'lodash-es';
import { Combobox, CardPanel } from '@/components/ui';
import { computed, ref, watch, nextTick, onMounted } from 'vue';
import FieldFilterRow from './FieldFilterRow.vue';

const emit = defineEmits(['changed', 'cleared']);

const props = defineProps({
    config: Object,
    values: Object,
});

const rows = ref([]);
const rowRefs = ref({});
const fieldSelect = ref(null);

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

async function createFilter(newField) {
    const filter = availableFieldFilters.value.find((filter) => filter.handle === newField);

    let defaultValues = {};
    filter.fields
        .filter((field) => field.default)
        .forEach((field) => (defaultValues[field.handle] = field.default));
    filter.values = defaultValues;

    rows.value.push(filter);
    
    // Focus on the newly created row's first field
    await nextTick();
    const newRowRef = rowRefs.value[filter.handle];
    if (newRowRef && newRowRef.focusFirstField) {
        newRowRef.focusFirstField();
    }
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

async function handleEnterPressed() {
    await nextTick();
    if (fieldSelect.value && hasAvailableFieldFilters.value) {
        // Focus the combobox to allow adding another field
        const comboboxInput = fieldSelect.value.$el?.querySelector('input') || fieldSelect.value.$el?.querySelector('[role="combobox"]');
        if (comboboxInput) {
            comboboxInput.focus();
        }
    }
}

// Robust auto-focus for the "Add Field" combobox
const didAutoFocus = ref(false);
async function focusAddFieldCombobox() {
    if (didAutoFocus.value) return;
    await nextTick();
    if (!fieldSelect.value) return;

    // Prefer the actual input if present
    const input = fieldSelect.value.$el?.querySelector('input');
    if (input && typeof input.focus === 'function') {
        input.focus();
        didAutoFocus.value = true;
        return;
    }

    // Fallback to the combobox anchor/trigger
    const anchor = fieldSelect.value.$el?.querySelector('[data-ui-combobox-anchor]');
    if (anchor && typeof anchor.focus === 'function') {
        anchor.focus();
        didAutoFocus.value = true;
    }
}

onMounted(() => {
    if (hasAvailableFieldFilters.value) {
        focusAddFieldCombobox();
    }
});

watch(availableFieldFilters, (list) => {
    if (list && list.length && !didAutoFocus.value) {
        focusAddFieldCombobox();
    }
});

// Expose for parents that want to trigger focus explicitly
defineExpose({
    focusAddFieldCombobox,
});
</script>

<template>
    <div v-if="hasAvailableFieldFilters">
        <FieldFilterRow
            v-for="filter in rows"
            :key="filter.handle"
            :ref="(el) => { if (el) rowRefs[filter.handle] = el }"
            :display="filter.display"
            :fields="filter.fields"
            :meta="filter.meta"
            :values="filter.values"
            @update:values="rowUpdated(filter.handle, $event)"
            @removed="removeRow(filter.handle)"
            @enter-pressed="handleEnterPressed"
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
