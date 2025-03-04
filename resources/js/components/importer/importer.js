import { each, size, uniq, includes } from 'lodash-es';

export default {
    props: ['importer'],

    data: function () {
        return {
            exporting: false,
            exported: false,
            exportFailed: false,
            exportError: null,
            importing: false,
            imported: false,
            summary: null,
            showAllPages: false,
            showCollections: [],
            showTaxonomies: [],
            showGlobals: [],
        };
    },

    computed: {
        totalPages: function () {
            return Object.keys(this.summary.pages).length;
        },
    },

    mounted() {
        this.summary = Statamic.ImportSummary;
    },

    methods: {
        import: function () {
            this.importing = true;
            this.$http.post(cp_url('import/import'), { summary: this.summary }).success(function (response) {
                this.importing = false;
                this.imported = true;
                console.log(response);
            });
        },

        hasDuplicates(collection) {
            return !!this.duplicateCount(collection);
        },

        duplicateCount: function (items) {
            let count = 0;

            each(items, (item) => {
                if (!item.exists) {
                    return;
                }

                count++;
            });

            return count;
        },

        uncheckDuplicates: function (items) {
            each(items, (item) => {
                if (!item.exists) {
                    return;
                }

                item._checked = false;
            });
        },

        size: function (obj) {
            return size(obj);
        },

        showCollection: function (collection) {
            this.showCollections.push(collection);
            uniq(this.showCollections);
        },

        hideCollection: function (hidden) {
            this.showCollections = this.showCollections.filter(function (c) {
                return c !== hidden;
            });
        },

        shouldShowCollection: function (collection) {
            return includes(this.showCollections, collection);
        },

        showTaxonomy: function (taxonomy) {
            this.showTaxonomies.push(taxonomy);
            uniq(this.showTaxonomies);
        },

        hideTaxonomy: function (hidden) {
            this.showTaxonomies = this.showTaxonomies.filter(function (t) {
                return t !== hidden;
            });
        },

        shouldShowTaxonomy: function (taxonomy) {
            return contains(this.showTaxonomies, taxonomy);
        },

        showGlobal: function (global) {
            this.showGlobals.push(global);
            uniq(this.showGlobals);
        },

        hideGlobal: function (hidden) {
            this.showGlobals = this.showGlobals.filter(function (g) {
                return g !== hidden;
            });
        },

        shouldShowGlobal: function (global) {
            return contains(this.showGlobals, global);
        },
    },
};
