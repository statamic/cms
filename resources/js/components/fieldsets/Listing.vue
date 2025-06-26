<script setup>
import { ref } from 'vue';
import { Listing, DropdownItem } from '@statamic/ui';

const props = defineProps(['initialRows', 'actionUrl']);

const rows = ref(props.initialRows);

const columns = ref([
    { label: __('Title'), field: 'title' },
    { label: __('Handle'), field: 'handle', width: '25%' },
    { label: __('Fields'), field: 'fields', width: '15%' },
]);

function reloadPage() {
    window.location.reload();
}
</script>

<template>
    <Listing
        :items="rows"
        :columns="columns"
        :action-url="actionUrl"
        :allow-search="false"
        :allow-customizing-columns="false"
        @refreshing="reloadPage"
    >
        <template #cell-title="{ row: fieldset }">
            <a :href="fieldset.edit_url">{{ __(fieldset.title) }}</a>
        </template>
        <template #cell-handle="{ value }">
            <span class="font-mono text-xs">{{ value }}</span>
        </template>
        <template #prepended-row-actions="{ row: fieldset }">
            <DropdownItem :text="__('Edit')" icon="edit" :href="fieldset.edit_url" />
        </template>
    </Listing>
</template>
