<template>
    <div>
        <Header :title="__(container.title)">
            <dropdown-list v-if="container.can_edit || container.can_delete">
                <dropdown-item v-if="container.can_edit" v-text="__('Edit Container')" :redirect="container.edit_url" />
                <dropdown-item v-text="__('Edit Blueprint')" :redirect="container.blueprint_url" />
                <dropdown-item v-if="container.can_delete" class="warning" @click="$refs.deleter.confirm()">
                    {{ __('Delete Container') }}
                    <resource-deleter
                        ref="deleter"
                        :resource-title="__(container.title)"
                        :route="container.delete_url"
                    />
                </dropdown-item>
            </dropdown-list>

            <!-- @TODO: Move Create Container into the action dropdown -->
            <!-- <Button
                v-if="canCreateContainers"
                :href="createContainerUrl"
                :text="__('Create Container')"
            /> -->

            <!-- @TODO: Make this work -->
            <Button
                :text="__('Upload')"
                icon="upload"
                variant="primary"
                @click="uploadAsset"
            />

            <!-- @TODO: Make this work -->
            <Button
                :text="__('Create Folder')"
                icon="folder-add"
                @click="createFolder"
            />

            <ui-toggle-group>
                <ui-toggle-item icon="layout-grid" value="grid" />
                <ui-toggle-item icon="layout-grid-compact" value="grid-compact" />
                <ui-toggle-item icon="layout-list" value="list" />
            </ui-toggle-group>
        </Header>

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
            @edit-asset="editAsset"
        />
    </div>
</template>

<script>
import { Header, Button } from '@statamic/ui';

export default {
    components: {
        Header,
        Button,
    },

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
        };
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
            window.history.replaceState({ container: { ...this.container }, path: this.path }, '');

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

            window.history.pushState(
                {
                    container: { ...this.container },
                    path: this.path,
                },
                '',
                url,
            );
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
            event.preventDefault();
            this.$refs.browser.edit(asset.id);
        },
    },
};
</script>
