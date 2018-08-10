<template>

    <div class="asset-tile"
         :class="{
             'is-image': isImage && !canShowSvg,
             'is-svg': canShowSvg,
             'is-file': !isImage && !canShowSvg,
             'is-selected': isSelected
         }"
         :title="asset.filename"
         @click="toggle"
         @dblclick="doubleClicked"
         @dragstart="assetDragStart"
    >
        <div class="asset-thumb-container">
            <div v-if="canShowSvg"
                 class="svg-img"
                 :style="svgBackgroundStyle">
            </div>
            <template v-else>
                <div class="asset-thumb" v-if="isImage">
                    <img :src="asset.thumbnail">
                </div>
                <file-icon v-else :extension="asset.extension"></file-icon>
            </template>
        </div>

        <div class="asset-meta" :title="label">{{ label }}</div>

    </div>

</template>


<script>
import Asset from './Asset';

export default {

    mixins: [Asset],


    computed: {

        isImage() {
            return this.asset.is_image;
        },

        icon() {
            return resource_url('img/filetypes/'+ this.asset.extension +'.png');
        },

        label() {
            return this.asset.title || this.asset.basename;
        }

    }

}
</script>
