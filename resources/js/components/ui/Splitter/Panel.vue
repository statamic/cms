<script setup>
import { SplitterPanel } from 'reka-ui';

defineProps({
    collapsible: { type: Boolean, default: false },
    collapsedSize: { type: Number, default: 10 },
    minSize: { type: Number, default: 20 },
});
</script>

<template>
    <SplitterPanel :min-size :collapsible class="">
        <slot />
    </SplitterPanel>
</template>
