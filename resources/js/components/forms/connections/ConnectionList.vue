<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { nanoid as uniqid } from 'nanoid';
import { Button, ConfirmationModal, Description } from '@ui';
import { SortableList } from '@/components/sortable/Sortable.js';
import { deepClone } from '@/util/clone.js';
import { preferences } from '@api';
import ConnectionItem from './ConnectionItem.vue';
import { __ } from '@/bootstrap/globals';

type Connection = {
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
    modelValue: Connection[];
    errors: Record<string, string[]>;
    defaults: Record<string, unknown>;
    addLabel: string;
    description?: string;
    deleteHeading: string;
    deleteDescription: string;
}>(), {
    modelValue: () => [],
    errors: () => ({}),
    defaults: () => ({}),
    addLabel: () => __('Add Connection'),
    deleteHeading: () => __('Delete Connection'),
    deleteDescription: () => __('Are you sure you want to delete this connection?'),
});

const sortableItemClass = 'connection-item';
const sortableHandleClass = 'connection-item-handle';

const collapseByDefault = ref<boolean>(preferences.get('forms.connect.collapse_connections', true));
const collapsed = ref<string[]>(collapseByDefault.value ? props.modelValue.map((connection) => connection.id) : []);
const confirmingRemoval = ref<string | null>(null);
const errorConnectionIds = ref<string[]>([]);

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

const duplicate = (connection: Connection): void => {
    const duplicated = [...props.modelValue];

    duplicated.splice(props.modelValue.indexOf(connection) + 1, 0, {
        ...deepClone(connection),
        id: uniqid(),
        conditions: connection.conditions.map((condition) => ({ ...condition, _id: uniqid() })),
    });

    emit('update:modelValue', duplicated);
};

const updateEnabled = (connection: Connection, enabled: boolean): void => {
    emit(
        'update:modelValue',
        props.modelValue.map((existing) => (existing.id === connection.id ? { ...existing, enabled } : existing))
    );
};

const remove = (): void => {
    const connection = props.modelValue.find((item) => item.id === confirmingRemoval.value);

    expand(confirmingRemoval.value);
    confirmingRemoval.value = null;

    if (connection) emit('update:modelValue', props.modelValue.filter((existing) => existing !== connection));
};

const isEnabled = (connection: Connection): boolean => connection.enabled !== false;
const isCollapsed = (connection: Connection): boolean => collapsed.value.includes(connection.id);

const collapse = (id: string): void => {
    if (!collapsed.value.includes(id)) {
        collapsed.value.push(id);
    }
};

const expand = (id: string): void => (collapsed.value = collapsed.value.filter((connectionId) => connectionId !== id));

const expandAll = (): void => {
    collapsed.value = [];
    collapseByDefault.value = false;
};

const collapseAll = (): void => {
    collapsed.value = props.modelValue.map((connection) => connection.id);
    collapseByDefault.value = true;
};

const allCollapsed = computed(() => collapsed.value.length === props.modelValue.length);

const errorIndex = (connection: Connection): number => errorConnectionIds.value.indexOf(connection.id);

const hasError = (connection: Connection): boolean => {
    const index = errorIndex(connection);

    return index !== -1 && Object.keys(props.errors).some((key) => key === `${index}` || key.startsWith(`${index}.`));
};

const connectionErrors = (connection: Connection) => {
    const index = errorIndex(connection);

    return Object.entries(props.errors)
        .filter(([key]) => key.startsWith(`${index}.`))
        .reduce((fields, [key, messages]) => {
            const handle = key.replace(`${index}.`, '').split('.')[0];
            fields[handle] = [...(fields[handle] ?? []), ...messages];
            return fields;
        }, {});
};

watch(collapseByDefault, (collapse: boolean) => preferences.set('forms.connect.collapse_connections', collapse));

watch(
    () => props.errors,
    () => (errorConnectionIds.value = props.modelValue.map((connection) => connection.id)),
    { immediate: true },
);
</script>

<template>
    <Teleport v-if="modelValue.length > 1" defer to="#connection-list-actions">
        <Button
            size="xs"
            variant="ghost"
            :icon="allCollapsed ? 'expand' : 'collapse'"
            :aria-label="allCollapsed ? __('Expand all') : __('Collapse all')"
            @click="allCollapsed ? expandAll() : collapseAll()"
        />
    </Teleport>

    <Description v-if="description" :text="description" class="mb-4" />

    <Button v-if="modelValue.length === 0" size="sm" :text="addLabel" icon="plus" @click="add" />

    <template v-else>
        <SortableList
            vertical
            constrain-dimensions
            :model-value="modelValue"
            :item-class="sortableItemClass"
            :handle-class="sortableHandleClass"
            @update:model-value="$emit('update:modelValue', $event)"
        >
            <div class="relative space-y-6 mb-0" data-connection-list>
                <div v-for="(connection, index) in modelValue" :key="connection.id" :class="sortableItemClass">
                    <ConnectionItem
                        :enabled="isEnabled(connection)"
                        :collapsed="isCollapsed(connection)"
                        :has-error="hasError(connection)"
                        :handle-class="sortableHandleClass"
                        @collapsed="collapse(connection.id)"
                        @expanded="expand(connection.id)"
                        @duplicated="duplicate(connection)"
                        @removed="confirmingRemoval = connection.id"
                        @update:enabled="updateEnabled(connection, $event)"
                    >
                        <template #header>
                            <slot name="header" :item="connection" :index="index" :collapsed="collapsed.includes(connection.id)" />
                        </template>

                        <slot :item="connection" :index="index" :errors="connectionErrors(connection)" />
                    </ConnectionItem>
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
[data-connection-list]::before {
    content: '';
    position: absolute;
    top: 1.5rem;
    bottom: 0;
    inset-inline-start: 0.875rem;
    border-inline-start: 1px dashed var(--color-gray-400);
}

.dark [data-connection-list]::before {
    border-inline-start-color: var(--color-gray-600);
}
</style>
