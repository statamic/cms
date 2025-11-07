<script setup>
import { TabsContent } from 'reka-ui';

defineProps({
    name: { type: String, default: null },
    forceMount: { type: Boolean, default: false },
});
</script>

<template>
    <TabsContent :value="name" tabindex="-1" :force-mount>
        <slot />
    </TabsContent>
</template>
