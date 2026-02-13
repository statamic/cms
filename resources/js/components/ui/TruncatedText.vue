<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import createMiddleEllipsisUtils from '@dynamic-middle-ellipsis/core';

const props = defineProps({
    text: { type: String, required: true },
});

const truncated = ref(null);
let cleanup = null;

onMounted(() => {
    const truncateOnResize = createMiddleEllipsisUtils();

    cleanup = truncateOnResize({
        targetElement: truncated.value,
        originalText: truncated.value.innerText,
    });
});

onUnmounted(() => {
    cleanup?.();
});
</script>

<template>
    <div class="relative">
        <div ref="truncated" v-text="text" v-tooltip="text" :aria-label="text"></div>
    </div>
</template>
