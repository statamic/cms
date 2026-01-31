<template>
    <!-- Safari doesn't support `position: relative` on `<tr>` elements, but these two properties can be used as an alternative. Source: https://mtsknn.fi/blog/relative-tr-in-safari/ transform: translate(0); clip-path: inset(0); -->
    <tr class="group relative cursor-grab bg-white hover:bg-gray-50 dark:bg-gray-900 dark:hover:bg-gray-900 border-b dark:border-dark-500 last:border-b-0" style="transform: translate(0); clip-path: inset(0);">
        <td class="flex gap-2 sm:gap-3 h-full items-center p-3">
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
                    v-if="thumbnail"
                />
                <file-icon :extension="asset.extension" v-else class="size-7" />
            </button>
            <button
                v-if="showFilename"
                @click="editOrOpen"
                class="flex flex-col w-full flex-1 justify-center gap-1 truncate text-sm text-gray-600 dark:text-gray-400 text-start"
                :title="__('Edit')"
                :aria-label="__('Edit Asset')"
            >
                <div>{{ asset.basename }}</div>
                <template v-if="errors.length">
                    <small class="text-xs text-red-500" v-for="(error, i) in errors" :key="i" v-text="error" />
                </template>
            </button>
        </td>
        <td class="absolute top-0 right-0 flex items-center bg-gradient-to-r to-20% from-transparent to-white dark:to-gray-900 p-3 ps-[2rem] align-middle text-end group-hover:to-gray-50 dark:group-hover:to-gray-900">
            <ui-badge
                v-if="showSetAlt && needsAlt"
                as="button"
                color="sky"
                :text="__('Set Alt')"
                @click="editOrOpen"
            />
            <div v-text="asset.size" class="asset-filesize hidden px-2 text-sm text-gray-600 dark:text-gray-400 @xs:inline" />
            <div v-if="!readOnly">
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
            </div>
        </td>
    </tr>
</template>

<script>
import Asset from './Asset';
export default {
    mixins: [Asset],
};
</script>
