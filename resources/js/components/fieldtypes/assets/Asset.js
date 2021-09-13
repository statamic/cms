import Luminous from 'luminous-lightbox';
import AssetEditor from '../../assets/Editor/Editor.vue';

export default {

    components: {
        AssetEditor
    },

    props: {
        asset: Object,
        readOnly: Boolean,
        showFilename: {
            type: Boolean,
            default: true
        }
    },

    data() {
        return {
            editing: false
        }
    },


    computed: {

        isImage() {
            return this.asset.isImage;
        },

        canShowSvg() {
            return this.asset.extension === 'svg' && ! this.asset.url.includes('::');
        },

        canBeTransparent() {
            return ['png', 'svg'].includes(this.asset.extension)
        },

        thumbnail() {
            return this.asset.thumbnail;
        },

        label() {
            return this.asset.basename;
        }
    },


    methods: {

        edit() {
            if (this.readOnly) return;

            this.editing = true;
        },

        remove() {
            if (this.readOnly) return;

            this.$emit('removed', this.asset);
        },

        makeZoomable() {
            const el = $(this.$el).find('a.zoom')[0];

            if (! el || ! this.isImage) return;

            new Luminous(el, {
                closeOnScroll: true,
                captionAttribute: 'title'
            });
        },

        closeEditor() {
            this.editing = false;
        },

        assetSaved(asset) {
            this.$emit('updated', asset);
            this.closeEditor();
        }

    },


    mounted() {
        this.makeZoomable();
    }

}
