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
        },
        showSetAlt: {
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
            return ['png', 'svg', 'webp', 'avif'].includes(this.asset.extension)
        },

        canDownload() {
            return Statamic.$permissions.has(`view ${this.container} assets`);
        },

        thumbnail() {
            return this.asset.thumbnail;
        },

        label() {
            return this.asset.basename;
        },

        needsAlt() {
            return (this.asset.isImage || this.asset.isSvg) && !this.asset.values.alt;
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
            if (! this.asset.url) {
                return this.download();
            }

            window.open(this.asset.url, '_blank');
        },

        download() {
            window.open(this.asset.downloadUrl);
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

    }

}
