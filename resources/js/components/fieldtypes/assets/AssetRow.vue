<template>
    <tr class="cursor-grab bg-white hover:bg-gray-100 dark:bg-dark-750 dark:hover:bg-dark-700">
        <td class="flex h-full items-center">
            <div
                v-if="canShowSvg"
                class="img svg-img flex h-7 w-7 items-center justify-center bg-cover bg-center bg-no-repeat text-center"
                :style="'background-image:url(' + thumbnail + ')'"
            ></div>
            <button
                class="flex h-7 w-7 cursor-pointer items-center justify-center whitespace-nowrap"
                @click="editOrOpen"
                v-else
            >
                <img
                    class="asset-thumbnail h-7 max-h-full w-7 max-w-full rounded object-cover"
                    loading="lazy"
                    :src="thumbnail"
                    :alt="asset.basename"
                    v-if="isImage"
                />
                <file-icon :extension="asset.extension" v-else class="h-7 w-7" />
            </button>
            <button
                v-if="showFilename"
                @click="editOrOpen"
                class="flex w-full flex-1 items-center truncate text-xs ltr:ml-3 ltr:text-left rtl:mr-3 rtl:text-right"
                :title="__('Edit')"
                :aria-label="__('Edit Asset')"
            >
                {{ asset.basename }}
            </button>
            <button
                v-if="showSetAlt && needsAlt"
                class="asset-set-alt px-4 text-sm text-blue hover:text-black dark:text-dark-blue-100 dark:hover:text-dark-100"
                type="button"
                @click="editOrOpen"
            >
                {{ asset.values.alt ? '✅' : __('Set Alt') }}
            </button>
            <div v-text="asset.size" class="asset-filesize hidden px-2 text-xs text-gray-600 @xs:inline" />
        </td>
        <td class="w-8 p-0 align-middle ltr:text-right rtl:text-left" v-if="!readOnly">
            <button
                class="flex h-8 w-6 items-center p-1 text-lg text-gray-600 antialiased hover:text-gray-900 dark:text-dark-150 dark:hover:text-dark-100"
                @click="remove"
                :title="__('Remove')"
                :aria-label="__('Remove Asset')"
            >
                ×
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
import Asset from './Asset';
export default {
    mixins: [Asset],

    methods: {
        editOrOpen() {
            return this.readOnly ? this.open() : this.edit();
        },
    },
};
</script>
