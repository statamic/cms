export default {

    data() {
        return {
            selectedAssets: [],
            selectorViewMode: null,
            showAssetSelector: false
        };
    },

    computed: {
        assetsEnabled: function () {
            return this.getFieldtypeConfig() && typeof this.getFieldtypeConfig().container !== 'undefined';
        },

        container: function () {
            return this.getFieldtypeConfig().container;
        },

        folder: function () {
            return this.getFieldtypeConfig().folder || '/';
        },

        restrictAssetNavigation() {
            return this.getFieldtypeConfig().restrict_assets || false;
        }
    },

    ready() {
        this.selectorViewMode = Cookies.get('statamic.assets.listing_view_mode') || 'grid';
    },

    methods: {

        addAsset: function () {
            this.showAssetSelector = true
        },

        closeAssetSelector() {
            this.showAssetSelector = false;
        },

    }

}