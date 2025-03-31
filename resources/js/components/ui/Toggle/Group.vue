<script setup>
import { ToggleGroupRoot } from 'reka-ui'
import { ref, watch, provide } from 'vue'
import { cva } from 'cva'

const props = defineProps({
    description: { type: String, default: null },
    required: { type: Boolean, default: false },
    label: { type: String, default: null },
    modelValue: { type: [String, Array], default: null },
    size: { type: String, default: 'base' },
    variant: { type: String, default: 'default' },
    multiple: { type: Boolean, default: false },
});

const emit = defineEmits(['update:modelValue']);

const toggleState = ref(props.modelValue ?? (props.multiple ? [] : null));

watch(() => props.modelValue, (newValue) => {
    toggleState.value = newValue ?? (props.multiple ? [] : null);
});

watch(toggleState, (newValue) => {
    emit('update:modelValue', newValue);
});

// Provide variant to child components
provide('toggleVariant', props.variant);
provide('toggleSize', props.size);

const groupClasses = cva({
    base: 'flex group/button',
    variants: {
        notGhost: {
            true: [
                '[&>[data-ui-group-target]:not(:first-child):not(:last-child)]:rounded-none',
                '[&>[data-ui-group-target]:first-child:not(:last-child)]:rounded-e-none',
                '[&>[data-ui-group-target]:last-child:not(:first-child)]:rounded-s-none',
                '[&>*:not(:first-child):not(:last-child):not(:only-child)_[data-ui-group-target]]:rounded-none',
                '[&>*:first-child:not(:last-child)_[data-ui-group-target]]:rounded-e-none',
                '[&>*:last-child:not(:first-child)_[data-ui-group-target]]:rounded-s-none',
            ],
            false: 'gap-1.5'
        }
    }
})({
    notGhost: props.variant !== 'ghost'
});

</script>

<template>
    <ui-with-field :label :description :required>
        <ToggleGroupRoot
            v-model="toggleState"
            :type="multiple ? 'multiple' : 'single'"
            :class="groupClasses"
            data-ui-toggle-group
        >
            <slot />
        </ToggleGroupRoot>
    </ui-with-field>
</template>
