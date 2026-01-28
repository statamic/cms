<script setup>
import { SplitterPanel } from 'reka-ui';

defineProps({
    /** When `true`, allows the panel to be collapsed */
    collapsible: { type: Boolean, default: false },
    /** Size of the panel when collapsed (percentage) */
    collapsedSize: { type: Number, default: 10 },
    /** Minimum size of the panel (percentage) */
    minSize: { type: Number, default: 20 },
});
</script>

<template>
    <SplitterPanel :min-size :collapsible class="">
        <slot />
    </SplitterPanel>
</template>
