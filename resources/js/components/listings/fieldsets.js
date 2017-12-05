module.exports = {

    mixins: [Dossier],

    data: function() {
        return {
            ajax: {
                get: cp_url('fieldsets/get'),
                delete: cp_url('fieldsets/delete')
            },
            tableOptions: {
                headers: false,
                search: false,
                checkboxes: false,
                sort: 'title',
                sortOrder: 'asc',
                partials: {
                    cell: `
                        <a v-if="$index === 0" :href="item.edit_url">
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

            if (this.can('fieldsets:manage')) {
                str += `<li><a :href="item.edit_url">{{ translate('cp.edit') }}</a></li>`;
            }

            if (this.can('fieldsets:delete')) {
                str += `
                    <li class="warning">
                        <a href="#" @click.prevent="call('deleteItem', item.id)">{{ translate('cp.delete') }}</a>
                    </li>`;
            }

            this.tableOptions.partials.actions = str;
        }
    }

};
