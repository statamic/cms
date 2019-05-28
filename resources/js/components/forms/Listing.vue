<template>
    <data-list :visible-columns="columns" :columns="columns" :rows="forms">
        <div class="card p-0" slot-scope="{ filteredRows: rows }">
            <data-list-table>
                <template slot="cell-title" slot-scope="{ row: form }">
                    <a :href="form.show_url">{{ form.title }}</a>
                </template>
                <template slot="actions" slot-scope="{ row: form }">
                    <dropdown-list>
                        <ul class="dropdown-menu">
                            <li><a :href="form.edit_url">Edit</a></li>
                            <li class="warning" v-if="form.deleteable">
                                <a @click.prevent="destroy(form.id)">Delete</a>
                            </li>
                        </ul>
                    </dropdown-list>
                </template>
            </data-list-table>
        </div>
    </data-list>
</template>

<script>
export default {

    props: ['forms'],

    data() {
        return {
            columns: [
                { field: 'title', label: __('Title') },
                { field: 'submissions', label: __('Submissions') },
            ]
        }
    },

    methods: {

        destroy(id) {
            if (confirm('Are you sure?')) {
                this.$axios.delete(`/cp/forms/${id}`);
            }
        }

    }
}
</script>
