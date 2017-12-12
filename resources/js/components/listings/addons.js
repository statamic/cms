module.exports = {

    mixins: [Dossier],

    data: function() {
        return {
            ajax: {
                get: cp_url('addons/get')
            },
            tableOptions: {
                checkboxes: false,
                partials: {
                    cell: `
                        <a :href="item.url" _target="blank" v-if="item.url && column.field === 'name'">{{ item[column.label] }}</a>
                        <template v-else>
                            {{ item[column.label] }}
                        </template>
                    `
                }
            }
        }
    }

};
