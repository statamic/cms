<script setup>
import { TabTrigger } from '@/components/ui';

defineProps({
    text: { type: String, default: null },
    name: { type: String, default: null },
});
</script>

<template>
    <TabTrigger :name="name">
        <slot>{{ text }}</slot>
    </TabTrigger>
</template>
