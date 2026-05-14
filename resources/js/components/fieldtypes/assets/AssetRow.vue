<template>
    <!-- Safari doesn't support `position: relative` on `<tr>` elements, but these two properties can be used as an alternative. Source: https://mtsknn.fi/blog/relative-tr-in-safari/ transform: translate(0); clip-path: inset(0); -->
    <tr class="group relative bg-white hover:bg-gray-50 dark:bg-gray-900 dark:hover:bg-gray-900 border-b dark:border-gray-600 last:border-b-0" :class="{ 'cursor-grab': !readOnly }" style="transform: translate(0); clip-path: inset(0);">
        <td class="flex h-full min-w-0 items-center gap-2 p-3 sm:gap-3">
            <div
                v-if="canShowSvg"
                class="img svg-img flex size-7 items-center justify-center bg-cover bg-center bg-no-repeat text-center"
                :style="'background-image:url(' + thumbnail + ')'"
            ></div>
            <button
                v-else
                class="flex size-7 shrink-0 cursor-pointer items-center justify-center whitespace-nowrap"
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
                class="min-w-0 flex-1 truncate text-start text-sm leading-5 text-gray-600 dark:text-gray-400"
                :title="__('Edit')"
                :aria-label="__('Edit Asset')"
            >
                {{ asset.basename }}
            </button>
            <div v-if="readOnly" v-text="asset.size" class="asset-filesize hidden shrink-0 px-2 text-sm leading-5 text-gray-600 dark:text-gray-400 @xs:block" />
        </td>
        <td v-if="!readOnly" class="absolute top-0 right-0 flex items-center bg-linear-to-r to-20% from-transparent to-white p-3 ps-8 align-middle text-end group-hover:to-gray-50 dark:to-gray-900 dark:group-hover:to-gray-900">
            <ui-badge
                v-if="showSetAlt && needsAlt"
                as="button"
                color="sky"
                :text="__('Set Alt')"
                @click="editOrOpen"
            />
            <div v-text="asset.size" class="asset-filesize hidden px-2 text-sm text-gray-600 dark:text-gray-400 @xs:inline" />
            <div>
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
