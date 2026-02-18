<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import createMiddleEllipsisUtils from '@dynamic-middle-ellipsis/core';
import customFontWidthMap from './TruncatedTextCharacterMap.js';

const props = defineProps({
    text: { type: String, required: true },
});

const truncatedRef = ref(null);
let cleanup = null;

onMounted(() => {
    const truncateOnResize = createMiddleEllipsisUtils({ customFontWidthMap });

    cleanup = truncateOnResize({
        targetElement: truncatedRef.value,
        originalText: truncatedRef.value.innerText,
    });
});

onUnmounted(() => {
    cleanup?.();
});
</script>

<template>
    <div class="relative">
        <div ref="truncatedRef" v-text="text" :title="text" :aria-label="text"></div>
    </div>
</template>
