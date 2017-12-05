module.exports = {

    mixins: [Dossier],

    props: ['get'],

    data: function() {
        return {
            sort: 'datestamp',
            sortOrder: 'desc',
            ajax: {
                get: this.get
            },
            tableOptions: {
                checkboxes: false,
                sort: 'datestamp',
                sortOrder: 'desc',
                partials: {
                    cell: `
                        <a v-if="$index === 0" :href="item.edit_url">
                            {{ item[column.label] }}
                        </a>
                        <template v-else>
                            {{{ item[column.label] }}}
                        </template>`
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
                <li><a :href="item.delete_url">{{ translate('cp.delete') }}</a></li>
            `;

            this.tableOptions.partials.actions = str;
        }
    }


};
