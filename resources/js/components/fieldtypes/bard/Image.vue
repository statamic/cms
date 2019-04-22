<template>

    <div class="inline-block whitespace-normal relative max-w-md"
        @mousedown="parentMousedown"
        @dragstart="parentDragStart"
    >
        <div
            class="inline-block"
            @click="showingToolbar = true"
            ref="dragHandle"
        >
            <div class="bg-white border h-8 w-8 inline-flex items-center justify-center rounded text-grey" v-if="!src">
                <span class="fa fa-picture-o" />
            </div>
            <img v-if="src" :src="src" alt="" class="inline-block" />
        </div>

        <div class="bard-link-toolbar" v-if="showingToolbar">
            <div class="flex items-center px-2">
                <input type="text" v-model="alt" class="input" :placeholder="__('Alt text')" />
                <div class="bard-link-toolbar-buttons">
                    <button v-tooltip="__('Select...')" @click="openSelector">
                        Select...
                    </button>
                </div>
            </div>
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
            showingToolbar: false,
            showingSelector: false,
            loading: false,
            alt: this.node.attrs.alt,
            lastClicked: null,
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
        if (! this.node.attrs.src) {
            this.openSelector();
        }

        let src = this.node.attrs.src;
        if (src.startsWith('asset:')) {
            this.assetId = src.substr(7);
        }

        this.options.bard.$on('image-deselected', () => this.showingToolbar = false);
    },

    watch: {

        assetId(id) {
            this.loadAsset(id);
        },

        actualSrc(src) {
            this.updateAttrs({ src, asset: !!this.assetId });
        },

        alt(alt) {
            console.log(alt);
            this.updateAttrs({ alt });
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
        },

        loadAsset(id) {
            this.$axios.get(cp_url('assets-fieldtype'), {
                params: { assets: [id] }
            }).then(response => {
                this.asset = response.data[0];
                this.alt = this.asset.alt || this.alt;
                this.loading = false;
            });
        },

        parentMousedown(e) {
            this.lastClicked = e.target;
        },

        parentDragStart(e) {
            const handle = this.$refs.dragHandle;

            if (this.lastClicked !== handle && !handle.contains(this.lastClicked)) {
                e.preventDefault();
            }
        }

    }

}
</script>
