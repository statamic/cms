<template>
    <tr class="cursor-grab bg-white hover:bg-gray-100">
        <td class="flex items-center h-full">
            <div
                v-if="canShowSvg"
                class="img svg-img h-7 w-7 bg-no-repeat bg-center bg-cover text-center flex items-center justify-center"
                :style="'background-image:url(' + thumbnail + ')'"
            ></div>
            <button
                class="w-7 h-7 cursor-pointer whitespace-nowrap flex items-center justify-center"
                @click="editOrOpen"
                v-else
            >
                <img
                    class="asset-thumbnail max-h-full max-w-full rounded w-7 h-7 object-cover"
                    loading="lazy"
                    :src="thumbnail"
                    :alt="asset.basename"
                    v-if="isImage"
                />
                <file-icon :extension="asset.extension" v-else class="w-7 h-7" />
            </button>
            <button
                v-if="showFilename"
                @click="editOrOpen"
                class="flex items-center flex-1 ml-3 text-xs text-left truncate w-full"
                :aria-label="__('Edit Asset')"
            >
                {{ asset.basename }}
            </button>
            <div v-text="asset.size" class="hidden @xs:inline asset-filesize text-xs text-gray-600 px-2" />
        </td>
        <td class="w-24" v-if="showSetAlt">
            <button
                class="asset-set-alt text-blue px-4 text-sm hover:text-black"
                @click="editOrOpen"
                v-if="needsAlt"
            >
                {{ asset.values.alt ? "✅" : __("Set Alt") }}
            </button>
        </td>
        <td class="p-0 w-8 text-right align-middle" v-if="!readOnly">
            <button
                class="flex items-center p-1 w-6 h-8 text-gray-600 hover:text-gray-900"
                @click="remove"
                :aria-label="__('Remove Asset')"
            >
                <svg-icon name="micro/trash" class="w-6 h-6" />
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

    methods: {
        editOrOpen() {
            return this.readOnly ? this.open() : this.edit();
        }
    },

};
</script>
