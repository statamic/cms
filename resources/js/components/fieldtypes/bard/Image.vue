<template>

    <node-view-wrapper>
        <div class="bard-inline-image-container">
            <div v-if="src">
                <div class="p-1 text-center">
                    <div ref="content" hidden />
                    <img :src="src" class="block mx-auto" data-drag-handle />
                </div>

                <div class="flex items-center p-1 pt-0 rounded-b" @paste.stop>
                    <text-input name="alt" v-model="alt" prepend="Alt Text" class="mr-1" />
                    <button class="btn-flat mr-1" @click="openSelector">
                        {{ __('Replace') }}
                    </button>
                    <button class="btn-flat" @click="deleteNode">
                        {{ __('Remove') }}
                    </button>
                </div>
            </div>

            <div v-else class="text-center p-2">
                <button class="btn-flat" @click="openSelector">
                    {{ __('Choose Image') }}
                </button>
                <button class="btn-flat" @click="deleteNode">
                    {{ __('Remove') }}
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
            this.$root.hideOverflow = true;
        },

        closeSelector() {
            this.showingSelector = false;
            this.$root.hideOverflow = false;
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
