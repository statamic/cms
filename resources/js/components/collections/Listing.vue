<template>
    <data-list :columns="columns" :rows="rows">
        <div class="card p-0" slot-scope="{ filteredRows: rows }">
            <data-list-table :rows="rows">
                <template slot="cell-title" slot-scope="{ row: collection }">
                    <a :href="collection.entries_url">{{ collection.title }}</a>
                </template>
                <template slot="actions" slot-scope="{ row: collection, index }">
                    <dropdown-list>
                        <dropdown-item :text="__('Edit')" :redirect="collection.edit_url" />
                        <dropdown-item
                            v-if="collection.deleteable"
                            :text="__('Delete')"
                            class="warning"
                            @click="confirmDeleteRow(collection.id, index)" />
                    </dropdown-list>

                    <confirmation-modal
                        v-if="deletingRow !== false"
                        :title="deletingModalTitle"
                        :bodyText="__('Are you sure you want to delete this collection?')"
                        :buttonText="__('Delete')"
                        :danger="true"
                        @confirm="deleteRow('collections')"
                        @cancel="cancelDeleteRow"
                    >
                    </confirmation-modal>
                </template>
            </data-list-table>
        </div>
    </data-list>
</template>

<script>
import DeletesListingRow from '../DeletesListingRow.js'

export default {

    mixins: [DeletesListingRow],

    props: [
        'initial-rows',
        'columns',
    ],

    data() {
        return {
            rows: this.initialRows
        }
    }

}
</script>
