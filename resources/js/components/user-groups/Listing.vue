<template>
    <data-list :rows="rows" :columns="columns">
        <div class="card p-0" slot-scope="{ }">
            <data-list-table>
                <template slot="cell-title" slot-scope="{ row: group, index }">
                    <a :href="group.edit_url">{{ group.title }}</a>
                </template>
                <template slot="cell-handle" slot-scope="{ value: handle }">
                    <span class="font-mono text-xs">{{ handle }}</span>
                </template>
                <template slot="actions" slot-scope="{ row: group, index }">
                    <dropdown-list>
                        <ul class="dropdown-menu">
                            <li><a :href="group.edit_url">{{ __('Edit') }}</a></li>
                            <li class="warning"><a @click.prevent="destroy(group.id, index)">{{ __('Delete') }}</a></li>
                        </ul>
                    </dropdown-list>
                </template>
            </data-list-table>
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
            columns: [
                { label: __('Title'), field: 'title' },
                { label: __('Handle'), field: 'handle' },
                { label: __('Users'), field: 'users' },
                { label: __('Roles'), field: 'roles' },
            ]
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
