<script setup>
import { nextTick, onBeforeUnmount, ref, useTemplateRef, watch } from 'vue';
import { useTooltip } from '@/composables/tooltip.js';

const props = defineProps({
    /** Saved prompts from Settings → AI (a name, its command and instructions), plus any built-in commands like /clear. */
    prompts: { type: Array, required: true },
    /** The one Enter would run, while typing a command. -1 for none. */
    active: { type: Number, default: -1 },
    /** Show the highlighted one's full instructions in a tooltip. */
    explain: { type: Boolean, default: false },
});

defineEmits(['pick']);

// Hovering one shows its full instructions in a tooltip off to the side, as the row cuts them short. So does Tab, for the highlighted one, and then it follows the highlight until Tab puts it away.
// Focus stays in the input, so it's shown from here rather than with v-tooltip.
const hovered = ref(-1);

// Only a pointer that actually moves counts as hovering. When the list opens (say with /) under a pointer that's sitting still, the row under it shouldn't pop up its tooltip until the pointer moves.
function onRowMove(index, event) {
    if (!event.movementX && !event.movementY) return;
    hovered.value = index;
}

const { show, dismissFor, reposition, isVisible } = useTooltip();
const list = useTemplateRef('list');
let shownFor = null;

watch(
    () => [props.active, props.prompts, props.explain, hovered.value],
    async ([, prompts], previous) => {
        await nextTick();
        const index = hovered.value >= 0 ? hovered.value : props.explain ? props.active : -1;
        const prompt = props.prompts[index];
        // Found in the page rather than from a template ref, as ref arrays can be out of order once the list has changed
        const row = prompt?.instructions ? list.value?.querySelectorAll('[role=option]')[index] : null;
        // Tab asks for it, so it shows straight away. Hovering waits a moment, so it doesn't flash up as the pointer passes. (A delay of our own also turns off floating-vue's.)
        // It sits 10px clear of the panel's edge, as usual, rather than of the row, so it doesn't run into the highlight's pill and the panel's border (9px out from the row)
        const options = { content: prompt?.instructions, placement: 'left', distance: 19, class: 'max-w-64 text-xs', delay: hovered.value >= 0 ? 300 : 0 };

        if (!row) {
            if (shownFor) dismissFor(shownFor);
            shownFor = null;
            return;
        }

        if (shownFor && isVisible.value) {
            show(row, options);
            // Typing a command filters the list, which can move the row without it changing
            if (row === shownFor && prompts !== previous?.[1]) reposition();
            shownFor = row;
            return;
        }

        if (shownFor) dismissFor(shownFor);
        shownFor = row;
        show(row, options);
    },
    { immediate: true },
);

onBeforeUnmount(() => shownFor && dismissFor(shownFor));
</script>

<template>
    <!-- Laid out like the keyboard shortcuts: a heading, then rows in one white card with a ring -->
    <div class="space-y-2 p-2">
        <p class="px-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Saved prompts') }}</p>

        <div ref="list" role="listbox" :aria-label="__('Saved prompts')" class="divide-y divide-gray-200 rounded-lg bg-white ring ring-gray-200 dark:divide-gray-700/80 dark:bg-gray-850 dark:ring-gray-700/80">
            <!-- mousedown.prevent keeps focus in the input, so picking one doesn't hide the menu before the click lands -->
            <button
                v-for="(prompt, index) in prompts"
                :key="prompt.command"
                type="button"
                role="option"
                :aria-selected="index === active"
                :class="[
                    // The card doesn't clip its rows, so the highlight's pill can sit outside it, so the first and last rows round their own corners.
                    // The corners are smaller than the usual card's (8px, not 12px), so they don't curve away much beside the pill on the first and last rows.
                    'relative flex w-full cursor-pointer items-start gap-3 py-2 ps-3 pe-3 text-start transition-colors first:rounded-t-lg last:rounded-b-lg focus-visible:focus-outline',
                    index === active ? 'bg-gray-50 dark:bg-gray-800/60' : 'hover:bg-gray-50 dark:hover:bg-gray-800',
                ]"
                @mousedown.prevent
                @mousemove="onRowMove(index, $event)"
                @mouseleave="hovered = -1"
                @click="$emit('pick', prompt)"
            >
                <!-- The highlighted one (the one Enter runs) gets a purple-to-blue pill (the colours of the chat input's focus edge) just outside the card, nearly as tall as the row, against the panel's edge. Being outside, it never moves the text. -->
                <span v-if="index === active" aria-hidden="true" class="absolute inset-y-1 -start-2 w-1 rounded-full bg-linear-to-b from-primary to-[#60a5fa]" />
                <span class="min-w-0 flex-1">
                    <span class="block text-sm/5 text-gray-900 dark:text-gray-200">{{ prompt.name }}</span>
                    <span v-if="prompt.instructions" class="block truncate text-xs/5 text-gray-500 dark:text-gray-400">{{ prompt.instructions }}</span>
                </span>
                <!-- The command in a key, like the keyboard shortcuts, as it's something you type -->
                <span class="inline-flex h-5 shrink-0 items-center rounded-md bg-gray-100 px-1.5 text-[0.6875rem] font-medium text-gray-600 ring-1 ring-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:ring-gray-700">/{{ prompt.command }}</span>
            </button>
        </div>
    </div>
</template>
