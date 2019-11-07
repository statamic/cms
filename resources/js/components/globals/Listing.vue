<template>
    <data-list :rows="rows" :columns="columns">
        <div class="card p-0" slot-scope="{ filteredRows: rows }">
            <data-list-table>
                <template slot="cell-title" slot-scope="{ row: global }">
                    <a :href="global.edit_url">{{ global.title }}</a>
                </template>
                <template slot="cell-handle" slot-scope="{ value: handle }">
                    <span class="font-mono text-2xs">{{ handle }}</span>
                </template>
                <template slot="actions" slot-scope="{ row: global, index }">
                    <dropdown-list>
                        <dropdown-item :text="__('Edit')" :redirect="global.edit_url" />
                        <dropdown-item
                            v-if="global.deleteable"
                            :text="__('Delete')"
                            class="warning"
                            @click="destroy(global.id, index)" />
                    </dropdown-list>
                </template>
            </data-list-table>
        </div>
    </data-list>
</template>

<script>
export default {

    props: ['globals'],

    data() {
        return {
            rows: this.globals,
            columns: [
                { label: __('Title'), field: 'title' },
                { label: __('Handle'), field: 'handle' },
            ]
        }
    },

    methods: {

        destroy(id, index) {
            const url = cp_url(`globals/${id}`);
            this.$axios.delete(url).then(response => {
                this.rows.splice(index, 1);
                this.$toast.success(__('Global Set deleted'));
            }).catch(error => {
                this.$toast.error(error.response.data.message);
            })
        }

    }
}
</script>
