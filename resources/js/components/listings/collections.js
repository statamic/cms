module.exports = {

    mixins: [Dossier],

    data: function() {
        return {
            ajax: {
                get: cp_url('collections/get')
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
                            <span class="icon icon-documents"></span>
                            {{ item.entries }}
                        </div>
                        <a :href="item.entries_url">{{ item.title }}</a>

                        <a href="{{ item.create_url }}" v-if="can('collections:'+item.id+':create')"
                           class="btn btn-icon btn-primary pull-right"><span class="icon icon-plus"></span>
                       </a>`
                }
            }
        }
    },

    ready: function () {
        if (this.can('super')) {
            this.addActionPartial();
        }
    },

    methods: {
        addActionPartial: function () {
            var str = `
                <li><a :href="item.edit_url">{{ translate('cp.edit') }}</a></li>
            `;

            this.tableOptions.partials.actions = str;
        }
    }

};
