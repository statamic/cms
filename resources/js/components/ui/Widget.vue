<script setup>
import { Card, Icon } from '@ui';

defineProps({
    title: String,
    icon: { type: String, default: 'collections' },
});
</script>

<template>
    <Card inset class="@container/widget min-h-54 entry-animate-in" v-cloak>
        <div class="flex h-full min-h-54 flex-col entry-animate-in">
            <header class="flex items-center min-h-[49px] justify-between border-b border-gray-200 px-4.5 py-2 dark:border-gray-700">
                <div class="flex items-center gap-3">
                    <Icon :name="icon" class="hidden! size-5 text-gray-500 @xs/widget:block!" />
                    <span v-text="title" />
                </div>
                <div class="flex items-center gap-4 -mr-2.5">
                    <slot name="actions" />
                </div>
            </header>
            <slot />
            <slot name="footer" />
        </div>
    </Card>
</template>
