<template>

    <div class="bard-inline-image-container">
        <div v-if="src">
            <div class="p-1 text-center">
                <div ref="content" hidden />
                <img :src="src" class="block mx-auto" data-drag-handle />
            </div>

            <div class="flex items-center p-1 pt-0 rounded-b">
                <text-input name="alt" v-model="alt" prepend="Alt Text" class="mr-1" />
                <button class="btn-flat mr-1" @click="openSelector">
                    {{ __('Replace') }}
                </button>
                <button class="btn-flat" @click="remove">
                    {{ __('Remove') }}
                </button>
            </div>
        </div>

        <div v-else class="text-center p-2">
            <button class="btn-flat" @click="openSelector">
                {{ __('Choose Image') }}
            </button>
            <button class="btn-flat" @click="remove">
                {{ __('Remove') }}
            </button>
        </div>

        <stack
            v-if="showingSelector"
            name="asset-selector"
            @closed="closeSelector"
        >
            <selector
                :container="options.bard.config.container"
                :folder="options.bard.config.folder || '/'"
                :restrict-container-navigation="true"
                :restrict-folder-navigation="options.bard.config.restrict_assets"
                :selected="selections"
                :view-mode="'grid'"
                :max-files="1"
                @selected="assetsSelected"
                @closed="closeSelector">
            </selector>
        </stack>
    </div>

</template>

<script>
import Selector from '../../assets/Selector.vue';

export default {

    components: {
        Selector,
    },

    inject: ['storeName'],

    props: [
        'node', // Prosemirror Node Object
        'view', // Prosemirror EditorView Object
        'getPos', // function allowing the view to find its position
        'updateAttrs', // function to update attributes defined in `schema`
        'editable', // global editor prop whether the content can be edited
        'options', // array of extension options
        `selected`, // whether its selected
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

        if (! src) {
            this.openSelector();
        }

        if (src.startsWith('asset:')) {
            this.assetId = src.substr(7);
        }

        this.loadAsset(this.assetId || src);
    },

    watch: {

        actualSrc(src) {
            if ( !this.node.attrs.src) {
                this.updateAttrs({ src, asset: !!this.assetId });
            }
        },

        alt(alt) {
            this.updateAttrs({ alt });
        }

    },

    methods: {

        openSelector() {
            this.showingSelector = true;
            this.$root.hideOverflow = true;
        },

        remove() {
            let tr = this.view.state.tr;
            let pos = this.getPos();
            tr.delete(pos, pos + this.node.nodeSize);
            this.view.dispatch(tr);
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
                this.setAsset(preloaded);
                return;
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
            this.updateAttrs({ src: this.actualSrc });
        },

    }

}
</script>
