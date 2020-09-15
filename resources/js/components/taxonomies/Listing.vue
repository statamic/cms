<template>
    <data-list :columns="columns" :rows="rows">
        <div class="card p-0" slot-scope="{ filteredRows: rows }">
            <data-list-table :rows="rows">
                <template slot="cell-title" slot-scope="{ row: taxonomy }">
                    <a :href="taxonomy.terms_url">{{ taxonomy.title }}</a>
                </template>
                <template slot="actions" slot-scope="{ row: taxonomy, index }">
                    <dropdown-list>
                        <dropdown-item :text="__('Edit')" :redirect="taxonomy.edit_url" />
                        <dropdown-item :text="__('Edit Blueprints')" :redirect="taxonomy.blueprints_url" />
                        <dropdown-item
                            v-if="taxonomy.deleteable"
                            :text="__('Delete')"
                            class="warning"
                            @click="$refs[`deleter_${taxonomy.id}`].confirm()"
                        >
                            <resource-deleter
                                :ref="`deleter_${taxonomy.id}`"
                                :resource="taxonomy"
                                @deleted="removeRow(taxonomy)">
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
        'initial-rows',
        'initial-columns',
    ],

    data() {
        return {
            rows: this.initialRows,
            columns: this.initialColumns
        }
    }

}
</script>
