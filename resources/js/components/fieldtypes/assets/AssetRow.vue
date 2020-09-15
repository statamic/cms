<template>

    <tr class="cursor-grab bg-white hover:bg-grey-10">
        <td class="flex items-center">
            <div v-if="canShowSvg"
                 class="img svg-img mr-1 h-5 w-5 bg-no-repeat bg-center bg-cover"
                 :style="'background-image:url('+asset.url+')'">
            </div>
            <div class="w-8 h-8 mr-1 cursor-pointer whitespace-no-wrap" v-else>
                <img class="asset-thumbnail max-h-full max-w-full rounded w-8 h-8 fit-cover lazyloaded" :src="thumbnail" v-if="isImage" />
                <div class="img" v-else><file-icon type="div" :extension="asset.extension"></file-icon></div>
            </div>
            <button @click="edit" class="flex-1 text-sm truncate" :aria-label="__('Edit Asset')">{{ asset.basename }}</button>
        </td>
        <td class="p-0 pr-1 w-10 text-right align-middle">

            <button class="flex items-center p-1 w-full h-full text-grey-60 hover:text-grey-90" @click="remove" :aria-label="__('Remove Asset')">
                <svg-icon name="trash" />
            </button>

            <asset-editor
                v-if="editing"
                :id="asset.id"
                :allow-deleting="false"
                @closed="closeEditor"
                @saved="assetSaved">
            </asset-editor>
        </td>
    </tr>

</template>

<script>
import Asset from './Asset';
require('lazysizes')

export default {

    mixins: [Asset]

}
</script>
