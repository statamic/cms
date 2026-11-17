<script setup>
import { computed, ref } from 'vue';
import { Button, Stack } from '@ui';
import AiSuggestBlocks from './AiSuggestBlocks.vue';
import { changesOf, suggestForBard, toBard } from './mock-bard-suggestions.js';
import { icon } from './use-ai-suggest.js';

// Prototype: suggestions for a large field, like Bard, where a whole rewrite is too long to read in the small dropdown the other fields use
// (see AiSuggest.vue). The suggest button opens a stack instead: the field as it is on the left, the suggestion on the right with its changes
// marked, and a tab for each suggestion along the top.
const props = defineProps({
    config: { type: Object, required: true },
    value: { default: null },
    values: { type: Object, default: () => ({}) },
    buttonClass: { type: [String, Array, Object], default: null },
});

const emit = defineEmits(['apply']);

const open = ref(false);
const loading = ref(false);
const suggestions = ref([]);
// The suggestion being looked at
const active = ref(0);
// What was in the field when the suggestions were made
const original = ref([]);
let timer = null;

// Prototype: "think" for a moment, then show the mock suggestions. A label comes from the "Make it…" box, which (for now) only renames them.
function generate(label = null) {
    loading.value = true;
    clearTimeout(timer);
    timer = setTimeout(() => {
        original.value = props.value;
        suggestions.value = suggestForBard(props.value, label);
        active.value = 0;
        loading.value = false;
    }, 900);
}

const changes = computed(() => suggestions.value.map((suggestion) => changesOf(original.value, suggestion)));
// The field as it is, unmarked, for the left-hand side
const current = computed(() => (changes.value[active.value] ?? []).map((change) => ({ ...change, after: change.before, changed: false })));

function openStack() {
    open.value = true;
    generate();
}

function apply() {
    emit('apply', toBard(suggestions.value[active.value].blocks));
    open.value = false;
}

const instruction = ref('');

function follow(text) {
    if (!text.trim() || loading.value) return;
    instruction.value = '';
    generate(text.trim().replace(/^./, (letter) => letter.toUpperCase()));
}

const tabButton = 'w-56 cursor-pointer rounded-lg px-2.5 py-1.5 text-start';
</script>

<template>
    <Button
        size="xs"
        variant="ghost"
        :icon="icon"
        :class="buttonClass"
        class="[&_svg]:size-3.5! [&_svg]:text-gray-400! [&_svg]:opacity-100! dark:[&_svg]:text-gray-500!"
        :aria-label="__('Suggest')"
        v-tooltip="__('Suggest')"
        @click="openStack"
    />

    <Stack v-model:open="open" :title="__('Suggestions for :field', { field: __(config.display) })" :icon="icon" size="full">
        <div class="flex h-full flex-col">
            <!-- A tab for each, with what it does, so they can be told apart without reading them all -->
            <div class="flex gap-1 pb-4">
                <button
                    v-for="(suggestion, i) in suggestions"
                    :key="i"
                    type="button"
                    :class="[tabButton, active === i ? 'bg-gray-100 dark:bg-white/8' : 'hover:bg-gray-50 dark:hover:bg-white/5']"
                    @click="active = i"
                >
                    <span class="block text-sm font-medium text-gray-900 dark:text-gray-100">{{ suggestion.label }}</span>
                    <span class="block text-xs text-gray-500 dark:text-gray-400">{{ suggestion.summary }}</span>
                </button>
            </div>

            <!-- While it "thinks", lines that pulse where the two sides will be -->
            <div v-if="loading" class="grid flex-1 grid-cols-2 gap-6" aria-busy="true">
                <div v-for="i in 2" :key="i" class="space-y-2 rounded-xl border border-gray-200 p-5 dark:border-white/10">
                    <div v-for="j in 5" :key="j" class="h-3 animate-pulse rounded bg-gray-100 dark:bg-white/5" />
                </div>
            </div>

            <!-- The field as it is, then the suggestion with its changes marked -->
            <div v-else class="grid flex-1 grid-cols-2 gap-6">
                <div>
                    <p class="mb-2 text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Now') }}</p>
                    <div class="rounded-xl border border-gray-200 p-5 dark:border-white/10">
                        <AiSuggestBlocks :changes="current" compact />
                    </div>
                </div>
                <div>
                    <p class="mb-2 text-xs font-medium text-gray-500 dark:text-gray-400">{{ suggestions[active]?.label }}</p>
                    <div class="rounded-xl border border-ai/25 p-5 dark:border-ai-light/25">
                        <AiSuggestBlocks :changes="changes[active] ?? []" compact />
                    </div>
                </div>
            </div>
        </div>
        <template #footer-start>
            <form class="w-80" @submit.prevent="follow(instruction)">
                <input v-model="instruction" type="text" :placeholder="__('Make it…')" :aria-label="__('Rework the suggestions')" class="w-full rounded-md border border-gray-200 bg-white px-2 py-1 text-sm dark:border-white/10 dark:bg-gray-900" />
            </form>
        </template>
        <template #footer-end>
            <Button variant="ghost" icon="refresh" :text="__('Try again')" @click="generate()" />
            <Button variant="primary" :text="__('Use this')" :disabled="loading" @click="apply" />
        </template>
    </Stack>
</template>
