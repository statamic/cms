<script setup>
import { nextTick, onBeforeUnmount, ref, useTemplateRef, watch } from 'vue';
import { Button } from '@ui';
import { altDelay } from './mock-alt-text.js';
import { icon } from '@/components/ai-suggest/use-ai-suggest.js';
import { textBoxIn, textStyleOf } from '@/components/ai-suggest/field-overlay.js';

// Prototype: a button on a field that the AI can fill in from an image, like an image's Alt Text in the asset editor, or its new name when
// you rename it (see Field.vue). It looks like the suggest fields' button, but rather than offering suggestions, it writes straight into the
// field. Nothing's asked until it's pressed, so opening an image costs no tokens. What it writes is badged as the AI's change, with Undo,
// like a picked suggestion (see suggestionApplied in Field.vue).
const props = defineProps({
    // What it writes (for now, made up from the image: see mock-alt-text.js and mock-image-edits.js)
    text: { type: Function, required: true },
    // The button's name, like "Write alt text"
    label: { type: String, required: true },
    // What screen readers hear while it writes, and once it's done
    writingStatus: { type: String, required: true },
    doneStatus: { type: String, required: true },
    // Where the button sits, as classes for the button itself
    buttonClass: { type: [String, Array, Object], default: null },
});

const emit = defineEmits(['apply']);

// Prototype: a vision model takes a couple of seconds to look at an image, so it does here too
const thinking = altDelay * 2.5;

const writing = ref(false);
const status = ref('');
let timers = [];

// Prototype: "look at" the image for a moment, then write
function write() {
    if (writing.value) return;
    writing.value = true;
    status.value = props.writingStatus;
    const text = props.text();

    timers.push(setTimeout(() => done(text), thinking));
}

function done(text) {
    emit('apply', text);
    writing.value = false;
    status.value = props.doneStatus;
}
onBeforeUnmount(() => timers.forEach(clearTimeout));

// The field's own text box, to lay the skeleton line over it exactly (covering anything already there)
const anchor = useTemplateRef('anchor');
const boxStyle = ref({});
watch(writing, (isWriting) =>
    nextTick(() => {
        const box = textBoxIn(anchor.value?.parentElement);
        if (isWriting && box) boxStyle.value = { ...textStyleOf(box), height: `${box.offsetHeight}px` };
    }),
);
</script>

<template>
    <span ref="anchor" hidden />

    <!-- While it writes, a skeleton line where the text will go, covering anything that's there already -->
    <span
        v-if="writing"
        class="pointer-events-none absolute inset-x-0 top-0 z-(--z-index-above) flex items-center overflow-hidden border-solid border-transparent bg-white bg-clip-padding dark:bg-gray-900"
        :style="boxStyle"
        aria-hidden="true"
    >
        <!-- A grey bar, pulsing (see GROUP AI / ALT TEXT SKELETON in ai.css) -->
        <span class="ai-skeleton w-3/5 rounded-full" />
    </span>

    <!-- Not disabled while it writes, as that would drop focus from the button. It just ignores clicks until the text is in, and its icon pulses. -->
    <Button
        size="xs"
        variant="ghost"
        :icon="icon"
        :class="[buttonClass, writing ? '[&_svg]:text-ai! motion-safe:[&_svg]:animate-pulse dark:[&_svg]:text-ai-light!' : '[&_svg]:text-gray-400! dark:[&_svg]:text-gray-500!']"
        class="[&_svg]:size-3.5! [&_svg]:opacity-100! aria-busy:cursor-default"
        :aria-label="label"
        :aria-busy="writing"
        v-tooltip="label"
        @click="write"
    />
    <!-- Tells screen readers it's writing, and when it's done -->
    <span class="sr-only" role="status">{{ status }}</span>
</template>
