module.exports = {

    mixins: [Dossier],

    data: function() {
        return {
            ajax: {
                get: cp_url('users/roles/get'),
                delete: cp_url('users/roles/delete')
            },
            tableOptions: {
                sort: 'title',
                sortOrder: 'asc',
                partials: {
                    cell: `
                        <a v-if="$index === 0" :href="item.edit_url">
                            {{ item[column.value] }}
                        </a>
                        <template v-else>
                            {{ item[column.value] }}
                        </template>`
                }
            }
        }
    },

    mounted() {
        this.addActionPartial();
    },

    methods: {
        addActionPartial: function () {
            var str = '';

            if (this.can('users:edit')) {
                str += `<li><a :href="item.edit_url">{{ translate('cp.edit') }}</a></li>`;
            }

            if (this.can('users:delete')) {
                str += `
                    <li class="warning">
                        <a href="#" @click.prevent="call('deleteItem', item.id)">{{ translate('cp.delete') }}</a>
                    </li>`;
            }

            this.tableOptions.partials.actions = str;
        }
    }

};
