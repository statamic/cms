<script setup>
import { TabsContent } from 'reka-ui';

defineProps({
    name: { type: String, default: null },
});
</script>

<template>
    <TabsContent :value="name">
        <slot />
    </TabsContent>
</template>
