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
    console.log(props.text, truncated.value.innerText, truncated.value.innerText);

    cleanup = truncateOnResize({
        targetElement: truncated.value,
        originalText: props.text,
    });
});

onUnmounted(() => {
    cleanup?.();
});
</script>

<template>
    <div ref="truncated" v-text="text" :title="text"></div>
</template>
