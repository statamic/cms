<template>
    <data-list :rows="rows" :columns="columns">
        <div class="card p-0" slot-scope="{ }">
            <data-table>
                <template slot="cell-title" slot-scope="{ row: group, index }">
                    <a :href="group.edit_url">{{ group.title }}</a>
                </template>
                <template slot="cell-handle" slot-scope="{ value: handle }">
                    <span class="font-mono text-xs">{{ handle }}</span>
                </template>
                <template slot="actions" slot-scope="{ row: group, index }">
                    <dropdown-list>
                        <li><a :href="group.edit_url">{{ __('Edit') }}</a></li>
                        <li class="warning"><a @click.prevent="destroy(group.id, index)">{{ __('Delete') }}</a></li>
                    </dropdown-list>
                </template>
            </data-table>
        </div>
    </data-list>
</template>

<script>
import axios from 'axios';

export default {

    props: {
        initialRows: Array,
    },

    data() {
        return {
            rows: this.initialRows,
            columns: ['title', 'handle', 'users', 'roles']
        }
    },

    methods: {

        destroy(id, index) {
            const url = cp_url(`user-groups/${id}`);
            axios.delete(url).then(response => {
                this.rows.splice(index, 1);
                this.$notify.success(__('User group deleted'));
            }).catch(error => {
                this.$notify.error(error.response.data.message);
            })
        }

    }

}
</script>
