<template>
    <tr class="cursor-grab bg-white hover:bg-gray-50 dark:bg-gray-800 dark:hover:bg-gray-900">
        <td class="flex gap-3 h-full items-center p-3">
            <div
                v-if="canShowSvg"
                class="img svg-img flex size-7 items-center justify-center bg-cover bg-center bg-no-repeat text-center"
                :style="'background-image:url(' + thumbnail + ')'"
            ></div>
            <button
                v-else
                class="flex size-7 cursor-pointer items-center justify-center whitespace-nowrap"
                @click="editOrOpen"
            >
                <img
                    class="asset-thumbnail size-7 text-gray-600 max-h-full max-w-full rounded-sm object-cover"
                    loading="lazy"
                    :src="thumbnail"
                    :alt="asset.basename"
                    v-if="isImage"
                />
                <file-icon :extension="asset.extension" v-else class="size-7" />
            </button>
            <button
                v-if="showFilename"
                @click="editOrOpen"
                class="flex w-full flex-1 items-center truncate text-sm text-gray-600 dark:text-gray-400 text-start"
                :title="__('Edit')"
                :aria-label="__('Edit Asset')"
            >
                {{ asset.basename }}
            </button>
            <ui-badge
                v-if="showSetAlt && needsAlt"
                as="button"
                color="sky"
                variant="outline"
                :text="__('Set Alt')"
                @click="editOrOpen"
            />
            <div v-text="asset.size" class="asset-filesize hidden px-2 text-sm text-gray-500 dark:text-gray-400 @xs:inline" />
        </td>
        <td class="p-3 align-middle text-end" v-if="!readOnly">
            <ui-button
                @click="remove"
                icon="x"
                round
                size="xs"
                variant="ghost"
                :aria-label="__('Remove Asset')"
                :title="__('Remove')"
            />

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
