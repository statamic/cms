<script setup>
import { ref, watch } from 'vue';
import { DropdownItem, Listing } from '@statamic/cms/ui';

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

function reloadPage() {
    window.location.reload();
}

function removeRow(row) {
    const i = rows.value.findIndex((r) => r.id === row.id);
    rows.value.splice(i, 1);
}
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
                <div class="little-dot me-2" :class="[blueprint.hidden ? 'hollow' : 'bg-green-600']" />
                <a :href="blueprint.edit_url">{{ __(blueprint.title) }}</a>

                <resource-deleter
                    :ref="`deleter_${blueprint.id}`"
                    :resource="blueprint"
                    @deleted="removeRow(blueprint)"
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
