<script setup>
import { nextTick, onBeforeUnmount, ref, watch } from 'vue';
import { Button, Icon, Popover } from '@ui';
import { suggestFor } from './mock-suggestions.js';
import { icon } from './use-ai-suggest.js';

const props = defineProps({
    // The field's config, its current value, and the values of the fields around it (for context, like the entry's title)
    config: { type: Object, required: true },
    value: { default: null },
    values: { type: Object, default: () => ({}) },
    // Where the button sits, as classes for the button itself
    buttonClass: { type: [String, Array, Object], default: null },
});

const emit = defineEmits(['apply']);

const open = ref(false);
const loading = ref(false);
const result = ref(null);
let timer = null;

// Prototype: "think" for a moment, then show the mock suggestions
function generate() {
    loading.value = true;
    clearTimeout(timer);
    timer = setTimeout(() => {
        result.value = suggestFor(props.config, props.value, props.values);
        loading.value = false;
    }, 900);
}

function toggled(isOpen) {
    open.value = isOpen;
    if (isOpen) generate();
}

// The suggestions are taller than the loading lines, so the dropdown's body eases to its new height rather than snapping.
// It's measured as it changes. The first measurement after opening sets the height without easing, so it doesn't grow from nothing.
const body = ref(null);
const bodyHeight = ref(null);
const easeHeight = ref(false);
let observer = null;

watch(body, (el) => {
    observer?.disconnect();
    easeHeight.value = false;
    bodyHeight.value = null;
    if (!el) return;

    observer = new ResizeObserver(([entry]) => {
        bodyHeight.value = `${entry.borderBoxSize[0].blockSize}px`;
        if (!easeHeight.value) nextTick(() => requestAnimationFrame(() => (easeHeight.value = true)));
    });
    observer.observe(el);
});

onBeforeUnmount(() => observer?.disconnect());

function apply(text) {
    emit('apply', text);
    open.value = false;
}
</script>

<template>
    <!-- Opens below the button, with its right edge lined up with the field's (the button sits 6px in from it) -->
    <Popover :open="open" @update:open="toggled" align="end" :align-offset="-6" :offset="16" inset class="w-80!">
        <template #trigger>
            <Button
                size="xs"
                variant="ghost"
                :icon="icon"
                :class="buttonClass"
                class="[&_svg]:size-3.5! [&_svg]:text-gray-400! [&_svg]:opacity-100! dark:[&_svg]:text-gray-500!"
                :aria-label="__('Suggest')"
                v-tooltip="__('Suggest')"
            />
        </template>

        <div class="flex items-center gap-2 border-b border-gray-200 px-3 py-2.5 dark:border-white/10">
            <Icon :name="icon" class="size-3.5 text-primary" />
            <span class="text-xs font-medium text-gray-700 dark:text-gray-300">
                {{ __('Suggestions') }}
            </span>
        </div>

        <!-- Eases to the height of what's inside, so it doesn't snap when the suggestions arrive -->
        <div :style="{ height: bodyHeight }" :class="['overflow-hidden', easeHeight ? 'transition-[height] duration-150 ease-out motion-reduce:transition-none' : '']">
            <div ref="body">
                <!-- While it "thinks", three lines that pulse where the suggestions will be -->
                <div v-if="loading" class="space-y-3 p-3" aria-busy="true">
                    <div v-for="i in 3" :key="i" class="space-y-1.5">
                        <div class="h-2.5 w-14 animate-pulse rounded bg-gray-200 dark:bg-white/10" />
                        <div class="h-3 animate-pulse rounded bg-gray-100 dark:bg-white/5" :class="['w-full', 'w-4/5', 'w-2/3'][i - 1]" />
                    </div>
                </div>

                <!-- Picking one puts it in the field, replacing what's there -->
                <div v-else-if="result" class="space-y-0.5 p-1.5">
                    <button
                        v-for="suggestion in result.suggestions"
                        :key="suggestion.text"
                        type="button"
                        class="block w-full cursor-pointer rounded-lg px-2.5 py-[9px] text-start hover:bg-gray-100 focus-visible:bg-gray-100 focus-visible:outline-hidden dark:hover:bg-white/5 dark:focus-visible:bg-white/5"
                        @click="apply(suggestion.text)"
                    >
                        <span v-if="suggestion.label" class="mb-0.5 block text-2xs font-medium text-gray-500 dark:text-gray-400">{{ __(suggestion.label) }}</span>
                        <span class="block text-sm leading-[18px] text-gray-900 dark:text-gray-200">{{ suggestion.text }}</span>
                    </button>
                </div>
            </div>
        </div>

        <div class="flex justify-end border-t border-gray-200 px-2 py-1.5 dark:border-white/10">
            <Button size="xs" variant="ghost" icon="sync" :text="__('Try again')" :disabled="loading" @click="generate" />
        </div>
    </Popover>
</template>
