<template>
    <data-list :columns="columns" :rows="rows">
        <div class="card p-0" slot-scope="{ filteredRows: rows }">
            <data-list-table :rows="rows">
                <template slot="cell-title" slot-scope="{ row: structure }">
                    <a :href="structure.show_url" class="flex items-center">
                        <svg-icon :name="structure.purpose === 'collection' ? 'list-bullets' : 'hierarchy-files'" class="w-4 h-4 text-grey-60 inline-block mr-2" />
                        {{ structure.title }}
                    </a>
                </template>
                <template slot="actions" slot-scope="{ row: structure, index }">
                    <dropdown-list>
                        <dropdown-item :text="__('Edit')" :redirect="structure.edit_url" />
                        <dropdown-item
                            v-if="structure.deleteable"
                            :text="__('Delete')"
                            class="warning"
                            @click="confirmDeleteRow(structure.id, index)" />
                    </dropdown-list>

                    <confirmation-modal
                        v-if="deletingRow !== false"
                        :title="deletingModalTitle"
                        :bodyText="__('Are you sure you want to delete this structure?')"
                        :buttonText="__('Delete')"
                        :danger="true"
                        @confirm="deleteRow('structures')"
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
        'initialRows',
    ],

    data() {
        return {
            rows: this.initialRows,
            columns: [
                { label: __('Title'), field: 'title', visible: true },
            ]
        }
    }

}
</script>
