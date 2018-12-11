<template>
    <div>

        <div v-if="loading" class="card loading">
            <loading-graphic />
        </div>

        <data-list
            v-if="!loading"
            :columns="columns"
            :rows="users"
            :sort="false"
            :sort-column="sortColumn"
            :sort-direction="sortDirection"
        >
            <div slot-scope="{ filteredRows: rows }">
                <div class="card p-0">
                    <data-table @sorted="sorted">
                        <template slot="cell-name" slot-scope="{ row: user, value }">
                            <a :href="user.edit_url">{{ value }}</a>
                        </template>
                        <template slot="actions" slot-scope="{ row: user, index }">
                            <dropdown-list>
                                <li><a :href="user.edit_url">Edit</a></li>
                                <li class="warning"><a @click.prevent="destroy(user.id, index)">Delete</a></li>
                            </dropdown-list>
                        </template>
                    </data-table>
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
    },

    data() {
        return {
            loading: true,
            users: [],
            columns: [],
            sortColumn: null,
            sortDirection: 'asc',
            meta: null,
            page: 1
        }
    },

    computed: {

        parameters() {
            return {
                group: this.group,
                sort: this.sortColumn,
                order: this.sortDirection,
                page: this.page,
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
        }

    }

}
</script>
