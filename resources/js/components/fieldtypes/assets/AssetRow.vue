<template>

    <tr class="cursor-grab bg-white hover:bg-grey-10">
        <td class="flex items-center">
            <div v-if="canShowSvg"
                 class="img svg-img mr-1 h-7 w-7 bg-no-repeat bg-center bg-cover text-center flex items-center justify-center"
                 :style="'background-image:url('+asset.url+')'">
            </div>
            <button class="w-7 h-7 cursor-pointer whitespace-no-wrap flex items-center justify-center" @click="edit" v-else>
                <img class="asset-thumbnail max-h-full max-w-full rounded w-7 h-7 fit-cover lazyloaded" :src="thumbnail" v-if="isImage" />
                <file-icon :extension="asset.extension" v-else />
            </button>
            <button v-if="showFilename" @click="edit" class="flex-1 ml-1 text-sm text-left truncate" :aria-label="__('Edit Asset')" v-tooltip="asset.basename">{{ asset.basename }}</button>
        </td>
        <td class="p-0 w-8 text-right align-middle">

            <button v-if="!readOnly" class="flex items-center p-1 w-full h-full text-grey-60 hover:text-grey-90" @click="remove" :aria-label="__('Remove Asset')">
                <svg-icon name="trash" class="w-6 h-6" />
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
