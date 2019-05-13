<template>

    <div class="asset-manager">

        <div class="flex mb-3">
            <h1 class="flex-1">{{ container.title }}</h1>

            <a :href="container.edit_url" class="btn">{{ __('Edit') }}</a>
            <a :href="createContainerUrl" class="btn-primary ml-2" v-if="canCreateContainers">{{ __('Create Container') }}</a>
        </div>

        <asset-browser
            ref="browser"
            :initial-container="container"
            :selected-path="path"
            :selected-assets="selectedAssets"
            :actions="actions"
            :action-url="actionUrl"
            @navigated="navigate"
            @selections-updated="updateSelections"
            @asset-doubleclicked="editAsset"
        >

            <template slot="actions" slot-scope="{ ids }">
                <button class="btn ml-1" @click="openAssetMover">{{ __('Move') }}</button>
                <button class="btn btn-danger ml-1" @click="destroyMultiple(ids)">{{ __('Delete') }}</button>
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


    props: {
        initialContainer: Object,
        initialPath: String,
        actions: Array,
        actionUrl: String,
        canCreateContainers: Boolean,
        createContainerUrl: String,
    },


    data() {
        return {
            container: this.initialContainer,
            path: this.initialPath,
            selectedAssets: [],
            showAssetMover: false
        }
    },


    mounted() {
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
            let url = cp_url('assets/browse/' + this.container.id);

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

        destroyMultiple(ids) {
            this.$refs.browser.destroyMultiple(ids);
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
        },

        editAsset(asset) {
            this.$refs.browser.edit(asset.id);
        }
    }

}
</script>
