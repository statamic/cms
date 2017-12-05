module.exports = {

    mixins: [Dossier],

    data: function() {
        return {
            ajax: {
                get: cp_url('assets/containers/get'),
                delete: cp_url('configure/content/assets')
            },
            tableOptions: {
                headers: false,
                search: false,
                checkboxes: false,
                sort: 'title',
                sortOrder: 'asc',
                partials: {
                    cell: `
                        <div class="stat">
                            <span class="icon icon-folder-images"></span>
                            {{ item.assets }}
                        </div>
                        <a :href="item.edit_url">{{ item.title }}</a>`
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

            if (this.can('assets:manage')) {
                str += `<li><a :href="item.edit_url">{{ translate('cp.edit') }}</a></li>`;
            }

            if (this.can('assets:delete')) {
                str += `
                    <li class="warning">
                        <a href="#" @click.prevent="call('deleteItem', item.id)">{{ translate('cp.delete') }}</a>
                    </li>`;
            }

            this.tableOptions.partials.actions = str;
        }
    }

};
