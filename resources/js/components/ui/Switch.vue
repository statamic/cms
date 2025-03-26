<script setup>
import { useId } from 'vue';
import { SwitchRoot, SwitchThumb } from 'reka-ui';

defineProps({
    description: { type: String, default: null },
    required: { type: Boolean, default: false },
    id: { type: String, default: () => useId() },
    label: { type: String, default: null },
    modelValue: { type: Boolean, default: false },
});

defineEmits(['update:modelValue']);
</script>

<template>
    <ui-with-field :label :description :required variant="inline" :for="id">
        <SwitchRoot
            :checked="modelValue"
            :id
            @update:checked="$emit('update:modelValue', $event)"
            class="
                relative flex h-[20px] w-[32px] rounded-full border
                transition-[background] focus-within:outline-none
                data-[state=checked]:border-gray-700
                data-[state=checked]:bg-gray-800
                data-[state=unchecked]:border-transparent
                data-[state=unchecked]:bg-gray-400
            "
            data-ui-control
            dir="ltr"
        >
            <SwitchThumb
                class="
                    my-auto flex size-3.5 translate-x-0.5 items-center justify-center rounded-full bg-white text-xs shadow-xl transition-transform will-change-transform
                    data-[state=checked]:translate-x-full
                "
            />
        </SwitchRoot>
    </ui-with-field>
</template>
