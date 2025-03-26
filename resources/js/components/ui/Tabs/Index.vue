<script setup>
import { TabsRoot } from 'reka-ui'

defineProps({
    defaultTab: { type: String, default: null },
});
</script>

<template>
  <TabsRoot :default-value="defaultTab">
    <slot />
  </TabsRoot>
</template>
