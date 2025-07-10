<script setup>
import { ref } from 'vue';
import { DropdownItem, Listing } from '@statamic/ui';

const props = defineProps({
    initialRows: Array,
});

const rows = ref(props.initialRows);

const columns = ref([
    { label: __('Title'), field: 'title' },
    { label: __('Handle'), field: 'handle' },
    { label: __('Users'), field: 'users' },
    { label: __('Roles'), field: 'roles' },
]);

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
        @refreshing="reloadPage"
    >
        <template #cell-title="{ row: group }">
            <a :href="group.show_url">{{ __(group.title) }}</a>
            <resource-deleter :ref="`deleter_${group.id}`" :resource="group" @deleted="removeRow(group)" />
        </template>
        <template #cell-handle="{ value: handle }">
            <span class="font-mono text-xs">{{ handle }}</span>
        </template>
        <template #prepended-row-actions="{ row: group }">
            <DropdownItem :text="__('View')" icon="eye" :href="group.show_url" />
            <DropdownItem :text="__('Configure')" icon="cog" :href="group.edit_url" />
            <DropdownItem
                :text="__('Delete')"
                icon="trash"
                variant="destructive"
                @click="$refs[`deleter_${group.id}`].confirm()"
            />
        </template>
    </Listing>
</template>
