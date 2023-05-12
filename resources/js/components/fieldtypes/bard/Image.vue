<template>

    <node-view-wrapper>
        <div class="bard-inline-image-container">
            <div v-if="src" class="p-2 pb-0 text-center" draggable="true" data-drag-handle>
                <div ref="content" hidden />
                <img :src="src" class="block mx-auto" />
            </div>

            <div id="asset-editor-toolbar" class="@container/toolbar flex items-center justify-center py-4 px-2 text-2xs text-white text-center space-x-1 sm:space-x-3">
                <button v-if="!src" @click="openSelector" type="button" class="flex bg-gray-750 hover:bg-gray-900 hover:text-yellow-light rounded items-center px-3 py-1.5">
                    <svg-icon name="folder-image" class="h-4" />
                    <span class="ml-2 @3xl/toolbar:inline-block">{{ __('Chose Image') }}</span>
                </button>
                <button v-if="src" @click="openSelector" type="button" class="flex bg-gray-750 hover:bg-gray-900 hover:text-yellow-light rounded items-center px-3 py-1.5">
                    <svg-icon name="swap" class="h-4" />
                    <span class="ml-2 @3xl/toolbar:inline-block">{{ __('Replace') }}</span>
                </button>
                <button @click="deleteNode" class="flex bg-gray-750 hover:bg-gray-900 hover:text-red-400 rounded items-center text-center px-3 py-1.5">
                    <svg-icon name="trash" class="h-4" />
                    <span class="ml-2 @3xl/toolbar:inline-block">{{ __('Delete') }}</span>
                </button>
            </div>

            <stack
                v-if="showingSelector"
                name="asset-selector"
                @closed="closeSelector"
            >
                <selector
                    :container="extension.options.bard.config.container"
                    :folder="extension.options.bard.config.folder || '/'"
                    :restrict-container-navigation="true"
                    :restrict-folder-navigation="extension.options.bard.config.restrict_assets"
                    :selected="selections"
                    :view-mode="'grid'"
                    :max-files="1"
                    @selected="assetsSelected"
                    @closed="closeSelector">
                </selector>
            </stack>
        </div>
    </node-view-wrapper>

</template>

<script>
import { NodeViewWrapper } from '@tiptap/vue-2';
import Selector from '../../assets/Selector.vue';

export default {

    components: {
        NodeViewWrapper,
        Selector,
    },

    inject: ['storeName'],

    props: [
        'editor', // the editor instance
        'node', // access the current node
        'decorations', // an array of decorations
        'selected', // true when there is a NodeSelection at the current node view
        'extension', // access to the node extension, for example to get options
        'getPos', // get the document position of the current node
        'updateAttributes', // update attributes of the current node.
        'deleteNode', // delete the current node
    ],

    data() {
        return {
            assetId: null,
            asset: null,
            showingSelector: false,
            loading: false,
            alt: this.node.attrs.alt,
        }
    },

    computed: {

        src() {
            if (this.asset) {
                return this.asset.url;
            }
        },

        actualSrc() {
            if (this.asset) {
                return `asset::${this.assetId}`;
            }

            return this.src;
        },

        selections() {
            return this.assetId ? [this.assetId] : [];
        }

    },

    created() {
        let src = this.node.attrs.src;

        if (this.node.isNew) {
            this.openSelector();
        }

        if (src && src.startsWith('asset:')) {
            this.assetId = src.substr(7);
        }

        let id = this.assetId || src;
        if (id) this.loadAsset(id);
    },

    watch: {

        actualSrc(src) {
            if ( !this.node.attrs.src) {
                this.updateAttributes({ src, asset: !!this.assetId });
            }
        },

        alt(alt) {
            this.updateAttributes({ alt });
        }

    },

    methods: {

        openSelector() {
            this.showingSelector = true;
        },

        closeSelector() {
            this.showingSelector = false;
        },

        assetsSelected(selections) {
            this.loading = true;
            this.assetId = selections[0];
            this.loadAsset(this.assetId);
        },

        loadAsset(id) {
            let preloaded = _.find(this.$store.state.publish[this.storeName].preloadedAssets, asset => asset.id === id);

            if (preloaded) {
                // TODO
                // Disabling preloading temporarily. It's causing an infinite loop.
                // It wasn't working on 3.2 anyway. It wasn't preloading, the AJAX request was always happening.
                // this.setAsset(preloaded);
                // return;
            }

            this.$axios.get(cp_url('assets-fieldtype'), {
                params: { assets: [id] }
            }).then(response => {
                this.setAsset(response.data[0]);
            });
        },

        setAsset(asset) {
            this.asset = asset;
            this.assetId = asset.id;
            this.alt = asset.alt || this.alt;
            this.loading = false;
            this.updateAttributes({ src: this.actualSrc });
        },

    },

    updated() {
        // This is a workaround to avoid Firefox's inability to select inputs/textareas when the
        // parent element is set to draggable: https://bugzilla.mozilla.org/show_bug.cgi?id=739071
        this.$el.setAttribute('draggable', false);
    }
}
</script>
