<template>
    <div>

        <div v-if="loading" class="card loading">
            <loading-graphic />
        </div>

        <slot name="no-results" v-if="!loading && submissions.length === 0" />

        <data-list
            v-if="!loading"
            :columns="columns"
            :rows="submissions"
            :sort="false"
            :sort-column="sortColumn"
            :sort-direction="sortDirection"
        >
            <div class="card p-0" slot-scope="{ rows }">
                <data-list-table v-if="rows.length" @sorted="sorted">
                    <template slot="cell-datestamp" slot-scope="{ row: submission, value }">
                        <a :href="submission.url">{{ value }}</a>
                    </template>
                    <template slot="actions" slot-scope="{ row: submission, index }">
                        <dropdown-list>
                            <ul class="dropdown-menu">
                                <li><a :href="submission.url">View</a></li>
                                <li class="warning" v-if="submission.deleteable"><a @click.prevent="destroy(submission.id, index)">Delete</a></li>
                            </ul>
                        </dropdown-list>
                    </template>
                </data-list-table>
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
            submissions: [],
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
                // page: this.selectedPage
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
            const url = cp_url(`forms/${this.form}/submissions`);

            axios.get(url, { params: this.parameters }).then(response => {
                this.columns = response.data.meta.columns.map(column => column.field);
                this.sortColumn = response.data.meta.sortColumn;
                this.submissions = response.data.data;
                this.loading = false;
            });
        },

        sorted(column, direction) {
            this.sortColumn = column;
            this.sortDirection = direction;
        },

        destroy(id, index) {
            const url = cp_url(`forms/${this.form}/submissions/${id}`);
            axios.delete(url).then(response => {
                this.submissions.splice(index, 1);
                this.$notify.success(__('Submission deleted'));
            }).catch(error => {
                this.$notify.error(error.response.data.message);
            })
        },

    }

}
</script>
