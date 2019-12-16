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
            <div slot-scope="{ }">
                <div class="card p-0">
                    <data-list-table v-if="submissions.length" @sorted="sorted">
                        <template slot="cell-datestamp" slot-scope="{ row: submission, value }">
                            <a :href="submission.url" class="text-blue">{{ value }}</a>
                        </template>
                        <template slot="actions" slot-scope="{ row: submission, index }">
                            <dropdown-list>
                                <dropdown-item :text="__('View')" :redirect="submission.url" />
                                <dropdown-item
                                    v-if="submission.deleteable"
                                    :text="__('Delete')"
                                    class="warning"
                                    @click="destroy(submission.id, index)" />
                            </dropdown-list>
                        </template>
                    </data-list-table>
                </div>

                <data-list-pagination
                    class="mt-3"
                    :resource-meta="meta"
                    :per-page="perPage"
                    @page-selected="page = $event"
                />
            </div>
        </data-list>

    </div>
</template>

<script>
import HasPagination from '../data-list/HasPagination';
import HasPreferences from '../data-list/HasPreferences';

export default {

    mixins: [
        HasPagination,
        HasPreferences,
    ],

    props: {
        form: String
    },

    data() {
        return {
            loading: true,
            submissions: [],
            columns: [],
            sortColumn: null,
            sortDirection: 'asc',
            preferencesPrefix: `forms.${this.form}.submissions`,
        }
    },

    computed: {

        parameters() {
            return {
                sort: this.sortColumn,
                order: this.sortDirection,
                page: this.page,
                perPage: this.perPage,
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

            this.$axios.get(url, { params: this.parameters }).then(response => {
                this.columns = response.data.meta.columns;
                this.sortColumn = response.data.meta.sortColumn;
                this.submissions = response.data.data;
                this.meta = response.data.meta;
                this.loading = false;
            });
        },

        sorted(column, direction) {
            this.sortColumn = column;
            this.sortDirection = direction;
        },

        destroy(id, index) {
            const url = cp_url(`forms/${this.form}/submissions/${id}`);
            this.$axios.delete(url).then(response => {
                this.submissions.splice(index, 1);
                this.$toast.success(__('Submission deleted'));
            }).catch(error => {
                this.$toast.error(error.response.data.message);
            })
        },

    }

}
</script>
