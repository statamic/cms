<template>

    <div class="asset-tile"
         :class="{ 'is-image': isImage && !canShowSvg, 'is-svg': canShowSvg, 'is-file': !isImage && !canShowSvg }"
         :title="asset.filename"
    >

        <asset-editor
            v-if="editing"
            :id="asset.id"
            :allow-deleting="false"
            @closed="closeEditor"
            @saved="assetSaved">
        </asset-editor>

        <div class="asset-thumb-container">

            <div class="asset-thumb">

                <!-- Solo Bard -->
                <template v-if="isImage && isInBardField && !isInAssetBrowser">
                    <img :src="asset.url" >
                </template>

                <template v-else>
                    <a :href="toenail" class="zoom" v-if="isImage" :title="label">
                        <img :src="thumbnail" />
                    </a>

                    <template v-else>
                        <div v-if="canShowSvg"
                             class="svg-img"
                             :style="'background-image:url('+asset.url+')'">
                        </div>
                        <file-icon v-else type="div" :extension="asset.extension"></file-icon>
                    </template>

                </template>

                <div class="asset-controls">
                    <button
                        @click="edit"
                        class="btn btn-icon icon icon-pencil"
                        :alt="translate('cp.edit')"></button>

                    <button
                        @click="remove"
                        class="btn btn-icon icon icon-trash"
                        :alt="translate('cp.remove')"></button>
                </div>
            </div>
        </div>

        <div class="asset-meta">
            <div class="asset-filename" :title="label">{{ label }}</div>
            <div class="asset-filesize" v-if="! isInBardField">{{ asset.size }}</div>
        </div>
    </div>

</template>


<script>
import Asset from './Asset';

export default {

    mixins: [Asset],

    computed: {

        isInAssetBrowser() {
            let vm = this;

            while (true) {
                let parent = vm.$parent;

                if (! parent) return false;

                if (parent.constructor.name === 'AssetBrowser') {
                    return true;
                }

                vm = parent;
            }
        },

        isInBardField() {
            return this.$parent.isInBardField;
        }
    }
}
</script>
