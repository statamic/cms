<script setup lang="ts">
import { Icon, Heading, Button } from '@ui';
import { inject, useSlots } from 'vue';

defineOptions({ name: 'StackHeader' });

withDefaults(defineProps<{
    /** Title displayed at the top of the stack */
    title?: string;
    /** Icon name. [Browse available icons](/?path=/story/components-icon--all-icons) */
    icon?: string | null;
    /** Whether the close button should be shown */
    showCloseButton?: boolean;
}>(), {
    showCloseButton: true,
});

const close = inject<() => void>('closeStack');

const hasActionsSlot = !!useSlots().actions;
</script>

<template>
    <div
        data-ui-stack-title
        class="flex items-center justify-between rounded-t-xl border-b border-gray-300 ps-6 pe-4 py-2 dark:border-gray-950 dark:bg-gray-800"
    >
        <div class="flex items-center gap-2">
            <Icon :name="icon" v-if="icon" class="size-4" />
            <Heading size="lg" :text="title" />
        </div>
        <div v-if="hasActionsSlot" class="flex items-center gap-2">
            <slot name="actions" :close="close" />
            <Button v-if="showCloseButton" icon="x" variant="ghost" class="-me-2" @click="close" />
        </div>
        <Button v-if="!hasActionsSlot && showCloseButton" icon="x" variant="ghost" class="-me-2" @click="close" />
    </div>
</template>
