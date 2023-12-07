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
                        <dropdown-item
                            v-if="fieldset.is_deletable"
                            :text="__('Delete')"
                            class="warning"
                            @click="$refs[`deleter_${fieldset.id}`].confirm()"
                        >
                            <fieldset-deleter
                                :ref="`deleter_${fieldset.id}`"
                                :resource="fieldset"
                                @deleted="removeRow(fieldset)">
                            </fieldset-deleter>
                        </dropdown-item>
                    </dropdown-list>
                </template>
            </data-list-table>
        </div>
    </data-list>
</template>

<script>
import Listing from '../Listing.vue';
import FieldsetDeleter from './FieldsetDeleter.vue';

export default {

    mixins: [Listing],

    components: {FieldsetDeleter},

    props: ['initialRows'],

    data() {
        return {
            rows: this.initialRows,
            columns: [
                { label: __('Title'), field: 'title' },
                { label: __('Handle'), field: 'handle', width: '25%' },
                { label: __('Fields'), field: 'fields', width: '15%' },
            ]
        }
    }
}
</script>
