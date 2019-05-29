<template>
    <data-list :columns="columns" :rows="rows">
        <div class="card p-0" slot-scope="{ filteredRows: rows }">
            <data-list-table :rows="rows">
                <template slot="cell-title" slot-scope="{ row: collection }">
                    <a :href="collection.entries_url">{{ collection.title }}</a>
                </template>
                <template slot="actions" slot-scope="{ row: collection, index }">
                    <dropdown-list>
                        <ul class="dropdown-menu">
                            <li><a :href="collection.edit_url">Edit</a></li>
                            <li class="warning" v-if="collection.deleteable">
                                <a @click.prevent="confirmDeleteRow(collection.id, index)">Delete</a>
                            </li>
                        </ul>
                    </dropdown-list>

                    <confirmation-modal
                        v-if="deletingRow !== false"
                        :title="deletingModalTitle"
                        :bodyText="__('Are you sure you want to delete this collection?')"
                        :buttonText="__('Delete')"
                        :danger="true"
                        @confirm="deleteRow('/cp/collections')"
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
