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
                        <dropdown-item :text="__('Edit')" :redirect="group.edit_url" />
                        <dropdown-item :text="__('Delete')" class="warning" @click="destroy(group.id, index)" />
                    </dropdown-list>
                </template>
            </data-list-table>
        </div>
    </data-list>
</template>

<script>
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
            this.$axios.delete(url).then(response => {
                this.rows.splice(index, 1);
                this.$toast.success(__('User Group deleted'));
            }).catch(error => {
                this.$toast.error(error.response.data.message);
            })
        }

    }

}
</script>
