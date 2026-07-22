<script setup>
import { computed, ref } from 'vue';
import { Button } from '@ui';
import { SortableList } from '@/components/sortable/Sortable.js';
import LogicEmptyState from '@/components/forms/logic/LogicEmptyState.vue';
import ConnectionListItem from './ConnectionListItem.vue';

const emit = defineEmits(['add', 'duplicate', 'remove', 'update:modelValue']);

const props = defineProps({
    modelValue: { type: Array, default: () => [] },
    addLabel: String,
    emptyHeading: String,
    emptyDescription: String,
    hasError: { type: Function, default: () => false },
});

const sortableItemClass = 'connection-list-item';
const sortableHandleClass = 'connection-list-handle';

const items = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value),
});

const collapsed = ref(items.value.map((item) => item.id));

const collapse = (id) => {
    if (!collapsed.value.includes(id)) {
        collapsed.value.push(id);
    }
};

const expand = (id) => (collapsed.value = collapsed.value.filter((itemId) => itemId !== id));
</script>

<template>
    <LogicEmptyState v-if="items.length === 0" :heading="emptyHeading" :description="emptyDescription">
        <Button size="sm" :text="addLabel" icon="plus" @click="emit('add')" />
    </LogicEmptyState>

    <template v-else>
        <SortableList
            v-model="items"
            vertical
            constrain-dimensions
            :item-class="sortableItemClass"
            :handle-class="sortableHandleClass"
        >
            <div class="relative space-y-6 mb-0" data-connection-list>
                <div v-for="(item, index) in items" :key="item.id" :class="sortableItemClass">
                    <ConnectionListItem
                        :collapsed="collapsed.includes(item.id)"
                        :enabled="item.enabled !== false"
                        :has-error="hasError(index)"
                        :handle-class="sortableHandleClass"
                        @collapsed="collapse(item.id)"
                        @expanded="expand(item.id)"
                        @duplicated="emit('duplicate', item)"
                        @removed="emit('remove', item)"
                        @update:enabled="item.enabled = $event"
                    >
                        <template #header>
                            <slot name="header" :item="item" :index="index" :collapsed="collapsed.includes(item.id)" />
                        </template>

                        <slot :item="item" :index="index" />
                    </ConnectionListItem>
                </div>
            </div>
        </SortableList>

        <div class="inline-flex relative pt-6">
            <div class="absolute inset-y-0 h-full left-3.5 border-l-1 border-gray-400 dark:border-gray-600 border-dashed z-0 dark:bg-gray-850" />
            <Button size="sm" :text="addLabel" icon="plus" class="relative" @click="emit('add')" />
        </div>
    </template>
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
