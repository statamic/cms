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
            <div class="card p-0" slot-scope="{ filteredRows: rows }">
                <data-table @sorted="sorted">
                    <template slot="cell-username" slot-scope="{ row: user, value }">
                        <a :href="user.edit_url">{{ value }}</a>
                    </template>
                </data-table>
            </div>
        </data-list>

    </div>
</template>

<script>
import axios from 'axios';

export default {

    props: {
        form: String
    },

    data() {
        return {
            loading: true,
            users: [],
            columns: [],
            sortColumn: null,
            sortDirection: 'asc'
        }
    },

    computed: {

        parameters() {
            return {
                sort: this.sortColumn,
                order: this.sortDirection,
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
                this.loading = false;
            });
        },

        sorted(column, direction) {
            this.sortColumn = column;
            this.sortDirection = direction;
        }

    }

}
</script>
