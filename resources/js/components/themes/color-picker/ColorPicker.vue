<script setup lang="ts">
import { Description, Popover } from '@ui';
import { computed, ref } from 'vue';
import { colorFamilies, colors, shades, specialColors } from '.';
import Swatch from './Swatch.vue';
import { translate as __ } from '@/translations/translator';

const props = defineProps<{
    modelValue?: string;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void;
}>();

function colorSelected(color: string) {
    emit('update:modelValue', color);
}

const hoveredColor = ref<string>(null);
function colorHovered(color: string) {
    hoveredColor.value = color;
}

function isSelected(color: string, shade?: number) {
    if (shade !== undefined) {
        const oklchValue = colors[color]?.[shade];
        return props.modelValue === oklchValue;
    } else {
        return props.modelValue === specialColors[color]?.value;
    }
}

const colorLabel = computed(() => {
    return hoveredColor.value ? hoveredColor.value : selectedColor.value;
});

const selectedColor = computed(() => {

    if (!props.modelValue) return '';

    for (const [key, special] of Object.entries(specialColors)) {
        if (special.value === props.modelValue) {
            return special.name;
        }
    }

    for (const [familyKey, shades] of Object.entries(colors)) {
        for (const [shade, oklchValue] of Object.entries(shades)) {
            if (oklchValue === props.modelValue) {
                const familyName = colorFamilies.find(f => f.key === familyKey)?.name || familyKey;
                return `${familyName} ${shade}`;
            }
        }
    }

    return props.modelValue;
});

const customSwatchBackground = computed(() => {
    return isCustomSelected.value
        ? props.modelValue
        : 'linear-gradient(to bottom right, red, orange, yellow, green, blue, indigo, violet)';
});

const isCustomSelected = computed(() => {
    if (!props.modelValue) return false;
    return selectedColor.value === props.modelValue;
});

function customSelected() {
    const value = prompt(__('Enter a color value'));

    if (value) colorSelected(value);
}
</script>

<template>
    <Popover arrow inset class="w-full!">
        <template #trigger>
            <button
                type="button"
                class="relative size-8 shape-squircle rounded-full border-2 border-gray-300 hover:border-gray-400 transition-all"
                :style="{
                    backgroundColor: modelValue || 'transparent',
                }"
                v-tooltip="selectedColor"
            >
                <div v-if="modelValue === 'transparent'" class="absolute inset-0 flex items-center justify-center">
                    <div class="w-full h-[1px] bg-red-500 rotate-45 origin-center"></div>
                </div>
            </button>
        </template>
        <div class="p-2">
            <div class="mb-2 text-center">
                <Description :text="colorLabel" />
            </div>
            <div class="grid grid-cols-23" @mouseleave="hoveredColor = null">
                <div v-for="family in colorFamilies" :key="family.key" :data-family="family.key" class="flex flex-col">
                    <Swatch
                        v-for="shade in shades"
                        :key="shade"
                        :color="`${family.key}-${shade}`"
                        :color-name="`${family.name} ${shade}`"
                        :is-selected="isSelected(family.key, shade)"
                        @selected="colorSelected"
                        @mouseover="colorHovered"
                    />
                </div>
                <div class="flex flex-col">
                    <Swatch
                        color="transparent"
                        color-name="Transparent"
                        :is-selected="isSelected('transparent')"
                        @selected="colorSelected"
                    >
                        <div class="absolute inset-0 flex items-center justify-center">
                            <div class="w-full h-[1px] bg-red-500 rotate-45 origin-center"></div>
                        </div>
                    </Swatch>
                    <Swatch
                        class="border"
                        color="white"
                        color-name="White"
                        :is-selected="isSelected('white')"
                        @selected="colorSelected"
                        @mouseover="colorHovered"
                    />
                    <Swatch
                        color="volt"
                        :is-selected="isSelected('volt')"
                        @selected="colorSelected"
                        @mouseover="colorHovered"
                    />
                    <Swatch
                        color="transparent"
                        color-name="Custom"
                        :style="{ background: `${customSwatchBackground}` }"
                        :is-selected="isCustomSelected"
                        @selected="customSelected"
                        @mouseover="colorHovered"
                    />
                </div>
            </div>
        </div>
    </Popover>
</template>
