<template>

    <div class="asset-manager">

        <asset-browser
            :selected-container="container"
            :selected-path="path"
            :selected-assets="selectedAssets"
            @navigated="navigate"
            @selections-updated="updateSelections">

            <template slot="contextual-actions" v-if="selectedAssets.length">
                    <button class="btn btn-danger ml-16 mr-16 mb-24" @click="deleteSelected">{{ translate('cp.delete') }}</button>
                    <div class="btn-group mb-24">
                        <button class="btn" @click="selectedAssets = []">{{ translate('cp.uncheck_all') }}</button>
                        <button class="btn" @click="openAssetMover">{{ translate('cp.move') }}</button>
                    </div>
            </template>

        </asset-browser>

        <mover
            v-if="showAssetMover"
            :assets="selectedAssets"
            :container="container"
            :folder="path"
            @saved="assetsMoved"
            @closed="closeAssetMover">
        </mover>

    </div>

</template>

<script>
export default {

    components: {
        Mover: require('./Mover.vue')
    },


    props: ['container', 'path'],


    data() {
        return {
            selectedAssets: [],
            showAssetMover: false
        }
    },


    ready() {
        this.bindBrowserNavigation();
    },


    methods: {

        /**
         * Bind browser navigation features
         *
         * This will initialize the state for using the history API to allow
         * navigation back and forth through folders using browser buttons.
         */
        bindBrowserNavigation() {
            window.history.replaceState({ container: this.container, path: this.path }, '');

            window.onpopstate = (e) => {
                this.container = e.state.container;
                this.path = e.state.path;
            };
        },

        /**
         * Push a new state onto the browser's history
         */
        pushState() {
            let url = cp_url('assets/browse/' + this.container);

            if (this.path !== '/') {
                url += '/' + this.path;
            }

            window.history.pushState({
                container: this.container, path: this.path
            }, '', url);
        },

        /**
         * When a user has navigated to another folder or container
         */
        navigate(container, path) {
            this.container = container;
            this.path = path;
            this.pushState();

            // Clear out any selections. It would be confusing to navigate to a different
            // folder and/or container, perform an action, and discover you performed
            // it on an asset that was still selected, but no longer visible.
            this.selectedAssets = [];
        },

        /**
         * When selections are changed, we need them reflected here.
         */
        updateSelections(selections) {
            this.selectedAssets = selections;
        },

        /**
         * Delete all the selected assets.
         */
        deleteSelected() {
            this.$broadcast('delete-assets', this.selectedAssets);
        },

        openAssetMover() {
            this.showAssetMover = true;
        },

        closeAssetMover() {
            this.showAssetMover = false;
        },

        assetsMoved(folder) {
            this.closeAssetMover();
            this.navigate(this.container, folder);
        }
    }

}
</script>
