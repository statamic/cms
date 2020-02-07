<template>
    <data-list :rows="rows" :columns="columns">
        <div class="card p-0" slot-scope="{ filteredRows: rows }">
            <data-list-table>
                <template slot="cell-title" slot-scope="{ row: global }">
                    <a :href="global.edit_url">{{ global.title }}</a>
                </template>
                <template slot="cell-handle" slot-scope="{ value: handle }">
                    <span class="font-mono text-2xs">{{ handle }}</span>
                </template>
                <template slot="actions" slot-scope="{ row: global, index }">
                    <dropdown-list>
                        <dropdown-item :text="__('Edit')" :redirect="global.edit_url" />
                        <dropdown-item
                            v-if="global.deleteable"
                            :text="__('Delete')"
                            class="warning"
                            @click="$refs[`deleter_${global.id}`].confirm()"
                        >
                            <resource-deleter
                                :ref="`deleter_${global.id}`"
                                :resource="global"
                                @deleted="removeRow(global)">
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

    props: ['globals'],

    data() {
        return {
            rows: this.globals,
            columns: [
                { label: __('Title'), field: 'title' },
                { label: __('Handle'), field: 'handle' },
            ]
        }
    }

}
</script>
