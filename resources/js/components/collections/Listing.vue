<template>
    <data-list ref="dataList" :columns="columns" :rows="items">
        <div class="card overflow-hidden p-0" slot-scope="{ filteredRows: rows }">
            <data-list-table :rows="rows">
                <template slot="cell-title" slot-scope="{ row: collection }">
                    <a :href="collection.available_in_selected_site ? collection.entries_url : collection.edit_url">{{ __(collection.title) }}</a>
                </template>
                <template slot="actions" slot-scope="{ row: collection, index }">
                    <dropdown-list placement="left-start">
                        <dropdown-item :text="__('View')" :redirect="collection.entries_url" />
                        <dropdown-item v-if="collection.url" :text="__('Visit URL')" :external-link="collection.url"  />
                        <dropdown-item v-if="collection.editable" :text="__('Edit Collection')" :redirect="collection.edit_url" />
                        <dropdown-item v-if="collection.blueprint_editable" :text="__('Edit Blueprints')" :redirect="collection.blueprints_url" />
                        <dropdown-item v-if="collection.editable" :text="__('Scaffold Views')" :redirect="collection.scaffold_url" />
                        <data-list-inline-actions
                            :item="collection.id"
                            :url="collection.actions_url"
                            :actions="collection.actions"
                            @completed="actionCompleted"
                        ></data-list-inline-actions>
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

    props: {
        initialRows: Array,
        initialColumns: Array,
    },

    data() {
        return {
            initializedRequest: false,
            items: this.initialRows,
            requestUrl: cp_url(`collections`),
        }
    },

    methods: {
        request() {
            // If we have initial data, we don't need to perform a request.
            // Subsequent requests, like after performing actions, we do want to perform a request.
            if (! this.initializedRequest) {
                this.loading = false;
                this.initializedRequest = true;
                return;
            }

            Listing.methods.request.call(this);
        }
    }

}
</script>
