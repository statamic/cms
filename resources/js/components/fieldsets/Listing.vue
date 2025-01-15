<template>
    <data-list :visible-columns="columns" :columns="columns" :rows="rows" v-slot="{ filteredRows: rows }">
        <div class="card overflow-hidden p-0 relative">
            <div class="overflow-x-auto overflow-y-hidden">
                <data-list-table>
                    <template #cell-title="{ row: fieldset }">
                        <a :href="fieldset.edit_url">{{ __(fieldset.title) }}</a>
                    </template>
                    <template #cell-handle="{ value }">
                        <span class="font-mono text-xs">{{ value }}</span>
                    </template>
                    <template #actions="{ row: fieldset, index }">
                        <dropdown-list>
                            <dropdown-item :text="__('Edit')" :redirect="fieldset.edit_url" />
                            <dropdown-item
                                v-if="fieldset.is_resettable"
                                :text="__('Reset')"
                                class="warning"
                                @click="$refs[`resetter_${fieldset.id}`].confirm()"
                            >
                                <fieldset-resetter
                                    :ref="`resetter_${fieldset.id}`"
                                    :resource="fieldset"
                                    :reload="true"
                                >
                                </fieldset-resetter>
                            </dropdown-item>
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
        </div>
    </data-list>
</template>

<script>
import Listing from '../Listing.vue';
import FieldsetDeleter from './FieldsetDeleter.vue';
import FieldsetResetter from './FieldsetResetter.vue';

export default {

    mixins: [Listing],

    components: {
        FieldsetDeleter,
        FieldsetResetter
    },

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
