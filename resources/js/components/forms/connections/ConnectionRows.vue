<script setup>
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import { nanoid as uniqid } from 'nanoid';
import { Button, ConfirmationModal } from '@ui';
import { SortableList } from '@/components/sortable/Sortable.js';
import { deepClone } from '@/util/clone.js';
import LogicEmptyState from '@/components/forms/logic/LogicEmptyState.vue';
import ConnectionRow from './ConnectionRow.vue';

const emit = defineEmits(['update:modelValue']);

const props = defineProps({
    modelValue: { type: Array, default: () => [] },
    defaults: Object,
    addLabel: String,
    emptyHeading: String,
    emptyDescription: String,
    deleteHeading: String,
    deleteDescription: String,
    hasError: { type: Function, default: () => false },
});

const sortableItemClass = 'connection-row';
const sortableHandleClass = 'connection-row-handle';

const collapsed = ref([]);
const confirmingRemoval = ref(null);

const items = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value),
});

const add = () => {
    items.value = [
        ...items.value,
        {
            id: uniqid(),
            enabled: true,
            conditions: [], ...deepClone(props.defaults.values),
        },
    ];
};

const duplicate = (item) => {
    const duplicated = [...items.value];

    duplicated.splice(items.value.indexOf(item) + 1, 0, {
        ...deepClone(item),
        id: uniqid(),
        conditions: item.conditions.map((condition) => ({ ...condition, _id: uniqid() })),
    });

    items.value = duplicated;
};

const collapse = (id) => {
    if (!collapsed.value.includes(id)) {
        collapsed.value.push(id);
    }
};

const expand = (id) => (collapsed.value = collapsed.value.filter((itemId) => itemId !== id));

const remove = () => {
    const item = items.value.find((item) => item.id === confirmingRemoval.value);

    expand(confirmingRemoval.value);
    confirmingRemoval.value = null;

    if (item) items.value = items.value.filter((existing) => existing !== item);
};

const updateEnabled = (item, enabled) =>
    (items.value = items.value.map((existing) => (existing.id === item.id ? { ...existing, enabled } : existing)));

watch(items, () => Statamic.$dirty.add('connection'), { deep: true });

onBeforeUnmount(() => Statamic.$dirty.remove('connection'));
</script>

<template>
    <LogicEmptyState v-if="items.length === 0" :heading="emptyHeading" :description="emptyDescription">
        <Button size="sm" :text="addLabel" icon="plus" @click="add" />
    </LogicEmptyState>

    <template v-else>
        <SortableList
            v-model="items"
            vertical
            constrain-dimensions
            :item-class="sortableItemClass"
            :handle-class="sortableHandleClass"
        >
            <div class="relative space-y-6 mb-0" data-connection-rows>
                <div v-for="(item, index) in items" :key="item.id" :class="sortableItemClass">
                    <ConnectionRow
                        :collapsed="collapsed.includes(item.id)"
                        :enabled="item.enabled !== false"
                        :has-error="hasError(index)"
                        :handle-class="sortableHandleClass"
                        @collapsed="collapse(item.id)"
                        @expanded="expand(item.id)"
                        @duplicated="duplicate(item)"
                        @removed="confirmingRemoval = item.id"
                        @update:enabled="updateEnabled(item, $event)"
                    >
                        <template #header>
                            <slot name="header" :item="item" :index="index" :collapsed="collapsed.includes(item.id)" />
                        </template>

                        <slot :item="item" :index="index" />
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
