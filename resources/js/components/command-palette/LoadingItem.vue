<script setup>
import { Icon } from '@/components/ui';
</script>

<template>
    <div
        :class="[
            'flex items-center gap-2 border-0',
            'px-2 py-1.5 text-sm antialiased',
            'not-data-disabled:cursor-pointer data-disabled:opacity-50',
        ]"
    >
        <div class="flex size-6 items-center justify-center p-1 text-gray-500">
            <Icon name="loading" class="size-4 animate-spin" key="loading" />
        </div>
        <div class="flex-1 text-gray-500" v-text="__('Loading')" />
    </div>
</template>
