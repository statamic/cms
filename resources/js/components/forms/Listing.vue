<template>
    <data-list
        :visible-columns="columns"
        :columns="columns"
        :rows="items"
    >
        <div slot-scope="{ filteredRows: rows }">
            <div class="card p-0 relative overflow-x-auto">
                <div class="sticky top-0 right-0 left-0 z-10 w-full">
                    <data-list-bulk-actions
                        class="rounded data-list-header"
                        :url="actionUrl"
                        @started="actionStarted"
                        @completed="actionCompleted"
                    />
                </div>

                <data-list-table :allow-bulk-actions="true">
                    <template slot="cell-title" slot-scope="{ row: form }">
                        <a :href="form.show_url">{{ form.title }}</a>
                    </template>
                    <template slot="actions" slot-scope="{ row: form, index }">
                        <dropdown-list>
                            <dropdown-item :text="__('Edit')" :redirect="form.edit_url" />
                            <dropdown-item :text="__('Edit Blueprint')" :redirect="form.blueprint_url" />
                            <div class="divider" v-if="form.actions.length" />
                            <data-list-inline-actions
                                :item="form.id"
                                :url="actionUrl"
                                :actions="form.actions"
                                @started="actionStarted"
                                @completed="actionCompleted"
                            />
                        </dropdown-list>
                    </template>
                </data-list-table>
            </div>
        </div>
    </data-list>
</template>

<script>
import Listing from '../Listing.vue'

export default {

    mixins: [Listing],

    props: ['initialColumns'],

    data() {
        return {
            columns: this.initialColumns,
            requestUrl: cp_url('forms'),
        }
    }

}
</script>
