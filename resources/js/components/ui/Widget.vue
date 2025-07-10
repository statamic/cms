<script setup>
import { Card, Icon } from '@statamic/ui';

defineProps({
    title: String,
    icon: { type: String, default: 'collections' },
});
</script>

<template>
    <Card inset class="@container/widget">
        <div class="flex h-full min-h-54 flex-col justify-between">
            <header class="flex h-15 items-center justify-between border-b border-gray-200 px-4 py-2 dark:border-gray-700">
                <div class="flex items-center gap-3">
                    <Icon :name="icon" class="hidden! size-5 text-gray-500 @xs/widget:block!" />
                    <span v-text="title" />
                </div>
                <div class="flex items-center gap-4">
                    <slot name="actions" />
                </div>
            </header>
            <slot />
            <slot name="footer" />
        </div>
    </Card>
</template>
