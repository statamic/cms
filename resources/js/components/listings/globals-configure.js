module.exports = {

    mixins: [Dossier],

    data: function() {
        return {
            ajax: {
                get: cp_url('globals/get'),
                delete: cp_url('configure/content/globals/delete')
            },
            tableOptions: {
                headers: false,
                search: false,
                checkboxes: false,
                sort: 'title',
                sortOrder: 'asc',
                partials: {
                    cell: `<a :href="cp_url('configure/content/globals/')+item.slug">{{ item.title }}</a>`
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

            if (this.can('globals:manage')) {
                str += `<li><a :href="cp_url('configure/content/globals/')+item.slug">{{ translate('cp.edit') }}</a></li>`;
            }

            if (this.can('globals:delete')) {
                str += `
                    <li class="warning">
                        <a href="#" @click.prevent="call('deleteItem', item.id)">{{ translate('cp.delete') }}</a>
                    </li>`;
            }

            this.tableOptions.partials.actions = str;
        }
    }

};
