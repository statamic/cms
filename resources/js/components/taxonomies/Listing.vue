<template>
    <data-list :columns="columns" :rows="rows">
        <div class="card p-0" slot-scope="{ filteredRows: rows }">
            <data-list-table :rows="rows">
                <template slot="cell-title" slot-scope="{ row: taxonomies }">
                    <a :href="taxonomies.terms_url">{{ taxonomies.title }}</a>
                </template>
                <template slot="actions" slot-scope="{ row: taxonomies, index }">
                    <dropdown-list>
                        <dropdown-item :text="__('Edit')" :redirect="taxonomies.edit_url" />
                        <dropdown-item
                            v-if="taxonomies.deleteable"
                            :text="__('Delete')"
                            class="warning"
                            @click="confirmDeleteRow(taxonomies.id, index)" />
                    </dropdown-list>

                    <confirmation-modal
                        v-if="deletingRow !== false"
                        :title="deletingModalTitle"
                        :bodyText="__('Are you sure you want to delete this taxonomy?')"
                        :buttonText="__('Delete')"
                        :danger="true"
                        @confirm="deleteRow('taxonomies')"
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
