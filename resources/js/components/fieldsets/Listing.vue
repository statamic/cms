<script setup>
import { ref } from 'vue';
import { Listing, DropdownItem } from '@/components/ui';
import FieldsetDeleter from './FieldsetDeleter.vue';
import FieldsetResetter from './FieldsetResetter.vue';

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

function removeRow(row) {
    const i = rows.value.findIndex((r) => r.id === row.id);
    rows.value.splice(i, 1);
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
            <fieldset-resetter :ref="`resetter_${fieldset.id}`" :resource="fieldset" :reload="true" />
            <fieldset-deleter :ref="`deleter_${fieldset.id}`" :resource="fieldset" @deleted="removeRow(fieldset)" />
        </template>
        <template #cell-handle="{ value }">
            <span class="font-mono text-xs">{{ value }}</span>
        </template>
        <template #prepended-row-actions="{ row: fieldset }">
            <DropdownItem :text="__('Edit')" icon="edit" :href="fieldset.edit_url" />
            <DropdownItem
                v-if="fieldset.is_resettable"
                :text="__('Reset')"
                icon="history"
                variant="destructive"
                @click="$refs[`resetter_${fieldset.id}`].confirm()"
            />
            <DropdownItem
                v-if="fieldset.is_deletable"
                :text="__('Delete')"
                icon="trash"
                variant="destructive"
                @click="$refs[`deleter_${fieldset.id}`].confirm()"
            />
        </template>
    </Listing>
</template>
