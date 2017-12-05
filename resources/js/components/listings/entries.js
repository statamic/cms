import HasLocaleSelector from '../HasLocaleSelector';
import HasShowDraftsSelector from '../HasShowDraftsSelector';

module.exports = {

    mixins: [HasLocaleSelector, HasShowDraftsSelector, Dossier],

    props: ['get', 'delete', 'reorder', 'search', 'canCreate', 'canDelete', 'sort', 'sortOrder', 'reorderable', 'collection', 'createEntryRoute'],

    data: function() {
        return {
            ajax: {
                get: this.get,
                delete: this.delete,
                reorder: this.reorder,
                search: this.search
            },
            tableOptions: {
                sort: this.sort,
                sortOrder: this.sortOrder,
                reorderable: this.reorderable,
                partials: {}
            }
        }
    },

    computed: {

        getParameters() {
            return {
                sort: this.sort,
                order: this.sortOrder,
                page: this.selectedPage,
                locale: this.locale,
                drafts: this.showDrafts ? 1 : 0
             };
        },

        createEntryUrl() {
            let url = this.createEntryRoute;

            if (this.locale !== Object.keys(Statamic.locales)[0]) {
                url += '?locale=' + this.locale;
            }

            return url;
        }

    },

    ready: function () {
        this.addActionPartial();
        this.bindLocaleWatcher();
        this.bindShowDraftsWatcher();
    },

    methods: {

        addActionPartial: function () {
            var str = `<li><a :href="item.edit_url">{{ translate('cp.edit') }}</a></li>`;

            if (this.canCreate) {
                str += `
                    <li>
                        <a href="#" @click.prevent="call('duplicate', item.id)">{{ translate('cp.duplicate') }}</a>
                    </li>`;
            }

            if (this.canDelete) {
                str += `
                    <li class="warning">
                        <a href="#" @click.prevent="call('deleteItem', item.id)">{{ translate('cp.delete') }}</a>
                    </li>`;
            }

            this.tableOptions.partials.actions = str;
        },

        onLocaleChanged() {
            this.getItems();
        },

        onShowDraftsChanged() {
            this.getItems();
        },

        duplicate(id) {
            const url = cp_url(`collections/entries/${this.collection}/duplicate`);

            this.$http.post(url, { id }).success((data) => {
                window.location = data.redirect;
            });
        }
    }

};
