<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue';
import truncateOnResize from './TruncateText.js';

const props = defineProps({
    /** The text to display. Will be truncated from the middle when it overflows its container. */
    text: { type: String, required: true },
});

const truncatedRef = ref(null);
let cleanup = null;

const bindTruncation = () => {
    if (!truncatedRef.value) {
        return;
    }

    cleanup?.();
    cleanup = truncateOnResize(truncatedRef.value, props.text);
};

onMounted(bindTruncation);

watch(() => props.text, bindTruncation);

onUnmounted(() => {
    cleanup?.();
});
</script>

<template>
    <div class="relative">
        <div ref="truncatedRef" v-text="text" :title="text" :aria-label="text"></div>
    </div>
</template>
