<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import truncateOnResize from './TruncateText.js';

const props = defineProps({
    text: { type: String, required: true },
});

const truncatedRef = ref(null);
let cleanup = null;

onMounted(() => {
    cleanup = truncateOnResize(truncatedRef.value, props.text);
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
