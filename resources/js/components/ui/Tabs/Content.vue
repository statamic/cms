<script setup>
import { TabsContent } from 'reka-ui';

defineProps({
	/** Unique tab name. Should match the name of a `TabTrigger` component. */
    name: { type: String, default: null },
});
</script>

<template>
    <TabsContent :value="name" tabindex="-1">
        <slot />
    </TabsContent>
</template>
