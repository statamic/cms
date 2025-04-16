<script setup>
defineProps({
    title: String,
    icon: {
        type: String,
        default: 'collections'
    }
})
</script>

<template>
    <ui-card inset>
        <div class="flex h-full flex-col justify-between">
            <div>
                <header class="flex items-center justify-between px-4 py-2.5 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center gap-3">
                        <ui-icon :name="icon" class="size-5 text-gray-500" />
                        <span v-text="title" />
                    </div>
                    <div class="flex items-center gap-4">
                        <slot name="actions" />
                    </div>
                </header>
                <div class="p-4">
                    <slot />
                </div>
            </div>
            <slot name="footer" />
        </div>
    </ui-card>
</template>
