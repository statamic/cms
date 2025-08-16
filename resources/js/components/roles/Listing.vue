<script setup>
import { ref } from 'vue';
import { DropdownItem, Listing } from '@statamic/cms/ui';

const props = defineProps(['initialRows', 'initialColumns']);
const rows = ref(props.initialRows);
const columns = ref(props.initialColumns);

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
        <template #cell-title="{ row: role, index }">
            <a :href="role.edit_url">{{ __(role.title) }}</a>

            <resource-deleter
                :ref="`deleter_${role.id}`"
                :resource="role"
                requires-elevated-session
                @deleted="removeRow(role)"
            />
        </template>
        <template #cell-handle="{ value: handle }">
            <span class="font-mono text-xs">{{ handle }}</span>
        </template>
        <template #prepended-row-actions="{ row: role }">
            <DropdownItem :text="__('Configure')" icon="cog" :href="role.edit_url" />
            <DropdownItem
                :text="__('Delete')"
                icon="trash"
                variant="destructive"
                @click="$refs[`deleter_${role.id}`].confirm()"
            />
        </template>
    </Listing>
</template>
