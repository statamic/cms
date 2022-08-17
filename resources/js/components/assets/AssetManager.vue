<template>

    <div class="asset-manager">

        <div class="flex items-center mb-3">
            <h1 class="flex-1">{{ container.title }}</h1>

            <dropdown-list v-if="container.can_edit || container.can_delete" class="ml-2">
                <dropdown-item
                    v-if="container.can_edit"
                    v-text="__('Edit Container')"
                    :redirect="container.edit_url">
                </dropdown-item>
                <dropdown-item
                    v-text="__('Edit Blueprint')"
                    :redirect="container.blueprint_url">
                </dropdown-item>
                <dropdown-item
                    v-if="container.can_delete"
                    v-text="__('Delete Container')"
                    class="warning"
                    @click="$refs.deleter.confirm()"
                >
                    <resource-deleter
                        ref="deleter"
                        :resource-title="container.title"
                        :route="container.delete_url">
                    </resource-deleter>
                </dropdown-item>
            </dropdown-list>

            <a :href="createContainerUrl" class="btn ml-2" v-if="canCreateContainers">{{ __('Create Container') }}</a>
        </div>

        <asset-browser
            ref="browser"
            :initial-container="container"
            :initial-per-page="$config.get('paginationSize')"
            :initial-editing-asset-id="initialEditingAssetId"
            :selected-path="path"
            :selected-assets="selectedAssets"
            @navigated="navigate"
            @selections-updated="updateSelections"
            @asset-doubleclicked="editAsset"
            @edit-asset="editAsset" />

    </div>

</template>

<script>
export default {

    props: {
        initialContainer: Object,
        initialPath: String,
        initialEditingAssetId: String,
        actions: Array,
        canCreateContainers: Boolean,
        createContainerUrl: String,
    },

    data() {
        return {
            container: this.initialContainer,
            path: this.initialPath,
            selectedAssets: [],
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

        editAsset(asset) {
            event.preventDefault()
            this.$refs.browser.edit(asset.id);
        }

    }

}
</script>
