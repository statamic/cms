<template>
    <data-list ref="dataList" :columns="columns" :rows="rows">
        <div class="card p-0" slot-scope="{ filteredRows: rows }">
            <data-list-table :rows="rows">
                <template slot="cell-title" slot-scope="{ row: collection }">
                    <a :href="collection.entries_url">{{ collection.title }}</a>
                </template>
                <template slot="actions" slot-scope="{ row: collection, index }">
                    <dropdown-list>
                        <dropdown-item :text="__('View')" :redirect="collection.entries_url" />
                        <dropdown-item v-if="collection.url" :text="__('Visit URL')" :external-link="collection.url"  />
                        <dropdown-item v-if="collection.editable" :text="__('Edit Collection')" :redirect="collection.edit_url" />
                        <dropdown-item v-if="collection.blueprint_editable" :text="__('Edit Blueprints')" :redirect="collection.blueprints_url" />
                        <dropdown-item v-if="collection.editable" :text="__('Scaffold Views')" :redirect="collection.scaffold_url" />
                        <dropdown-item
                            v-if="collection.deleteable"
                            :text="__('Delete Collection')"
                            class="warning"
                            @click="$refs[`deleter_${collection.id}`].confirm()"
                        >
                            <resource-deleter
                                :ref="`deleter_${collection.id}`"
                                :resource="collection"
                                @deleted="removeRow(collection)">
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
        initialColumns: Array,
    },

    data() {
        return {
            rows: this.initialRows,
            columns: this.initialColumns
        }
    }

}
</script>
