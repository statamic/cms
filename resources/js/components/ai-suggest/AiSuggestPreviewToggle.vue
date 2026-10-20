<script setup>
import useAiSuggest from './use-ai-suggest.js';

const { previewStyleIndex, previewStyles, mounted } = useAiSuggest();
</script>

<template>
    <!-- Prototype: flips through how a suggestion is previewed in the field. Clicking it leaves the suggestions open, so you can compare in place. -->
    <button
        v-if="mounted > 0"
        type="button"
        class="fixed start-4 bottom-4 z-(--z-index-modal) cursor-pointer rounded-md border border-gray-300 bg-white px-2 py-1 font-mono text-xs text-gray-700 shadow-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
        @mousedown.prevent
        @pointerdown.stop
        @click="previewStyleIndex = (previewStyleIndex + 1) % previewStyles.length"
    >
        {{ previewStyleIndex + 1 }}/{{ previewStyles.length }} · {{ previewStyles[previewStyleIndex].label }}
    </button>
</template>
