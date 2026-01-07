<script setup lang="ts">
import { Icon, Heading, Button } from '@ui';
import { inject } from 'vue';

defineOptions({ name: 'StackHeader' });

const emit = defineEmits(['closed']);

defineProps({
    /** Title displayed at the top of the stack */
    title: { type: String },
    /** Icon name. [Browse available icons](/?path=/story/components-icon--all-icons) */
    icon: { type: [String, null], default: null },
    /** Whether the close button should be shown */
    showCloseButton: { type: Boolean, default: true },
});

const close = inject('closeStack');
</script>

<template>
    <div
        data-ui-stack-title
        class="flex items-center justify-between rounded-t-xl border-b border-gray-300 px-4 py-2 dark:border-gray-950 dark:bg-gray-800"
    >
        <div class="flex items-center gap-2">
            <Icon :name="icon" v-if="icon" class="size-4" />
            <Heading size="lg" :text="title" />
        </div>
        <slot name="actions" :close="close">
            <Button v-if="showCloseButton" icon="x" variant="ghost" class="-me-2" @click="close" />
        </slot>
    </div>
</template>
