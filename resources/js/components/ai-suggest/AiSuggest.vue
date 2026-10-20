<script setup>
import { computed, nextTick, onBeforeUnmount, ref, useTemplateRef, watch } from 'vue';
import { Button, Icon, Popover } from '@ui';
import { steer, suggestFor } from './mock-suggestions.js';
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
// What was in the field when the suggestions were made, to show what changed
const original = ref('');
let timer = null;

// Prototype: "think" for a moment, then show the mock suggestions. rework reshapes them, to follow an instruction from the "Make it…" box.
function generate(rework = null) {
    loading.value = true;
    clearTimeout(timer);
    timer = setTimeout(() => {
        original.value = typeof props.value === 'string' ? props.value : '';
        const fresh = suggestFor(props.config, props.value, props.values);
        result.value = rework ? rework(fresh) : fresh;
        loading.value = false;
    }, rework ? 700 : 900);
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

// Arrow keys go round the suggestions and the "Make it…" box (which is below them), so Up from the box lands on the last suggestion.
const panel = useTemplateRef('panel');

function onArrowKey(event) {
    if (!['ArrowDown', 'ArrowUp'].includes(event.key)) return;

    const stops = [...panel.value.querySelectorAll('[data-suggestions] button, input')];
    const index = stops.indexOf(document.activeElement);
    if (index === -1 || stops.length < 2) return;

    event.preventDefault();
    stops[(index + (event.key === 'ArrowDown' ? 1 : -1) + stops.length) % stops.length].focus();
}

function apply(text) {
    emit('apply', text);
    open.value = false;
}


// Rewrites show what changed: words they added are highlighted in yellow and words they dropped are struck through.
// A word-level diff, from the longest run of words the two have in common.
function diffWords(from, to) {
    const a = from.split(/\s+/).filter(Boolean);
    const b = to.split(/\s+/).filter(Boolean);
    const key = (word) => word.toLowerCase().replace(/[^\w'-]/g, '');
    const common = Array.from({ length: a.length + 1 }, () => Array(b.length + 1).fill(0));

    for (let i = a.length - 1; i >= 0; i--) {
        for (let j = b.length - 1; j >= 0; j--) {
            common[i][j] = key(a[i]) === key(b[j]) ? common[i + 1][j + 1] + 1 : Math.max(common[i + 1][j], common[i][j + 1]);
        }
    }

    const parts = [];
    let i = 0;
    let j = 0;

    while (i < a.length || j < b.length) {
        if (i < a.length && j < b.length && key(a[i]) === key(b[j])) {
            parts.push({ text: b[j++], type: 'same' });
            i++;
        } else if (j < b.length && (i === a.length || common[i][j + 1] >= common[i + 1][j])) {
            parts.push({ text: b[j++], type: 'added' });
        } else {
            parts.push({ text: a[i++], type: 'removed' });
        }
    }

    return parts;
}

const showDiff = computed(() => result.value?.mode === 'improve' && original.value);

// The "Make it…" box: type an instruction or pick a chip, and the suggestions are reworked to follow it
const instruction = ref('');
const chips = ['Shorter', 'More formal', 'Friendlier'];

function follow(text) {
    if (!text.trim()) return;
    instruction.value = '';
    generate((fresh) => steer(fresh, text));
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

        <!-- Up and down move between the suggestions and the "Make it…" box, as well as Tab -->
        <div ref="panel" @keydown="onArrowKey">
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
                    <div v-else-if="result" data-suggestions class="space-y-0.5 p-1.5">
                        <button
                            v-for="suggestion in result.suggestions"
                            :key="suggestion.text"
                            type="button"
                            class="block w-full cursor-pointer rounded-lg px-2.5 py-[9px] text-start hover:bg-gray-100 dark:hover:bg-white/5"
                            @click="apply(suggestion.text)"
                        >
                            <span v-if="suggestion.label" class="mb-0.5 block text-2xs font-medium text-gray-500 dark:text-gray-400">{{ __(suggestion.label) }}</span>
                            <span v-if="showDiff" class="block text-sm leading-[18px] text-gray-900 dark:text-gray-200">
                                <template v-for="(part, p) in diffWords(original, suggestion.text)" :key="p">
                                    <span
                                        :class="{
                                            'rounded-sm bg-yellow-100 text-gray-900 dark:bg-yellow-400/20 dark:text-yellow-100': part.type === 'added',
                                            'text-gray-400 line-through dark:text-gray-500': part.type === 'removed',
                                        }"
                                        >{{ part.text }}</span
                                    >{{ ' ' }}
                                </template>
                            </span>
                            <span v-else class="block text-sm leading-[18px] text-gray-900 dark:text-gray-200">{{ suggestion.text }}</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tell it how to rework the suggestions -->
            <div class="space-y-2 border-t border-gray-200 px-3 py-2.5 dark:border-white/10">
                <form @submit.prevent="follow(instruction)">
                    <input
                        v-model="instruction"
                        type="text"
                        :placeholder="__('Make it…')"
                        class="w-full rounded-md border border-gray-200 bg-white px-2 py-1 text-sm dark:border-white/10 dark:bg-gray-900"
                    />
                </form>
                <div class="flex flex-wrap gap-1.5">
                    <button
                        v-for="chip in chips"
                        :key="chip"
                        type="button"
                        class="cursor-pointer rounded-full border border-gray-200 px-2 py-0.5 text-xs text-gray-600 hover:bg-gray-100 aria-disabled:cursor-default aria-disabled:opacity-50 dark:border-white/10 dark:text-gray-300 dark:hover:bg-white/5"
                        :aria-disabled="loading"
                        @click="!loading && follow(chip)"
                    >
                        {{ __(chip) }}
                    </button>
                </div>
            </div>

            <div class="flex justify-end border-t border-gray-200 px-2 py-1.5 dark:border-white/10">
                <!-- Not disabled while it works, as that would drop focus from the button. It just ignores clicks until the new suggestions are in. -->
                <Button size="xs" variant="ghost" icon="refresh" :text="__('Try again')" :aria-disabled="loading" class="aria-disabled:cursor-default aria-disabled:opacity-50" @click="!loading && generate()" />
            </div>
        </div>
    </Popover>

</template>
