module.exports = {

    mixins: [Dossier],

    props: ['get', 'delete', 'keyword', 'canManage', 'canDelete'],

    data: function() {
        return {
            sort: 'title',
            sortOrder: 'asc',
            ajax: {
                get: this.get,
                delete: this.delete
            },
            tableOptions: {
                sort: this.sort,
                sortOrder: 'asc',
                partials: {}
            }
        }
    },

    ready: function () {
        this.addActionPartial();
    },

    methods: {
        addActionPartial: function () {
            var str = `<li><a :href="item.edit_url">{{ translate('cp.edit') }}</a></li>`;

            if (this.canDelete) {
                str += `
                    <li class="warning">
                        <a href="#" @click.prevent="call('deleteItem', item.id)">{{ translate('cp.delete') }}</a>
                    </li>`;
            }

            this.tableOptions.partials.actions = str;
        }
    }

};
