<template>
    <data-list :visible-columns="columns" :columns="columns" :rows="rows">
        <div class="card p-0" slot-scope="{ filteredRows: rows }">
            <data-list-table>
                <template slot="cell-title" slot-scope="{ row: form }">
                    <a :href="form.show_url">{{ form.title }}</a>
                </template>
                <template slot="actions" slot-scope="{ row: form, index }">
                    <dropdown-list>
                        <dropdown-item :text="__('Edit')" :redirect="form.edit_url" />
                        <dropdown-item
                            v-if="form.deleteable"
                            :text="__('Delete')"
                            class="warning"
                            @click="confirmDeleteRow(form.id, index)" />
                    </dropdown-list>

                    <confirmation-modal
                        v-if="deletingRow !== false"
                        :title="deletingModalTitle"
                        :bodyText="__('Are you sure you want to delete this form?')"
                        :buttonText="__('Delete')"
                        :danger="true"
                        @confirm="deleteRow('forms')"
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

    props: ['forms'],

    data() {
        return {
            rows: this.forms,
            columns: [
                { field: 'title', label: __('Title') },
                { field: 'submissions', label: __('Submissions') },
            ]
        }
    }

}
</script>
