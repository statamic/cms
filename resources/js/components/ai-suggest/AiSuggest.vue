<script setup>
import { computed, nextTick, onBeforeUnmount, ref, useTemplateRef, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
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
// Whether this round came from Try again, which spins its icon. The first round, and reworks from the "Make it…" box, don't.
const retrying = ref(false);
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
        retrying.value = false;
    }, rework ? 700 : 900);
}

function retry() {
    if (loading.value) return;
    retrying.value = true;
    generate();
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

// Single-line fields need the dropdown 16px below the button to clear the field's bottom border. In a multi-line field the button sits well inside it, so the dropdown can sit closer.
const isMultiline = computed(() => ['textarea', 'markdown'].includes(props.config.type));
const dropdownOffset = computed(() => (isMultiline.value ? 8 : 16));

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
    preview.value = null;

    focusField();
}

// After picking one, focus goes into the field, with the cursor at the end of the new text, so you can carry on editing it (and screen readers read out what went in). The dropdown hands focus back to the suggest button as it closes, so wait for that first.
function focusField() {
    nextTick(() =>
        setTimeout(() => {
            const field = overlay.value?.parentElement?.querySelector('input:not([aria-hidden]), textarea:not([aria-hidden])');
            if (!field) return;
            field.focus();
            field.setSelectionRange(field.value.length, field.value.length);
        }),
    );
}

// Hovering or focusing a suggestion previews it in the field, in grey (gray-500, the lightest that passes AA). It's laid over the field, matching its padding and font, until you move off it.
// The preview is an input or textarea like the field, so the browser places the text exactly where the field will. A div with the same styles sits half a pixel higher than an input's text.
const preview = ref(null);
const overlay = useTemplateRef('overlay');
const overlayTag = ref('input');
const overlayStyle = ref({});

function showPreview(text) {
    const field = overlay.value?.parentElement?.querySelector('input:not([aria-hidden]), textarea:not([aria-hidden])');

    if (field) {
        const css = getComputedStyle(field);
        overlayTag.value = field.tagName.toLowerCase();
        overlayStyle.value = {
            // The field's own padding, so the preview wraps exactly where the text will once it's picked
            padding: css.padding,
            // Longhands rather than the font shorthand, which the browser reports as empty when the field sets font features
            fontFamily: css.fontFamily,
            fontSize: css.fontSize,
            fontWeight: css.fontWeight,
            letterSpacing: css.letterSpacing,
            fontFeatureSettings: css.fontFeatureSettings,
            fontVariationSettings: css.fontVariationSettings,
            lineHeight: css.lineHeight,
            borderWidth: css.borderWidth,
            borderRadius: css.borderRadius,
        };
    }

    preview.value = text;
}

// Moving between suggestions passes the preview along. It only clears once hover and focus have both left the suggestions.
function hidePreview() {
    requestAnimationFrame(() => {
        const list = panel.value?.querySelector('[data-suggestions]');
        if (list?.matches(':hover') || list?.contains(document.activeElement)) return;
        preview.value = null;
    });
}

watch(open, (isOpen) => isOpen || (preview.value = null));

// Rewrites show what changed: words they added are highlighted in yellow and words they dropped are struck through in red, like a diff.
// Screen readers don't announce either, so each suggestion is labelled with its clean text, rather than read out with the dropped words still in it.
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

const status = computed(() => {
    if (loading.value) return __('Finding suggestions…');
    if (result.value) return __n(':count suggestion|:count suggestions', result.value.suggestions.length);
    return '';
});

const showDiff = computed(() => result.value?.mode === 'improve' && original.value);

// The "Make it…" box: type an instruction or pick a chip, and the suggestions are reworked to follow it
const instruction = ref('');

// The chips are the site's saved field prompts, from Settings → AI (only the ones for this user's role, and for this collection if they're limited to some). Sites start with Shorter, More formal and Friendlier, and can change them or add their own.
const page = usePage();
const fieldPrompts = computed(() => {
    const collection = page.url.match(/\/collections\/([^/?]+)/)?.[1];

    return (page.props._statamic?.aiPrompts ?? []).filter((saved) => saved.use_on === 'fields' && (!saved.collections.length || saved.collections.includes(collection)));
});

// Safari and Firefox on macOS don't focus a button when it's clicked, as Chrome does. Focusing it ourselves means you can press Enter to go again after clicking.
function focusClicked(event) {
    event.currentTarget.focus();
}

function follow(text) {
    if (!text.trim()) return;
    instruction.value = '';
    generate((fresh) => steer(fresh, text));
}
</script>

<template>
    <!-- Opens below the button, with its right edge lined up with the field's (the button sits 6px in from it) -->
    <Popover :open="open" @update:open="toggled" align="end" :align-offset="-6" :offset="dropdownOffset" inset class="w-80!">
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

            <!-- Tells screen readers when it's thinking and when the suggestions are in, including after Try again or reworking them -->
            <p class="sr-only" role="status">{{ status }}</p>

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
                    <!-- A faint line between suggestions, stopping short of the edges and lined up with the text, in the middle of a 5px gap. A hovered suggestion's background (an ::after) reaches 3px past its top and bottom to cover the lines either side; the suggestion is lifted above its neighbours so it covers the line below too. The first and last reach to 1px short of the borders above and below the list, so the focus outline (which pokes 1px past the ::after) meets those borders without covering them. The focus outline is drawn on the same ::after so it reaches the lines too, and hides them either side. A focused suggestion sits above a hovered one, so a hovered neighbour's background can't cover its outline. -->
                    <div v-else-if="result" data-suggestions class="p-1.5">
                        <button
                            v-for="suggestion in result.suggestions"
                            :key="suggestion.text"
                            type="button"
                            class="group relative block w-full cursor-pointer rounded-lg px-2.5 py-3 text-start not-first:mt-[5px] not-first:before:absolute not-first:before:inset-x-2.5 not-first:before:-top-[3px] not-first:before:h-px not-first:before:bg-gray-200 after:absolute after:inset-x-0 after:-inset-y-[3px] first:after:-top-[5px] last:after:-bottom-[5px] after:z-(--z-index-below) after:rounded-lg hover:z-(--z-index-above) hover:before:opacity-0 hover:after:bg-gray-100 dark:not-first:before:bg-white/10 dark:hover:after:bg-white/5 focus-visible:z-[calc(var(--z-index-above)+1)] focus-visible:outline-none focus-visible:before:opacity-0 focus-visible:after:[outline:var(--focus-outline-width)_solid_var(--focus-outline-color,currentColor)] focus-visible:after:outline-offset-(--focus-outline-offset) [&:focus-visible+button]:before:opacity-0"
                            :aria-label="suggestion.label ? `${__(suggestion.label)}: ${suggestion.text}` : suggestion.text"
                            @click="apply(suggestion.text)"
                            @mouseenter="showPreview(suggestion.text)"
                            @mouseleave="hidePreview"
                            @focus="showPreview(suggestion.text)"
                            @blur="hidePreview"
                        >
                            <span v-if="suggestion.label" class="mb-0.5 block text-2xs font-medium text-gray-500 group-hover:text-gray-600 dark:text-gray-400">{{ __(suggestion.label) }}</span>
                            <span v-if="showDiff" class="block text-sm leading-[18px] text-gray-900 dark:text-gray-200">
                                <template v-for="(part, p) in diffWords(original, suggestion.text)" :key="p">
                                    <span
                                        :class="{
                                            'rounded-sm bg-yellow-100 text-gray-900 dark:bg-yellow-400/20 dark:text-yellow-100': part.type === 'added',
                                            'rounded-sm bg-red-50 text-red-700 line-through dark:bg-red-400/15 dark:text-red-300': part.type === 'removed',
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
                        :aria-label="__('Rework the suggestions')"
                        class="w-full rounded-md border border-gray-200 bg-white px-2 py-1 text-sm dark:border-white/10 dark:bg-gray-900"
                    />
                </form>
                <!-- Hovering one shows its instructions -->
                <div v-if="fieldPrompts.length" class="flex flex-wrap gap-1.5">
                    <button
                        v-for="saved in fieldPrompts"
                        :key="saved.command"
                        type="button"
                        class="cursor-pointer rounded-full border border-gray-200 px-2 py-0.5 text-xs text-gray-600 hover:bg-gray-100 aria-disabled:cursor-default aria-disabled:opacity-50 dark:border-white/10 dark:text-gray-300 dark:hover:bg-white/5"
                        :aria-disabled="loading"
                        v-tooltip="saved.instructions"
                        @click="focusClicked($event); !loading && follow(saved.name)"
                    >
                        {{ saved.name }}
                    </button>
                </div>
            </div>

            <div class="flex justify-end border-t border-gray-200 px-2 py-1.5 dark:border-white/10">
                <!-- Not disabled while it works, as that would drop focus from the button. It just ignores clicks until the new suggestions are in. Its arrows go round, clockwise like the icon, while it tries again. -->
                <Button size="xs" variant="ghost" icon="refresh" :text="__('Try again')" :aria-disabled="loading" class="aria-disabled:cursor-default aria-disabled:opacity-50" :class="{ 'motion-safe:[&_svg]:animate-spin': retrying }" @click="focusClicked($event); retry()" />
            </div>
        </div>
    </Popover>


    <!-- The previewed suggestion, laid over the field -->
    <component
        :is="overlayTag"
        ref="overlay"
        v-show="preview"
        aria-hidden="true"
        tabindex="-1"
        readonly
        :value="preview"
        :style="overlayStyle"
        class="pointer-events-none absolute inset-0 z-(--z-index-above) size-full resize-none appearance-none overflow-hidden border-solid border-gray-300 bg-white text-gray-500 antialiased dark:border-white/10 dark:bg-gray-900 dark:text-gray-400"
    />
</template>
