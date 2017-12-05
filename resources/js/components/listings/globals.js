module.exports = {

    mixins: [Dossier],

    data: function() {
        return {
            ajax: {
                get: cp_url('globals/get')
            },
            tableOptions: {
                headers: false,
                search: false,
                checkboxes: false,
                sort: 'title',
                sortOrder: 'asc',
                partials: {
                    cell: `<a :href="item.edit_url">{{ item.title }}</a>`
                }
            }
        }
    }

};
