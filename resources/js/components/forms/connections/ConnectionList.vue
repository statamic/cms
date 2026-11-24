<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { nanoid as uniqid } from 'nanoid';
import { Button, ConfirmationModal, Description } from '@ui';
import { SortableList } from '@/components/sortable/Sortable.js';
import { deepClone } from '@/util/clone.js';
import { preferences } from '@api';
import ConnectionItem from './ConnectionItem.vue';
import ConnectionRules from './ConnectionRules.vue';
import ConnectionFields from './ConnectionFields.vue';
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
    defaults: { values?: Record<string, unknown>; meta?: Record<string, unknown> };
    /** The blueprint's publish array, used by ConnectionFields. */
    blueprint?: Record<string, unknown>;
    /** Fieldtype meta for existing connections, keyed by connection id. */
    meta?: Record<string, Record<string, unknown>>;
    /** Prefix for each connection's publish container name. */
    name?: string;
    addLabel: string;
    description?: string;
    deleteHeading: string;
    deleteDescription: string;
    /** Label for the "always" option when no default slot is given. */
    alwaysLabel?: string;
    /** Label for the "if" option when no default slot is given. */
    ifLabel?: string;
    /** Whether to render ConnectionRules around the fields when no default slot is given. */
    rules?: boolean;
}>(), {
    modelValue: () => [],
    errors: () => ({}),
    defaults: () => ({}),
    meta: () => ({}),
    name: 'connection',
    rules: true,
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

// Each connection gets its own meta object, since publish containers mutate it (e.g. grid row meta).
const metaStore: Record<string, Record<string, unknown>> = {};

const seedMeta = (meta: Record<string, Record<string, unknown>>): void => {
    Object.entries(meta).forEach(([id, connectionMeta]) => (metaStore[id] = deepClone(connectionMeta)));
};

const metaFor = (connection: Connection): Record<string, unknown> =>
    (metaStore[connection.id] ??= deepClone(props.defaults.meta ?? {}));

const add = (): void => {
    const id = uniqid();

    metaStore[id] = deepClone(props.defaults.meta ?? {});

    emit('update:modelValue', [
        ...props.modelValue,
        {
            id,
            enabled: true,
            conditions: [],
            ...deepClone(props.defaults.values),
        },
    ]);
};

const duplicate = (connection: Connection): void => {
    const id = uniqid();
    const duplicated = [...props.modelValue];

    metaStore[id] = deepClone(metaFor(connection));

    duplicated.splice(props.modelValue.indexOf(connection) + 1, 0, {
        ...deepClone(connection),
        id,
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

    if (!connection) return;

    delete metaStore[connection.id];
    emit('update:modelValue', props.modelValue.filter((existing) => existing !== connection));
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
            fields[key.slice(`${index}.`.length)] = messages;
            return fields;
        }, {});
};

const context = (connection: Connection) => ({
    connection,
    errors: connectionErrors(connection),
    meta: metaFor(connection),
    blueprint: props.blueprint,
    name: props.name,
});

seedMeta(props.meta);

watch(() => props.meta, seedMeta);

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
                        :context="context(connection)"
                        @collapsed="collapse(connection.id)"
                        @expanded="expand(connection.id)"
                        @duplicated="duplicate(connection)"
                        @removed="confirmingRemoval = connection.id"
                        @update:enabled="updateEnabled(connection, $event)"
                    >
                        <template #header>
                            <slot name="header" :item="connection" :index="index" :collapsed="collapsed.includes(connection.id)" />
                        </template>

                        <slot
                            v-if="$slots.default"
                            :item="connection"
                            :index="index"
                            :errors="connectionErrors(connection)"
                            :meta="metaFor(connection)"
                        />
                        <ConnectionRules
                            v-else-if="rules"
                            v-model:conditions="connection.conditions"
                            :always-label="alwaysLabel"
                            :if-label="ifLabel"
                        >
                            <template #then>
                                <ConnectionFields />
                            </template>
                        </ConnectionRules>
                        <ConnectionFields v-else />
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
