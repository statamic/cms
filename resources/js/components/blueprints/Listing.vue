<script setup>
import { ref, watch } from 'vue';
import { DropdownItem, Listing } from '@/components/ui';
import { router } from '@inertiajs/vue3';

defineEmits(['reordered']);

const props = defineProps(['initialRows', 'reorderable']);

const rows = ref(props.initialRows);

const columns = ref([
    { label: __('Title'), field: 'title' },
    { label: __('Handle'), field: 'handle' },
    { label: __('Fields'), field: 'fields' },
]);

watch(
    () => props.initialRows,
    (newRows) => (rows.value = newRows),
    { deep: true },
);

const reloadPage = () => router.reload();
</script>

<template>
    <Listing
        :items="rows"
        :columns="columns"
        :allow-search="false"
        :allow-customizing-columns="false"
        :reorderable="reorderable"
        :sortable="false"
        :allow-actions-while-reordering="true"
        @refreshing="reloadPage"
        @reordered="$emit('reordered', $event)"
    >
        <template #cell-title="{ row: blueprint }">
            <div class="flex items-center">
                <div class="little-dot me-2" :class="[blueprint.hidden ? 'hollow' : 'bg-green-500']" />
                <a :href="blueprint.edit_url">{{ __(blueprint.title) }}</a>

                <resource-deleter
                    :ref="`deleter_${blueprint.id}`"
                    :resource="blueprint"
                    reload
                />
            </div>
        </template>
        <template #cell-handle="{ value }">
            <span class="font-mono text-xs">{{ value }}</span>
        </template>
        <template #prepended-row-actions="{ row: blueprint }">
            <DropdownItem :text="__('Edit')" icon="edit" :href="blueprint.edit_url" />
            <DropdownItem
                :text="__('Delete')"
                icon="trash"
                variant="destructive"
                @click="$refs[`deleter_${blueprint.id}`].confirm()"
            />
        </template>
    </Listing>
</template>
