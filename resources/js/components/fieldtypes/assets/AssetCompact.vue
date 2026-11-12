<script setup>
import { computed, ref } from 'vue';
import { Button, Dropdown, DropdownItem, DropdownMenu, Icon } from '@/components/ui';
import FileIcon from '@/components/FileIcon.vue';

const props = defineProps({
    asset: { type: Object, required: true },
    disabled: Boolean,
    readOnly: Boolean,
    canReplace: Boolean,
});

defineEmits(['replace', 'removed']);

const failedThumbnail = ref(null);
const thumbnail = computed(() => {
    if (props.asset.invalid) return null;

    const url = props.asset.thumbnail || (props.asset.isSvg ? props.asset.url : null);

    return url !== failedThumbnail.value ? url : null;
});
const label = computed(() => props.asset.invalid ? props.asset.id : props.asset.basename);
</script>

<template>
    <Dropdown>
        <template #trigger>
            <Button
                icon-only
                size="base"
                :aria-label="label"
                :title="label"
                :disabled="disabled"
                :read-only="readOnly"
            >
                <img
                    v-if="thumbnail"
                    :src="thumbnail"
                    alt=""
                    class="size-8 rounded-md object-cover outline-1 -outline-offset-1 outline-black/5 dark:outline-white/10"
                    :draggable="false"
                    @error="failedThumbnail = thumbnail"
                />
                <Icon v-else-if="asset.invalid" name="warning-diamond" />
                <FileIcon v-else :extension="asset.extension || 'generic'" class="size-6" />
                <span
                    class="absolute top-1/2 left-1/2 size-[max(100%,3rem)] -translate-1/2 pointer-fine:hidden"
                    aria-hidden="true"
                />
            </Button>
        </template>

        <DropdownMenu>
            <DropdownItem
                icon="folder-open"
                :text="__('Pick new one')"
                :disabled="disabled || readOnly || !canReplace"
                @select="$emit('replace')"
            />
            <DropdownItem
                icon="x"
                :text="__('Remove')"
                variant="destructive"
                :disabled="disabled || readOnly"
                @select="$emit('removed')"
            />
        </DropdownMenu>
    </Dropdown>
</template>
