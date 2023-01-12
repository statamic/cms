<template>
    <tr class="cursor-grab bg-white hover:bg-grey-10">
        <td class="flex items-center h-full">
            <div
                v-if="canShowSvg"
                class="img svg-img mr-1 h-7 w-7 bg-no-repeat bg-center bg-cover text-center flex items-center justify-center"
                :style="'background-image:url(' + thumbnail + ')'"
            ></div>
            <button
                class="w-7 h-7 cursor-pointer whitespace-no-wrap flex items-center justify-center"
                @click="edit"
                v-else
            >
                <img
                    class="asset-thumbnail max-h-full max-w-full rounded w-7 h-7 fit-cover"
                    loading="lazy"
                    :src="thumbnail"
                    :alt="asset.basename"
                    v-if="isImage"
                />
                <file-icon :extension="asset.extension" v-else class="w-7 h-7" />
            </button>
            <button
                v-if="showFilename"
                @click="edit"
                class="flex items-center flex-1 ml-1 text-xs text-left truncate"
                :aria-label="__('Edit Asset')"
            >
                {{ asset.basename }}
            </button>
            <button
                class="asset-set-alt text-blue px-2 text-sm hover:text-black"
                @click="edit"
                v-if="needsAlt"
            >
                {{ asset.values.alt ? "✅" : __("Set Alt") }}
            </button>
            <div v-text="asset.size" class="asset-filesize text-xs text-grey-50 px-1" />
        </td>
        <td class="p-0 w-8 text-right align-middle">
            <button
                v-if="!readOnly"
                class="flex items-center p-sm w-6 h-8 text-grey-60 hover:text-grey-90"
                @click="remove"
                :aria-label="__('Remove Asset')"
            >
                <svg-icon name="trash" class="w-6 h-6" />
            </button>

            <asset-editor
                v-if="editing"
                :id="asset.id"
                :allow-deleting="false"
                @closed="closeEditor"
                @saved="assetSaved"
                @action-completed="actionCompleted"
            >
            </asset-editor>
        </td>
    </tr>
</template>

<script>
import Asset from "./Asset";
export default {
    mixins: [Asset],

    computed: {
        needsAlt() {
            return (this.asset.isImage || this.asset.isSvg) && !this.asset.values.alt;
        }
    }
};
</script>
