<script setup>
import { computed, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { Button, Icon } from '@ui';
import AiSuggestBlocks from './AiSuggestBlocks.vue';
import { icon } from './use-ai-suggest.js';

// Prototype: what a large field's suggestions show (see AiSuggestLarge.vue): a heading, the suggestions, the "Make it…" box with the site's
// saved field prompts, and Try again, as on the other fields (see AiSuggest.vue).
const props = defineProps({
    suggestions: { type: Array, required: true },
    /** Each suggestion's changes, from changesOf() in mock-bard-suggestions.js */
    changes: { type: Array, required: true },
    loading: { type: Boolean, default: false },
    /** The field's name in the heading, and a close button, for when it isn't in a dropdown */
    field: { type: String, default: null },
    /** How tall the list can get before it scrolls */
    listClass: { type: String, default: 'max-h-[55vh]' },
});

const emit = defineEmits(['apply', 'follow', 'retry', 'close', 'preview']);

const instruction = ref('');
const page = usePage();
const fieldPrompts = computed(() => {
    const collection = page.url.match(/\/collections\/([^/?]+)/)?.[1];
    return (page.props._statamic?.aiPrompts ?? []).filter((saved) => saved.use_on === 'fields' && (!saved.collections.length || saved.collections.includes(collection)));
});

function follow(text) {
    if (!text.trim() || props.loading) return;
    instruction.value = '';
    emit('follow', text);
}

// The other fields' suggestion rows (see AiSuggest.vue), with a little more room (16px above and below each), as these are whole paragraphs.
// A gray-300 line between them is covered by a hovered or focused one's background.
const row = 'group relative block w-full cursor-pointer rounded-lg px-2.5 py-4 text-start not-first:mt-[5px] not-first:before:absolute not-first:before:inset-x-2.5 not-first:before:-top-[3px] not-first:before:h-px not-first:before:bg-gray-300 after:absolute after:inset-x-0 after:-inset-y-[3px] first:after:top-0 last:after:bottom-0 after:z-(--z-index-below) after:rounded-lg hover:z-(--z-index-above) hover:before:opacity-0 hover:after:bg-gray-100 dark:not-first:before:bg-white/15 dark:hover:after:bg-white/5 focus-visible:z-[calc(var(--z-index-above)+1)] focus-visible:outline-none focus-visible:before:opacity-0 focus-visible:after:[outline:var(--focus-outline-width)_solid_var(--focus-outline-color,currentColor)] focus-visible:after:outline-offset-(--focus-outline-offset) [&:focus-visible+button]:before:opacity-0';
</script>

<template>
    <div class="flex items-center gap-2 border-b border-gray-200 px-3 py-2.5 dark:border-white/10">
        <Icon :name="icon" class="size-3.5! text-ai dark:text-ai-light" />
        <span class="text-xs font-medium text-gray-700 dark:text-gray-300">{{ field ? __('Suggestions for :field', { field }) : __('Suggestions') }}</span>
        <!-- Pulled into the padding, so the heading is the same height as the other fields' (see AiSuggest.vue) -->
        <Button v-if="field" class="-my-1 -me-1.5 ms-auto" size="xs" variant="ghost" icon="x" :aria-label="__('Close')" @click="emit('close')" />
    </div>

    <div v-if="loading" class="space-y-3 p-3" aria-busy="true">
        <div v-for="i in 3" :key="i" class="space-y-1.5">
            <div class="h-2.5 w-14 animate-pulse rounded bg-gray-200 dark:bg-white/10" />
            <div class="h-3 animate-pulse rounded bg-gray-100 dark:bg-white/5" :class="['w-full', 'w-4/5', 'w-2/3'][i - 1]" />
        </div>
    </div>

    <!-- Scrolls once they're taller than there's room for -->
    <!-- Hovering or focusing one previews it in the field (see AiSuggestLarge.vue) -->
    <div v-else class="overflow-y-auto p-1.5" :class="listClass" @mouseleave="emit('preview', null)">
        <button
            v-for="(suggestion, i) in suggestions"
            :key="i"
            type="button"
            :class="row"
            @click="emit('apply', i)"
            @mouseenter="emit('preview', i)"
            @focus="emit('preview', i)"
            @blur="emit('preview', null)"
        >
            <span class="mb-1 block text-2xs font-medium text-gray-500 group-hover:text-gray-600 dark:text-gray-300">{{ suggestion.label }}</span>
            <!-- All of it, with its headings and paragraphs, and its changes marked -->
            <span class="block">
                <AiSuggestBlocks :changes="changes[i] ?? []" compact />
            </span>
        </button>
    </div>

    <!-- Tell it how to rework them, as on the other fields -->
    <div class="space-y-2 border-t border-gray-200 px-3 py-2.5 dark:border-white/10">
        <form @submit.prevent="follow(instruction)">
            <input v-model="instruction" type="text" :placeholder="__('Make it…')" :aria-label="__('Rework the suggestions')" class="w-full rounded-md border border-gray-200 bg-white px-2 py-1 text-sm dark:border-white/10 dark:bg-gray-900" />
        </form>
        <div v-if="fieldPrompts.length" class="flex flex-wrap gap-1.5">
            <button
                v-for="saved in fieldPrompts"
                :key="saved.command"
                type="button"
                class="cursor-pointer rounded-full border border-gray-200 px-2 py-0.5 text-xs text-gray-600 hover:bg-gray-100 dark:border-white/17 dark:text-gray-300 dark:hover:bg-white/8"
                v-tooltip="{ content: saved.instructions, class: 'max-w-48 text-xs' }"
                @click="follow(saved.name)"
            >
                {{ saved.name }}
            </button>
        </div>
    </div>

    <div class="flex justify-end border-t border-gray-200 px-2 py-1.5 dark:border-white/10">
        <Button size="xs" variant="ghost" icon="refresh" :text="__('Try again')" @click="emit('retry')" />
    </div>
</template>
