import Luminous from 'luminous-lightbox';
import AssetEditor from '../../assets/Editor/Editor.vue';

export default {

    components: {
        AssetEditor
    },

    props: {
        asset: Object
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

        thumbnail() {
            return this.asset.thumbnail;
        },

        toenail() {
            return this.asset.toenail;
        },

        label() {
            return this.asset.basename;
        }
    },


    methods: {

        edit() {
            this.editing = true;
        },

        remove() {
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
