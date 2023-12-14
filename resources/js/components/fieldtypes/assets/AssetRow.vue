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
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24"
                    class="w-6 h-6"
                >
                    <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.082 9.5A4.47 4.47 0 0 0 6.75 8h-1.5a4.5 4.5 0 0 0 0 9h1.5a4.474 4.474 0 0 0 3.332-1.5m3.836-6A4.469 4.469 0 0 1 17.25 8h1.5a4.5 4.5 0 1 1 0 9h-1.5a4.472 4.472 0 0 1-3.332-1.5M6.75 12.499h10.5"></path>
                </svg>
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
