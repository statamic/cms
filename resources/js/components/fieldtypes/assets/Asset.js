import AssetEditor from '../../assets/Editor/Editor.vue';

export default {
    components: {
        AssetEditor,
    },

    props: {
        asset: Object,
        readOnly: Boolean,
        showFilename: {
            type: Boolean,
            default: true,
        },
        showSetAlt: {
            type: Boolean,
            default: true,
        },
        siblings: {
            type: Array,
            default: () => [],
        },
    },

    data() {
        return {
            editing: false,
            editingId: null,
        };
    },

    computed: {
        isViewable() {
            return this.asset.isViewable;
        },

        isImage() {
            return this.asset.isImage;
        },

        canShowSvg() {
            return this.asset.extension === 'svg';
        },

        container() {
            return this.asset.id.substr(0, this.asset.id.indexOf('::'));
        },

        canBeTransparent() {
            return ['png', 'svg', 'webp', 'avif'].includes(this.asset.extension);
        },

        thumbnail() {
            return this.asset.thumbnail;
        },

        label() {
            return this.asset.invalid ? this.asset.id : this.asset.basename;
        },

        needsAlt() {
            if (this.asset.invalid) return false;

            return (this.asset.isImage || this.asset.isSvg) && !this.asset.values.alt;
        },

        invalidLabel() {
            return __('messages.relationship_item_unavailable');
        },
    },

    methods: {
        editOrOpen() {
            if (!this.isViewable) return;

            return this.readOnly ? this.open() : this.edit();
        },

        edit() {
            if (this.readOnly) return;
            if (this.asset?.invalid) return;

            this.editing = true;
            this.editingId = this.asset?.id ?? null;
        },

        remove() {
            if (this.readOnly) return;

            this.$emit('removed', this.asset);
        },

        open() {
            if (!this.asset.url) {
                return this.download();
            }

            window.open(this.asset.url, '_blank');
        },

        download() {
            window.open(this.asset.downloadUrl);
        },

        closeEditor() {
            this.editing = false;
            this.editingId = null;
        },

        assetSaved(asset) {
            this.$emit('updated', asset);
            this.closeEditor();
        },

        actionCompleted(successful, response) {
            if (successful === false) return;
            const id = response.ids[0] || null;
            if (id && id !== this.editingId) {
                this.$emit('id-changed', this.editingId, id);
            }
            this.closeEditor();
        },

        navigateToPrevious() {
            const index = this.siblings.findIndex((asset) => asset.id === this.editingId);
            if (index <= 0) return;

            const previousId = this.siblings[index - 1].id;
            this.editingId = null;
            this.$nextTick(() => (this.editingId = previousId));
        },

        navigateToNext() {
            const index = this.siblings.findIndex((asset) => asset.id === this.editingId);
            if (index === -1 || index >= this.siblings.length - 1) return;

            const nextId = this.siblings[index + 1].id;
            this.editingId = null;
            this.$nextTick(() => (this.editingId = nextId));
        },
    },
};
