module.exports = {

    mixins: [Dossier],

    data: function() {
        return {
            ajax: {
                get: cp_url('users/get'),
                delete: cp_url('users/delete')
            },
            tableOptions: {
                sort: 'name',
                sortOrder: 'asc',
                partials: {
                    cell: `
                        <a v-if="$index === 0" :href="item.edit_url">
                            <span class="status status-{{ (item.status === 'active') ? 'live' : 'hidden' }}"
                                  :title="(item.status === 'active') ? translate('cp.status_active') : translate('cp.status_pending')"
                            ></span>
                            {{ item[column.label] }}
                        </a>
                        <template v-else>
                            {{ item[column.label] }}
                        </template>`
                }
            }
        }
    },

    ready: function () {
        this.addActionPartial();
    },

    methods: {
        addActionPartial: function () {
            var str = '';

            if (this.can('users:manage')) {
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
