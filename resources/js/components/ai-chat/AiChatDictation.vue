<script setup>
import { computed } from 'vue';
import { Icon } from '@ui';
import shortcutLabel from '@/util/shortcutLabel.js';

// Prototype: the mic button in the AI chat's input (grey, where the chat icon is), which gets a red recording dot while you speak, and
// (with feedback) the circle that takes send's place meanwhile, the same size as send, with a wave moving with your voice. Pressing
// either stops.
const props = defineProps({
    dictation: { type: Object, required: true },
    /** The voice feedback in send's place, rather than the mic */
    feedback: { type: Boolean, default: false },
});
const emit = defineEmits(['toggle']);

const listening = computed(() => props.dictation.phase === 'listening');
const label = computed(() => (listening.value ? __('Stop') : __('Dictate')));
// The tooltip has the keyboard shortcut too (it works from anywhere, see AiChatButton.vue), written like the other shortcuts in tooltips
const tooltip = computed(() => {
    const shortcut = shortcutLabel('mod+shift+d');
    return listening.value ? __('Stop (:shortcut)', { shortcut }) : __('Dictate (:shortcut)', { shortcut });
});

// The wave: three bars, each moving with your voice on its own, the middle one reaching furthest
const shape = [0.6, 1, 0.6];
const barHeight = (i) => `${2 + props.dictation.bands[i] * shape[i] * 11}px`;

const classes = computed(() => {
    if (props.feedback) return 'grid size-6 place-items-center rounded-full bg-ai/10 text-ai dark:bg-ai/25 dark:text-white';
    // While you speak, the mic stays grey, with a red recording dot. (No `relative` for the dot: AiChat.vue places the button absolutely,
    // which positions the dot just the same.)
    if (listening.value) return 'grid size-6 place-items-center rounded-full text-gray-400 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white';
    return 'grid size-6 place-items-center rounded-full text-gray-400 hover:bg-gray-200/70 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white';
});
</script>

<template>
    <!-- mousedown.prevent keeps focus in the input -->
    <button
        type="button"
        :class="['cursor-pointer transition-[background-color,color] duration-150', classes]"
        :aria-label="label"
        :aria-pressed="listening"
        v-tooltip="tooltip"
        @mousedown.prevent
        @click="emit('toggle')"
    >
        <!-- The wave, moving with your voice -->
        <span v-if="feedback" class="flex h-4 items-center gap-[2px]">
            <span v-for="i in 3" :key="i" class="w-[2px] rounded-full bg-current transition-[height] duration-75" :style="{ height: barHeight(i - 1) }" />
        </span>
        <!-- 16px, the size of the chat icon it takes the place of -->
        <Icon v-else name="computer-voice-mail-microphone" />
        <!-- The recording dot: 6px, at the mic's top right, in a deep red, edged in the box's white so it stands clear of the mic -->
        <span v-if="listening && !feedback" class="absolute top-0.5 end-0.5 size-1.5 rounded-full bg-red-600 ring-2 ring-white dark:ring-gray-900" />
    </button>
</template>
