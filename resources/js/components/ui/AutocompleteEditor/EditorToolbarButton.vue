<template>
    <Button
        class="px-2!"
        :class="{ active }"
        variant="ghost"
        size="sm"
        :aria-label="button.text"
        v-tooltip="button.text"
        @mousedown.prevent
        @click="button.command(editor)"
    >
        <ui-icon v-if="button.svg" :name="button.svg" class="size-3.5!" />
        <span v-else class="flex items-center">{{ button.text }}</span>
    </Button>
</template>

<script setup>
import { Button } from '@/components/ui';

defineProps({
    button: { type: Object, required: true },
    editor: { type: Object, required: true },
    active: { type: Boolean, default: false },
});
</script>
