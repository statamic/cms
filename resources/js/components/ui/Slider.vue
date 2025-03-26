<script setup>
import { useId } from 'vue';
import { SliderRange, SliderRoot, SliderThumb, SliderTrack } from 'reka-ui'

defineProps({
    description: { type: String, default: null },
    id: { type: String, default: () => useId() },
    label: { type: String, default: null },
    modelValue: { type: Boolean, default: false },
});

defineEmits(['update:modelValue']);
</script>

<template>
    <ui-with-field :label :description :required variant="inline" :for="id">
        <SliderRoot
            data-ui-control
            class="relative flex items-center select-none touch-none w-full h-5"
            :id
            :max="100"
            :step="1"
            v-model="sliderValue"
            @update:checked="$emit('update:modelValue', $event)"
        >
            <SliderTrack class="bg-gray-200/80 relative grow rounded-full h-2">
                <SliderRange class="absolute bg-slate-900 rounded-full h-full" />
            </SliderTrack>
            <SliderThumb
                class="block w-5 h-5 bg-white rounded-full hover:bg-gray-50 border-2 border-gray-900 shadow-ui-sm focus:outline-none focus:shadow-ui-md"
                :aria-label="label"
            />
        </SliderRoot>
    </ui-with-field>
</template>
