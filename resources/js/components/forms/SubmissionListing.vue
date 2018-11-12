<template>
    <div>

        <div v-if="loading" class="card loading">
            <loading-graphic />
        </div>

        <data-list v-if="!loading" :visible-columns="columns" :columns="columns" :rows="submissions">
            <div class="card p-0" slot-scope="{ filteredRows: rows }">
                <data-table>
                    <template slot="cell-date" slot-scope="{ row: submission, value }">
                        <a :href="submission.edit_url">{{ value }}</a>
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
            submissions: [],
            columns: ['date', 'name', 'email']
        }
    },

    created() {
        axios.get(cp_url(`forms/${this.form}/submissions`)).then(response => {
            this.submissions = response.data.data;
            this.loading = false;
        })
    }

}
</script>
