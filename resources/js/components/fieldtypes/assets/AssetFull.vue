<template>
    <div class="relative w-full overflow-hidden rounded-xl bg-white outline-1 -outline-offset-1 outline-black/10 dark:bg-gray-850 dark:outline-white/10">
        <asset-editor
            v-if="editingId"
            :id="editingId"
            :allow-deleting="false"
            :show-navigation="siblings.length > 1"
            :redirect-after-crop="false"
            @previous="navigateToPrevious"
            @next="navigateToNext"
            @closed="closeEditor"
            @saved="assetSaved"
            @created="assetCreated"
            @action-completed="actionCompleted"
        />

        <button
            type="button"
            class="relative flex w-full items-center justify-center overflow-hidden bg-gray-50 focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-blue-500 enabled:cursor-pointer dark:bg-gray-900 aspect-(--preview-aspect-ratio)"
            :class="{ [`bg-checkerboard bg-checkerboard-${checkerboardMode}`]: canBeTransparent && checkerboardMode !== 'transparent' }"
            :style="{ '--preview-aspect-ratio': aspectRatio }"
            :aria-label="asset.invalid ? invalidLabel : label"
            :title="asset.invalid ? invalidLabel : label"
            :disabled="disabled || !isViewable"
            @click="editOrOpen"
        >
            <img
                v-if="preview"
                :src="preview"
                alt=""
                class="absolute inset-0 size-full object-cover"
                :style="previewStyle"
                :draggable="false"
                @load="imageRatio = $event.target.naturalWidth / $event.target.naturalHeight"
                @error="failedPreview = preview"
            />
            <Icon v-else-if="asset.invalid" name="warning-diamond" />
            <FileIcon v-else :extension="asset.extension || 'generic'" class="size-12" />
        </button>

        <div
            v-if="!readOnly"
            class="absolute bottom-3 end-3 flex items-center gap-0.5 rounded-full bg-white/70 p-0.5 shadow-xs ring-1 ring-black/5 dark:bg-gray-850/70 dark:shadow-none dark:ring-white/10"
        >
            <Button
                v-if="showSetAlt && needsAlt && asset.isEditable"
                variant="ghost"
                size="xs"
                round
                :text="__('Set Alt')"
                :disabled="disabled"
                @click="editOrOpen"
            />
            <Dropdown align="end">
                <template #trigger>
                    <Button
                        icon="dots"
                        icon-only
                        variant="ghost"
                        size="xs"
                        round
                        :aria-label="__('Open dropdown menu')"
                        :disabled="disabled"
                    >
                        <span
                            class="absolute top-1/2 left-1/2 size-[max(100%,3rem)] -translate-1/2 pointer-fine:hidden"
                            aria-hidden="true"
                        />
                    </Button>
                </template>
                <DropdownMenu>
                    <DropdownItem
                        v-if="isViewable"
                        :icon="asset.isEditable ? 'pencil' : 'eye'"
                        :text="asset.isEditable ? __('Edit') : __('View')"
                        :disabled="disabled"
                        @select="editOrOpen"
                    />
                    <DropdownItem
                        icon="folder-open"
                        :text="__('Pick new one')"
                        :disabled="disabled || !canReplace"
                        @select="$emit('replace')"
                    />
                    <DropdownItem
                        icon="x"
                        :text="__('Remove')"
                        variant="destructive"
                        :disabled="disabled"
                        @select="remove"
                    />
                </DropdownMenu>
            </Dropdown>
        </div>
    </div>
</template>

<script>
import Asset from './Asset';
import FileIcon from '@/components/FileIcon.vue';
import { Button, Dropdown, DropdownItem, DropdownMenu, Icon } from '@/components/ui';

export default {
    components: {
        Button,
        Dropdown,
        DropdownItem,
        DropdownMenu,
        FileIcon,
        Icon,
    },

    mixins: [Asset],

    emits: ['replace', 'updated', 'removed', 'id-changed'],

    props: {
        aspectRatio: { type: String, default: '8/3' },
        checkerboardMode: { type: String, default: 'transparent' },
        disabled: Boolean,
        canReplace: Boolean,
    },

    data() {
        return {
            failedPreview: null,
            imageRatio: null,
        };
    },

    computed: {
        preview() {
            if (this.asset.invalid) return null;

            const url = this.asset.preview || this.asset.thumbnail;

            return url !== this.failedPreview ? url : null;
        },

        previewStyle() {
            if (!this.imageRatio) return {};

            const [width, height] = this.aspectRatio.split('/').map(Number);
            const frameRatio = width / height;
            const [x = 50, y = 50, zoom = 1] = (this.asset.values?.focus || '50-50-1').split('-').map(Number);
            const visibleWidth = Math.min(1, frameRatio / this.imageRatio);
            const visibleHeight = Math.min(1, this.imageRatio / frameRatio);
            const left = Math.max(0, Math.min(x / 100 - visibleWidth / 2, 1 - visibleWidth));
            const top = Math.max(0, Math.min(y / 100 - visibleHeight / 2, 1 - visibleHeight));
            const positionX = visibleWidth === 1 ? 50 : left / (1 - visibleWidth) * 100;
            const positionY = visibleHeight === 1 ? 50 : top / (1 - visibleHeight) * 100;

            return {
                objectPosition: `${positionX}% ${positionY}%`,
                transform: `scale(${zoom})`,
                transformOrigin: `${(x / 100 - left) / visibleWidth * 100}% ${(y / 100 - top) / visibleHeight * 100}%`,
            };
        },
    },
};
</script>
