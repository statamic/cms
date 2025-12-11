<script setup lang="ts">
import { computed, useAttrs } from 'vue';
import { twMerge } from 'tailwind-merge';
import { colors, specialColors } from '.';

defineOptions({
    inheritAttrs: false
});

const emit = defineEmits<{
    (e: 'selected', value: string): void;
    (e: 'mouseover', value: string): void;
}>();

const props = defineProps<{
    color: string;
    colorName?: string;
    isSelected: Boolean;
}>();

const attrs = useAttrs();

const classes = computed(() => {
    return twMerge([
        `bg-${props.color}`,
        'size-4 cursor-pointer hover:scale-150 relative hover:rounded hover:z-10 hover:shadow-sm',
        props.isSelected ? 'ring-2 ring-blue-500 z-1' : '',
        attrs.class as string,
    ]);
});

const colorName = computed(() => {
   return props.colorName || props.color;
});

function select() {
    const { color, shade } = (() => {
        const parts = props.color.split('-');
        return parts.length === 2
            ? { color: parts[0], shade: parseInt(parts[1], 10) }
            : { color: props.color, shade: undefined };
    })();

    const value = shade === undefined
        ? specialColors[color]?.value
        : colors[color]?.[shade];

    emit('selected', value);
}
</script>

<template>
    <button
        type="button"
        :class="classes"
        :title="colorName"
        @click="select"
        @mouseover="emit('mouseover', colorName)"
    >
        <slot />
    </button>
</template>
