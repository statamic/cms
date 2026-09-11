<script setup>
import { Heading, Icon, Button, ButtonGroup } from '@/components/ui';
import WidthSelector from '@/components/fields/WidthSelector.vue';
import { computed } from 'vue';

const props = defineProps({
    config: { type: Object, required: true },
    meta: { type: Object, default: null },
});

const emit = defineEmits(['configure', 'remove', 'update:width']);

const widthToPercent = { sm: 25, md: 50, lg: 75, full: 100 };
const percentToWidth = { 25: 'sm', 50: 'md', 75: 'lg', 100: 'full' };

// The stylesheet also understands spans and percentages, which a hand written
// config may use. The selector resolves those itself, so pass them straight through.
const currentWidth = computed(() => widthToPercent[props.config.width] ?? props.config.width ?? widthToPercent.md);

function onWidthUpdate(value) {
    emit('update:width', percentToWidth[value]);
}
</script>

<template>
    <div
        data-widget-edit-chrome
        class="dashboard-widget-handle absolute inset-0 z-2 flex flex-col cursor-grab items-center justify-center gap-5 rounded-xl bg-white/80 px-4 backdrop-blur-[4px] -m-px border border-dashed border-purple-300 dark:border-purple-300/40 shadow-sm active:cursor-grabbing dark:bg-gray-900/85"
    >
        <Heading
            :text="meta?.title ?? config.type"
            :icon="meta?.icon ?? 'code-block'"
        />

        <div
            class="flex shrink-0 items-center gap-2"
            @mousedown.stop
            @click.stop
        >
            <WidthSelector
                size="md"
                :model-value="currentWidth"
                :initial-widths="[25, 50, 75, 100]"
                @update:model-value="onWidthUpdate"
            />

            <ButtonGroup>
                <Button
                    size="sm"
                    icon="configure"
                    :text="__('Configure')"
                    @click="emit('configure')"
                />
                <Button
                    size="sm"
                    icon="trash"
                    icon-only
                    :aria-label="__('Remove')"
                    @click="emit('remove')"
                />
            </ButtonGroup>
        </div>
    </div>
</template>
