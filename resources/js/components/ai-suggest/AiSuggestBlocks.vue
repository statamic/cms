<script setup>
import diffWords from './diff-words.js';

// Prototype: a Bard suggestion, block by block, with what it adds in yellow and what it takes out struck through in red, as the other suggestions do.
// compact gives the headings and paragraphs a small look of their own, rather than the page's.
defineProps({
    /** From changesOf() in mock-bard-suggestions.js */
    changes: { type: Array, required: true },
    compact: { type: Boolean, default: false },
});

const added = 'rounded-sm bg-yellow-100 text-gray-900 dark:bg-transparent dark:text-yellow-300';
const removed = 'rounded-sm bg-red-50 text-red-700 line-through dark:bg-transparent dark:text-red-400';
</script>

<template>
    <template v-for="change in changes" :key="change.index">
        <component
            :is="change.type === 'heading' ? `h${change.level ?? 2}` : 'p'"
            :class="compact && (change.type === 'heading' ? 'mt-3 mb-1 text-sm font-semibold text-gray-900 first:mt-0 dark:text-gray-100' : 'mb-2 text-sm/[18px] text-gray-900 last:mb-0 dark:text-gray-200')"
        >
            <!-- Dropped: the whole block struck through -->
            <span v-if="change.after === null" :class="removed">{{ change.before }}</span>
            <template v-else-if="change.changed">
                <template v-for="(part, p) in diffWords(change.before, change.after)" :key="p">
                    <span :class="{ [added]: part.type === 'added', [removed]: part.type === 'removed' }">{{ part.text }}</span>{{ ' ' }}
                </template>
            </template>
            <template v-else>{{ change.before }}</template>
        </component>
    </template>
</template>
