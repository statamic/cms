module.exports = {

    mixins: [Dossier],

    data: function() {
        return {
            ajax: {
                get: cp_url('users/get'),
                search: cp_url('users/search'),
                delete: cp_url('users/delete'),
            },
            tableOptions: {
                sort: 'name',
                sortOrder: 'asc',
                partials: {
                    cell: `
                        <span :class="{ 'has-status-icon': $index === 0 }">
                            <span v-if="$index === 0" class="status status-{{ (item.status === 'active') ? 'live' : 'hidden' }}"
                                :title="(item.status === 'active') ? translate('cp.status_active') : translate('cp.status_pending')"
                            ></span>
                            <a v-if="column.link" :href="item.edit_url" class="has-status-icon">
                                {{{ formatValue(item[column.value]) }}}
                            </a>
                            <template v-else>
                                {{{ formatValue(item[column.value]) }}}
                            </template>
                        </span>`
                },
                checkboxes: Vue.can('users:delete')
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

            if (this.can('users:edit-passwords')) {
                str += `<li><a :href="item.edit_password_url">{{ translate('cp.change_password') }}</a></li>`;
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
