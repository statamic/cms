<script setup>
import { useId } from 'vue';
import { SliderRange, SliderRoot, SliderThumb, SliderTrack } from 'reka-ui';

defineProps({
    description: { type: String, default: null },
    id: { type: String, default: () => useId() },
    label: { type: String, default: null },
    modelValue: { type: Boolean, default: false },
});

defineEmits(['update:modelValue']);
</script>

<template>
    <SliderRoot
        data-ui-control
        class="relative flex h-5 w-full touch-none items-center select-none"
        :id
        :max="100"
        :step="1"
        v-model="sliderValue"
        @update:checked="$emit('update:modelValue', $event)"
    >
        <SliderTrack class="relative h-2 grow rounded-full bg-gray-200/80">
            <SliderRange class="absolute h-full rounded-full bg-slate-900" />
        </SliderTrack>
        <SliderThumb
            class="shadow-ui-sm focus:shadow-ui-md block h-5 w-5 rounded-full border-2 border-gray-900 bg-white hover:bg-gray-50 focus:outline-hidden"
            :aria-label="label"
        />
    </SliderRoot>
</template>
