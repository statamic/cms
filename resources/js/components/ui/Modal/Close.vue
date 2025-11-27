<script setup>
import { inject, useSlots } from 'vue';
import Button from '../Button/Button.vue';

const props = defineProps({
    asChild: {
        type: Boolean,
        default: false,
    },
});

const slots = useSlots();
const closeModal = inject('closeModal', null);

function handleClick(event) {
    if (closeModal) {
        closeModal();
    }
}
</script>

<template>
    <div
        v-if="slots.default"
        data-ui-modal-close
        @click="handleClick"
    >
        <slot />
    </div>
    <Button
        v-else
        data-ui-modal-close
        variant="ghost"
        size="sm"
        icon="x"
        class="absolute top-3 right-2"
        @click="handleClick"
    />
</template>
