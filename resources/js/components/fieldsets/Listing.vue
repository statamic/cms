<template>
    <data-list :visible-columns="columns" :columns="columns" :rows="rows">
        <div class="card p-0" slot-scope="{ filteredRows: rows }">
            <data-list-table>
                <template slot="cell-title" slot-scope="{ row: fieldset }">
                    <a :href="fieldset.edit_url">{{ fieldset.title }}</a>
                </template>
                <template slot="cell-handle" slot-scope="{ value }">
                    <span class="font-mono text-xs">{{ value }}</span>
                </template>
                <template slot="actions" slot-scope="{ row: fieldset, index }">
                    <dropdown-list>
                        <dropdown-item :text="__('Edit')" :redirect="fieldset.edit_url" />
                        <dropdown-item :text="__('Delete')" class="warning" @click="confirmDeleteRow(fieldset.id, index)" />
                    </dropdown-list>
                </template>
            </data-list-table>

            <confirmation-modal
                v-if="deletingRow !== false"
                :title="deletingModalTitle"
                :bodyText="__('Are you sure you want to delete this fieldset?')"
                :buttonText="__('Delete')"
                :danger="true"
                @confirm="deleteRow('fields/fieldsets', __('Fieldset deleted'))"
                @cancel="cancelDeleteRow"
            />
        </div>
    </data-list>
</template>

<script>
import DeletesListingRow from '../DeletesListingRow';

export default {

    mixins: [DeletesListingRow],

    props: ['initialRows'],

    data() {
        return {
            rows: this.initialRows,
            columns: [
                { label: __('Title'), field: 'title' },
                { label: __('Handle'), field: 'handle' },
                { label: __('Fields'), field: 'fields' },
            ]
        }
    }
}
</script>
