<script setup>
import { ref } from 'vue';
import { DropdownItem, Listing } from '@statamic/ui';

const props = defineProps(['initialRows', 'initialColumns']);

const rows = ref(props.initialRows);
const columns = ref(props.initialColumns);

function removeRow(row) {
    const i = rows.value.findIndex((r) => r.id === row.id);
    rows.value.splice(i, 1);
}
</script>

<template>
    <Listing :items="rows" :columns="columns" :allow-search="false" :allow-customizing-columns="false">
        <template #cell-title="{ row: taxonomy }">
            <a :href="taxonomy.terms_url">{{ __(taxonomy.title) }}</a>

            <resource-deleter :ref="`deleter_${taxonomy.id}`" :resource="taxonomy" @deleted="removeRow(taxonomy)" />
        </template>

        <template #prepended-row-actions="{ row: taxonomy, index }">
            <DropdownItem :text="__('Edit')" icon="cog" :href="taxonomy.edit_url" />
            <DropdownItem :text="__('Edit Blueprints')" icon="blueprint-edit" :href="taxonomy.blueprints_url" />
            <DropdownItem
                :text="__('Delete Taxonomy')"
                icon="trash"
                variant="destructive"
                @click="$refs[`deleter_${taxonomy.id}`].confirm()"
            />
        </template>
    </Listing>
</template>
