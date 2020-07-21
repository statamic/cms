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
                        <dropdown-item :text="__('Edit Blueprint')" :redirect="form.blueprint_url" />
                        <dropdown-item
                            v-if="form.deleteable"
                            :text="__('Delete')"
                            class="warning"
                            @click="$refs[`deleter_${form.id}`].confirm()"
                        >
                            <resource-deleter
                                :ref="`deleter_${form.id}`"
                                :resource="form"
                                @deleted="removeRow(form)">
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
