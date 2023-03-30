<template>
    <data-list :rows="rows" :columns="columns">
        <div class="card p-0" slot-scope="{ }">
            <data-list-table>
                <template slot="cell-title" slot-scope="{ row: group, index }">
                    <a :href="group.show_url">{{ group.title }}</a>
                </template>
                <template slot="cell-handle" slot-scope="{ value: handle }">
                    <span class="font-mono text-xs">{{ handle }}</span>
                </template>
                <template slot="actions" slot-scope="{ row: group, index }">
                    <dropdown-list>
                        <dropdown-item :text="__('Edit')" :redirect="group.edit_url" />
                        <dropdown-item
                            :text="__('Delete')"
                            class="warning"
                            @click="$refs[`deleter_${group.id}`].confirm()"
                        >
                            <resource-deleter
                                :ref="`deleter_${group.id}`"
                                :resource="group"
                                @deleted="removeRow(group)">
                            </resource-deleter>
                        </dropdown-item>
                    </dropdown-list>
                </template>
            </data-list-table>
        </div>
    </data-list>
</template>

<script>
import Listing from '../Listing.vue'

export default {

    mixins: [Listing],

    props: {
        initialRows: Array,
    },

    data() {
        return {
            rows: this.initialRows,
            columns: [
                { label: __('Title'), field: 'title' },
                { label: __('Handle'), field: 'handle' },
                { label: __('Users'), field: 'users' },
                { label: __('Roles'), field: 'roles' },
            ]
        }
    }

}
</script>
