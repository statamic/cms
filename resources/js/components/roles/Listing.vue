<template>
    <data-list :rows="rows" :columns="columns">
        <div class="card p-0" slot-scope="{ }">
            <data-list-table>
                <template slot="cell-title" slot-scope="{ row: role, index }">
                    <a :href="role.edit_url">{{ role.title }}</a>
                </template>
                <template slot="cell-handle" slot-scope="{ value: handle }">
                    <span class="font-mono text-xs">{{ handle }}</span>
                </template>
                <template slot="actions" slot-scope="{ row: role, index }">
                    <dropdown-list>
                        <dropdown-item :text="__('Edit')" :redirect="role.edit_url" />
                        <dropdown-item
                            :text="__('Delete')"
                            class="warning"
                            @click="$refs[`deleter_${role.id}`].confirm()"
                        >
                            <resource-deleter
                                :ref="`deleter_${role.id}`"
                                :resource="role"
                                @deleted="removeRow(role)">
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

    props: [
        'initialRows',
        'initialColumns',
    ],

    data() {
        return {
            rows: this.initialRows,
            columns: this.initialColumns
        }
    }

}
</script>
