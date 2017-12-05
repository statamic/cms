export default {

    props: ['asset', 'selectedAssets'],


    computed: {

        /**
         * Determine if an asset should be in the selected state.
         */
        isSelected() {
            return _.contains(this.selectedAssets, this.asset.id);
        },

        /**
         * Whether the asset can be rendered as svg
         */
        canShowSvg() {
            return this.asset.extension === 'svg' && !this.asset.url.includes(':');
        },

        /**
         * The inline style used to display an SVG background image
         */
        svgBackgroundStyle() {
            return 'background-image: url("' + this.asset.url + '")';
        }
    },


    methods: {

        /**
         * Trigger a toggle of the selected state.
         */
        toggle() {
            if (this.isSelected) {
                this.deselect();
            } else {
                this.select();
            }
        },

        select() {
            this.$emit('selected', this.asset.id);
        },

        deselect() {
            this.$emit('deselected', this.asset.id);
        },

        /**
         * Trigger editing of an asset.
         */
        editAsset() {
            this.$emit('editing', this.asset.id);
        },

        /**
         * Trigger deleting of an asset.
         */
        deleteAsset() {
            this.$emit('deleting', this.asset.id)
        },

        assetDragStart(e) {
            e.dataTransfer.setData('asset', this.asset.id);
            e.dataTransfer.effectAllowed = 'move';
            this.$emit('assetdragstart', this.asset.id);
        },

        doubleClicked() {
            // When in the context of the asset manager, we want to edit the asset. Otherwise, we want to
            // select the asset and close the dialog, which will be handled in the parent components.
            if (document.location.pathname.split('/')[2] === 'assets') {
                this.editAsset();
            } else {
                this.select();
                this.$emit('doubleclicked', this.asset.id);
            }
        }

    }

}
