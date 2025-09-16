<script setup>
import { SplitterGroup } from 'reka-ui';

defineProps({
    direction: { type: String, default: 'horizontal' },
});
</script>

<template>
    <SplitterGroup :direction>
        <slot />
    </SplitterGroup>
</template>
