<script>
import Head from '@/pages/layout/Head.vue';
import { DocsCallout } from '@ui';

export default {
    components: {
        Head,
        DocsCallout,
    },

    props: ['container', 'folder', 'columns', 'canCreateContainers', 'createContainerUrl', 'editing'],

    data() {
        return {
            path: this.folder,
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
                this.path = e.state.path;
            };
        },

        editAsset(asset) {
            event.preventDefault();
            this.$refs.browser.edit(asset.id);
        },

        /**
         * When a user has navigated to another folder.
         */
        navigate(path) {
            let previousPath = this.path;

            this.path = path;
            this.pushState();

            // Clear out any selections. It would be confusing to navigate to a different
            // folder, perform an action, and discover you performed it on an asset that
            // was still selected, but no longer visible. We only want to do this when
            // navigating to/from folders, not when navigating between assets.
            if (! path.includes('/edit') && ! previousPath.includes('/edit')) {
                this.selectedAssets = [];
            }
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
         * When selections are changed, we need them reflected here.
         */
        updateSelections(selections) {
            this.selectedAssets = selections;
        },
    },
};
</script>

<template>
    <div class="h-full" v-cloak>
        <Head :title="container.title" />

        <asset-browser
            ref="browser"
            :can-create-containers="canCreateContainers"
            :create-container-url="createContainerUrl"
            :container="container"
            :initial-per-page="$config.get('paginationSize')"
            :initial-editing-asset-id="editing"
            :selected-path="path"
            :selected-assets="selectedAssets"
            :initial-columns="columns"
            @navigated="navigate"
            @selections-updated="updateSelections"
            @edit-asset="editAsset"
        >
            <template #initializing>
                <!-- Header Skeleton -->
                <div class="flex justify-between py-8">
                    <ui-skeleton class="h-8 w-95" />
                    <div class="flex gap-2 sm:gap-3">
                        <ui-skeleton class="h-10 w-26" />
                        <ui-skeleton class="h-10 w-36" />
                        <ui-skeleton class="h-10 w-20" />
                    </div>
                </div>
                <!-- Toolbar Skeleton -->
                <div class="flex justify-between py-3">
                    <ui-skeleton class="h-9 w-95" />
                    <ui-skeleton class="h-9 w-10" />
                </div>
                <!-- Assets grid Skeleton -->
                <div class="flex justify-between">
                    <ui-skeleton class="h-30 w-full" />
                </div>
            </template>
        </asset-browser>
    </div>
</template>