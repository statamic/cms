<template>
    <data-list :rows="rows" :columns="columns" v-slot="{}">
        <div class="card p-0">
            <data-list-table>
                <template #cell-title="{ row: role, index }">
                    <a :href="role.edit_url">{{ __(role.title) }}</a>
                </template>
                <template #cell-handle="{ value: handle }">
                    <span class="font-mono text-xs">{{ handle }}</span>
                </template>
                <template #actions="{ row: role, index }">
                    <dropdown-list>
                        <dropdown-item :text="__('Edit')" :redirect="role.edit_url" />
                        <dropdown-item
                            :text="__('Delete')"
                            class="warning"
                            @click="$refs[`deleter_${role.id}`].confirm()"
                        >
                            <resource-deleter :ref="`deleter_${role.id}`" :resource="role" :requires-elevated-session="true" @deleted="removeRow(role)">
                            </resource-deleter>
                        </dropdown-item>
                    </dropdown-list>
                </template>
            </data-list-table>
        </div>
    </data-list>
</template>

<script>
import Listing from '../Listing.vue';

export default {
    mixins: [Listing],

    props: ['initialRows', 'initialColumns'],

    data() {
        return {
            rows: this.initialRows,
            columns: this.initialColumns,
        };
    },
};
</script>
