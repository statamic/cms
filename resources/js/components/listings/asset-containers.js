module.exports = {

    mixins: [Dossier],

    data: function() {
        return {
            ajax: {
                get: cp_url('assets/containers/get')
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
                        <a :href="item.browse_url">{{ item.title }}</a>`
                }
            }
        }
    }

};
