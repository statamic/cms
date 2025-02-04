<template>
    <data-list :visible-columns="columns" :columns="columns" :rows="items" v-slot="{ filteredRows: rows }">
        <div class="card overflow-hidden p-0 relative">
            <data-list-bulk-actions
                class="rounded"
                :url="actionUrl"
                @started="actionStarted"
                @completed="actionCompleted"
            />

            <data-list-table :allow-bulk-actions="true">
                <template #cell-title="{ row: form }">
                    <a :href="form.show_url">{{ form.title }}</a>
                </template>
                <template #actions="{ row: form, index }">
                    <dropdown-list v-if="form.can_edit || form.can_edit_blueprint || form.actions.length">
                        <dropdown-item v-if="form.can_edit" :text="__('Edit')" :redirect="form.edit_url" />
                        <dropdown-item v-if="form.can_edit_blueprint" :text="__('Edit Blueprint')" :redirect="form.blueprint_url" />
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
