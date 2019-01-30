    <template>
    <div>

        <div v-if="initializing" class="card loading">
            <loading-graphic />
        </div>

        <data-list
            v-if="!initializing"
            :columns="columns"
            :rows="items"
            :sort="false"
            :sort-column="sortColumn"
            :sort-direction="sortDirection"
        >
            <div slot-scope="{ }">
                <div class="card p-0">
                    <div class="data-list-header">
                        <data-list-toggle-all ref="toggleAll" />
                        <data-list-filters
                            :filters="filters"
                            :active-filters="activeFilters"
                            :per-page="perPage"
                            @filters-changed="filtersChanged"
                            @per-page-changed="perPageChanged" />
                    </div>
                    <data-list-bulk-actions
                        :url="actionUrl"
                        :actions="actions"
                        @started="actionStarted"
                        @completed="actionCompleted"
                    />
                    <data-list-table :allow-bulk-actions="true" @sorted="sorted">
                        <template slot="cell-name" slot-scope="{ row: user, value }">
                            <a :href="user.edit_url">{{ value }}</a>
                        </template>
                        <template slot="actions" slot-scope="{ row: user, index }">
                            <dropdown-list>
                                <div class="dropdown-menu">
                                    <div class="li"><a :href="user.edit_url">Edit</a></div>
                                    <data-list-inline-actions
                                        :item="user.id"
                                        :url="actionUrl"
                                        :actions="actions"
                                        @started="actionStarted"
                                        @completed="actionCompleted"
                                    />
                                </div>
                            </dropdown-list>
                        </template>
                    </data-list-table>
                </div>

                <data-list-pagination
                    class="mt-3"
                    :resource-meta="meta"
                    @page-selected="page = $event"
                />
            </div>
        </data-list>

    </div>
</template>

<script>
import Listing from '../Listing.vue';

export default {

    mixins: [Listing],

    props: {
        listingKey: String,
        group: String,
    },

    data() {
        return {
            requestUrl: cp_url('users'),
        }
    },

    computed: {

        additionalParameters() {
            return {
                group: this.group,
            }
        }

    }

}
</script>
