<script setup lang="ts">
import { grayPalettes } from '.';
import { GrayPalette } from './types';
import { Description, Popover } from '@ui';
import { computed, ref } from 'vue';

const props = defineProps<{
    modelValue?: GrayPalette | null;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: GrayPalette): void;
}>();

const palettes: Array<{ key: GrayPalette; name: string }> = [
    { key: 'slate', name: 'Slate' },
    { key: 'gray', name: 'Gray' },
    { key: 'zinc', name: 'Zinc' },
    { key: 'neutral', name: 'Neutral' },
    { key: 'stone', name: 'Stone' },
];

function selectPalette(palette: GrayPalette) {
    emit('update:modelValue', palette);
}

function getPaletteName(palette: GrayPalette) {
    return palettes.find(p => p.key === palette)?.name || palette;
}

const selectedColorName = computed(() => {
    if (hoveredColor.value) {
        return getPaletteName(hoveredColor.value);
    }

    return getPaletteName(props.modelValue ?? 'zinc');
});

const selectedColorValue = computed(() => {
    const value = (props.modelValue ?? 'zinc');
    return grayPalettes[value][100];
});

const hoveredColor = ref<GrayPalette>(null);
function colorHovered(color: GrayPalette) {
    hoveredColor.value = color;
}
</script>

<template>
    <div class="flex gap-1">
        <Popover arrow inset class="w-full!">
            <template #trigger>
                <button
                    type="button"
                    class="relative size-8 shape-squircle rounded-full border-2 border-gray-300 hover:border-gray-400 transition-all"
                    :style="{ backgroundColor: selectedColorValue }"
                    v-tooltip="selectedColorName"
                />
            </template>
            <div class="p-2">
                <div class="mb-2 text-center">
                    <Description :text="selectedColorName" />
                </div>
                <div class="flex gap-2">
                    <button
                        v-for="palette in palettes"
                        :key="palette.key"
                        type="button"
                        class="relative size-8 shape-squircle rounded-full border-2 transition-all"
                        :class="modelValue === palette.key ? 'border-blue-500 ' : 'border-gray-300 hover:border-gray-400'"
                        :style="{ backgroundColor: grayPalettes[palette.key][100] }"
                        :title="palette.name"
                        @click="selectPalette(palette.key)"
                        @mouseover="colorHovered(palette.key)"
                    />
                </div>
            </div>
        </Popover>
    </div>
</template>
