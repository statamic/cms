    <template>
    <div>

        <div v-if="initializing" class="card loading">
            <loading-graphic />
        </div>

        <data-list
            v-if="!initializing"
            :columns="columns"
            :rows="users"
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
import axios from 'axios';

export default {

    props: {
        group: String,
        filters: Array,
        actions: Array,
        actionUrl: String
    },

    data() {
        return {
            initializing: true,
            loading: true,
            users: [],
            columns: [],
            sortColumn: null,
            sortDirection: 'asc',
            meta: null,
            page: 1,
            perPage: 25, // TODO: Should come from the controller, or a config.
            activeFilters: {},
        }
    },

    computed: {

        parameters() {
            return {
                group: this.group,
                sort: this.sortColumn,
                order: this.sortDirection,
                page: this.page,
                perPage: this.perPage,
                filters: btoa(JSON.stringify(this.activeFilters))
            }
        }

    },

    created() {
        this.request();
    },

    watch: {

        parameters() {
            this.request();
        }

    },

    methods: {

        request() {
            this.loading = true;
            const url = cp_url('users');

            axios.get(url, { params: this.parameters }).then(response => {
                this.columns = response.data.meta.columns.map(column => column.field);
                this.sortColumn = response.data.meta.sortColumn;
                this.users = response.data.data;
                this.meta = response.data.meta;
                this.loading = false;
                this.initializing = false;
            });
        },

        sorted(column, direction) {
            this.sortColumn = column;
            this.sortDirection = direction;
        },

        destroy(id, index) {
            const url = cp_url(`users/${id}`);
            axios.delete(url).then(response => {
                this.users.splice(index, 1);
                this.$notify.success(__('User deleted'));
            }).catch(error => {
                this.$notify.error(error.response.data.message);
            })
        },

        perPageChanged(perPage) {
            this.perPage = perPage;
            this.page = 1;
        },

        filtersChanged(filters) {
            this.activeFilters = filters;
            this.$refs.toggleAll.uncheckAllItems();
        },

        actionStarted() {
            this.loading = true;
        },

        actionCompleted() {
            this.request();
        }

    }

}
</script>
