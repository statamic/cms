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
            return this.asset.extension === 'svg';
        },

        container() {
            return this.asset.id.substr(0, this.asset.id.indexOf('::'))
        },

        canBeTransparent() {
            return ['png', 'svg'].includes(this.asset.extension)
        },

        canDownload() {
            return Statamic.$permissions.has(`view ${this.container} assets`);
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

        open() {
            window.open(this.asset.url, '_blank');
        },

        download() {
            window.open(this.asset.downloadUrl);
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
        },

        actionCompleted(successful, response) {
            if (successful === false) return;
            const id = response.ids[0] || null;
            if (id && id !== this.asset.id) {
                this.$emit('id-changed', id);
            }
            this.closeEditor();
        },

    },


    mounted() {
        this.makeZoomable();
    }

}
