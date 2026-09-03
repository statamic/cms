<script setup lang="ts">
import { ref, watch } from 'vue';
import { nanoid as uniqid } from 'nanoid';
import { Button, ConfirmationModal, Description } from '@ui';
import { SortableList } from '@/components/sortable/Sortable.js';
import { deepClone } from '@/util/clone.js';
import LogicEmptyState from '@/components/forms/logic/LogicEmptyState.vue';
import ConnectionRow from './ConnectionRow.vue';
import { __ } from '@/bootstrap/globals';

type Row = {
    id: string;
    enabled: boolean;
    conditions: {
        _id: string;
        field: string;
        operator: string;
        value: string;
    }[];
    [key: string]: unknown;
}

const emit = defineEmits(['update:modelValue']);

const props = withDefaults(defineProps<{
    modelValue: Row[];
    errors: Record<string, string[]>;
    defaults: Record<string, unknown>;
    addLabel: string;
    emptyHeading: string;
    emptyDescription?: string;
    deleteHeading: string;
    deleteDescription: string;
}>(), {
    modelValue: () => [],
    errors: () => ({}),
    defaults: () => ({}),
    addLabel: __('Add Row'),
    emptyHeading: __('No rows yet'),
    deleteHeading: __('Delete Row'),
    deleteDescription: __('Are you sure you want to delete this row?'),
});

const sortableItemClass = 'connection-row';
const sortableHandleClass = 'connection-row-handle';

const collapsed = ref<string[]>([]);
const confirmingRemoval = ref<string | null>(null);
const errorRowIds = ref<string[]>([]);

const add = (): void => {
    emit('update:modelValue', [
        ...props.modelValue,
        {
            id: uniqid(),
            enabled: true,
            conditions: [],
            ...deepClone(props.defaults.values),
        },
    ]);
};

const duplicate = (row: Row): void => {
    const duplicated = [...props.modelValue];

    duplicated.splice(props.modelValue.indexOf(row) + 1, 0, {
        ...deepClone(row),
        id: uniqid(),
        conditions: row.conditions.map((condition) => ({ ...condition, _id: uniqid() })),
    });

    emit('update:modelValue', duplicated);
};

const updateEnabled = (row: Row, enabled: boolean): void => {
    emit(
        'update:modelValue',
        props.modelValue.map((existing) => (existing.id === row.id ? { ...existing, enabled } : existing))
    );
};

const remove = (): void => {
    const row = props.modelValue.find((item) => item.id === confirmingRemoval.value);

    expand(confirmingRemoval.value);
    confirmingRemoval.value = null;

    if (row) emit('update:modelValue', props.modelValue.filter((existing) => existing !== row));
};

const isEnabled = (row: Row): boolean => row.enabled !== false;
const isCollapsed = (row: Row): boolean => collapsed.value.includes(row.id);

const collapse = (id: string): void => {
    if (!collapsed.value.includes(id)) {
        collapsed.value.push(id);
    }
};

const expand = (id: string): void => (collapsed.value = collapsed.value.filter((rowId) => rowId !== id));

const errorIndex = (row: Row): number => errorRowIds.value.indexOf(row.id);

const hasError = (row: Row): boolean => {
    const index = errorIndex(row);

    return index !== -1 && Object.keys(props.errors).some((key) => key === `${index}` || key.startsWith(`${index}.`));
};

const rowErrors = (row: Row) => {
    const index = errorIndex(row);

    return Object.entries(props.errors)
        .filter(([key]) => key.startsWith(`${index}.`))
        .reduce((fields, [key, messages]) => {
            const handle = key.replace(`${index}.`, '').split('.')[0];
            fields[handle] = [...(fields[handle] ?? []), ...messages];
            return fields;
        }, {});
};

watch(
    () => props.errors,
    () => (errorRowIds.value = props.modelValue.map((row) => row.id)),
    { immediate: true },
);
</script>

<template>
    <Description v-if="emptyDescription" :text="emptyDescription" class="mb-4" />

    <div v-if="modelValue.length === 0">
        <Button size="sm" :text="addLabel" icon="plus" @click="add" />
    </div>

    <template v-else>

        <SortableList
            vertical
            constrain-dimensions
            :model-value="modelValue"
            :item-class="sortableItemClass"
            :handle-class="sortableHandleClass"
            @update:model-value="$emit('update:modelValue', $event)"
        >
            <div class="relative space-y-6 mb-0" data-connection-rows>
                <div v-for="(row, index) in modelValue" :key="row.id" :class="sortableItemClass">
                    <ConnectionRow
                        :enabled="isEnabled(row)"
                        :collapsed="isCollapsed(row)"
                        :has-error="hasError(row)"
                        :handle-class="sortableHandleClass"
                        @collapsed="collapse(row.id)"
                        @expanded="expand(row.id)"
                        @duplicated="duplicate(row)"
                        @removed="confirmingRemoval = row.id"
                        @update:enabled="updateEnabled(row, $event)"
                    >
                        <template #header>
                            <slot name="header" :item="row" :index="index" :collapsed="collapsed.includes(row.id)" />
                        </template>

                        <slot :item="row" :index="index" :errors="rowErrors(row)" />
                    </ConnectionRow>
                </div>
            </div>
        </SortableList>

        <div class="inline-flex relative pt-6">
            <div class="absolute inset-y-0 h-full left-3.5 border-l-1 border-gray-400 dark:border-gray-600 border-dashed z-0 dark:bg-gray-850" />
            <Button size="sm" :text="addLabel" icon="plus" class="relative" @click="add" />
        </div>
    </template>

    <ConfirmationModal
        :open="confirmingRemoval !== null"
        :title="deleteHeading"
        :body-text="deleteDescription"
        :button-text="__('Delete')"
        danger
        @update:open="confirmingRemoval = null"
        @confirm="remove"
    />
</template>

<style scoped>
[data-connection-rows]::before {
    content: '';
    position: absolute;
    top: 1.5rem;
    bottom: 0;
    inset-inline-start: 0.875rem;
    border-inline-start: 1px dashed var(--color-gray-400);
}

.dark [data-connection-rows]::before {
    border-inline-start-color: var(--color-gray-600);
}
</style>
