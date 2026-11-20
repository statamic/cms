<script setup>
import { computed, nextTick, onBeforeUnmount, ref, useTemplateRef, watch } from 'vue';
import { Icon, Textarea } from '@ui';
import { toast } from '@api';
import { variationsOf } from './mock-image-variations.js';

// Prototype: ask the AI for things to do with an image, in the asset editor. Opening it pins a prompt card to the image's corner, as in
// Wix Harmony. There's no frame round the image (it looked like a crop) and no "Image" chip in the card, as the editor only has the one
// image. For now it makes variations: the "Make variations" skill (or anything typed) comes back with four new images, in the card.
// Hovering one shows it in the image's place, and Save adds it as a new asset next to the original.
// Nothing is saved until you click Save, and the original is never changed, as with the AI chat, where your own save is the approval.
// The image itself comes in the slot, which gets the src and style to show (the original, or the variation being previewed).
const props = defineProps({
    asset: { type: Object, required: true },
});

const open = defineModel('open', { type: Boolean, default: false });

const instruction = ref('');
const loading = ref(false);
const variations = ref([]);
const saved = ref(new Set());
const previewing = ref(null);
let timer = null;

// The chat's Clear link (see AiChat.vue), for Cancel, in white, as the card is always dark here
const cancelLink = 'flex h-6.5 cursor-pointer items-center rounded-md px-1.5 text-xs text-gray-500 hover:text-gray-900 hover:underline dark:text-white dark:hover:text-white';

// Prototype: the skills for images, as the chat has its own (see AiChatPrompts.vue). They'd come from Settings → AI, as a third
// kind beside page and field skills. For now there's one, which makes four images like this one.
const imagePrompts = [{ name: __('Make variations'), instructions: __('Make 4 new images with the same subject and style as this one.') }];

// The skill pills have the card's own decoration (see-through, with what's behind blurred, and no edge), a little lighter than it: 55% of
// the same gray-900, against the card's 70%. Their text is trimmed like a Badge's, so it sits in the middle, with 8.5px above and below,
// so they're as tall as they were untrimmed.
const skillPill =
    'flex cursor-pointer whitespace-nowrap rounded-full bg-gray-900/55 px-2.5 py-[8.5px] text-xs text-white backdrop-blur-xl backdrop-saturate-150 transition-colors hover:bg-gray-900/70 aria-disabled:cursor-default aria-disabled:opacity-60';


// A saved image says so with a small purple pill in its bottom right corner, 21px tall, saying "Saved", with 8px either side. The text is
// trimmed like a Badge's, which centres its capitals and baseline.

// Cancel shows while images are being made (and stops them) or once they're back (and puts them away), leaving the original as it was
const cancellable = computed(() => loading.value || variations.value.length > 0);

// Prototype: "think" for a moment, then show the mock variations
function generate() {
    loading.value = true;
    previewing.value = null;
    clearTimeout(timer);
    timer = setTimeout(() => {
        variations.value = variationsOf(props.asset);
        saved.value = new Set();
        loading.value = false;
    }, 1800);
}

// Sending and cancelling take away the button you pressed (the skill pill, Cancel), so focus goes back to the input first, rather than
// being lost when the button goes. (Save stays put: it turns into Saved.)
function focusInputIfInCard() {
    document.activeElement?.closest('[data-ai-image-card]')?.querySelector('textarea')?.focus();
}

// Sending clears the box for the next request, as in the AI chat
function send() {
    if (loading.value) return;
    focusInputIfInCard();
    instruction.value = '';
    generate();
}

// Save adds it as a new asset in the same folder (prototype: nothing is saved, it just says so)
function save(index) {
    if (saved.value.has(index)) return;
    saved.value = new Set([...saved.value, index]);
    toast.success(__('Saved as :file', { file: variations.value[index].file }));
}

function dismiss() {
    focusInputIfInCard();
    clearTimeout(timer);
    loading.value = false;
    variations.value = [];
    previewing.value = null;
}

function close() {
    dismiss();
    instruction.value = '';
    open.value = false;
}

// What the slot shows: the variation you're hovering, or the original. While it's a variation, a tag above the image says which, like a
// field's "Previewing" badge.
const hovered = computed(() => variations.value[previewing.value] ?? null);

// A variation is shown at the original's size, filling it, so the image (and the card pinned to its corner) doesn't change size or move as
// you go from one to the next: the variations aren't all the same shape. The original's size is measured as you start previewing.
const imageBox = useTemplateRef('imageBox');
const originalSize = ref(null);

function preview(index) {
    if (previewing.value === null) {
        // Its exact size, fractions included: offsetWidth rounds to whole pixels, which nudged the image (and the card) by a fraction
        const image = imageBox.value?.querySelector('img');
        const size = image?.getBoundingClientRect();
        originalSize.value = size ? { width: `${size.width}px`, height: `${size.height}px` } : null;
    }
    showPreview(index);
}

// Going from one image to the next fades over 150ms: the image showing is copied into a layer on top, exactly over it, which fades out over
// the new one. Reduced motion switches at once.
const fading = ref(null);
const fadeLayer = useTemplateRef('fadeLayer');
let fadeCount = 0;

function showPreview(index) {
    if (index === previewing.value) return;
    const image = imageBox.value?.querySelector('img');

    if (image && !matchMedia('(prefers-reduced-motion: reduce)').matches) {
        const box = imageBox.value.getBoundingClientRect();
        const rect = image.getBoundingClientRect();
        const count = ++fadeCount;
        fading.value = {
            key: count,
            src: image.currentSrc || image.src,
            css: image.getAttribute('style') ?? '',
            fit: getComputedStyle(image).objectFit,
            place: { left: `${rect.left - box.left}px`, top: `${rect.top - box.top}px`, width: `${rect.width}px`, height: `${rect.height}px` },
        };
        nextTick(() => {
            fadeLayer.value?.animate([{ opacity: 1 }, { opacity: 0 }], { duration: 150, easing: 'ease-out', fill: 'forwards' }).finished.then(() => count === fadeCount && (fading.value = null), () => {});
        });
    }

    previewing.value = index;
}

const shown = computed(() =>
    hovered.value ? { src: hovered.value.src, style: { ...originalSize.value, objectFit: 'cover', ...hovered.value.style } } : { src: props.asset.preview, style: {} },
);

// The variations are cropped to the original's shape
const ratio = computed(() => (props.asset.width && props.asset.height ? `${props.asset.width} / ${props.asset.height}` : '4 / 3'));

const input = useTemplateRef('input');
watch(open, (isOpen) => isOpen && nextTick(() => input.value?.$el?.querySelector('textarea')?.focus()));

// Enter in the input sends, as in the AI chat (on a button, it presses the button). Shift+Enter starts a new line. Esc closes it, rather
// than the editor.
function onKeydown(event) {
    if (event.key === 'Enter' && !event.shiftKey && event.target.tagName === 'TEXTAREA') {
        event.preventDefault();
        send();
    }
    if (event.key === 'Escape') {
        event.stopPropagation();
        close();
    }
}

onBeforeUnmount(() => clearTimeout(timer));
</script>

<template>
    <div class="relative flex h-full min-h-0 w-full items-center justify-center">
        <!-- The image, with the prompt card pinned to its corner -->
        <div ref="imageBox" class="relative flex min-h-0 max-w-full items-center justify-center">
            <slot v-bind="shown" />

            <!-- The image that was showing, fading out over the new one (see showPreview) -->
            <div v-if="fading" ref="fadeLayer" :key="fading.key" class="pointer-events-none absolute overflow-hidden" :style="fading.place">
                <img :src="fading.src" :style="fading.css" class="size-full! max-h-none! max-w-none!" :class="fading.fit === 'cover' ? 'object-cover' : 'object-contain'" alt="" />
            </div>

            <template v-if="open">
                <!-- A tab on the photo's top edge, flush with it and square at the bottom, saying which image is showing -->
                <span v-if="hovered" class="absolute start-0 bottom-full rounded-t-md bg-ai px-2 py-1 text-xs font-medium whitespace-nowrap text-white">{{ __('Previewing :name', { name: hovered.label }) }}</span>

                <!-- Ask for something. It's the AI chat's floating card (see PanelLayout.vue and AiChat.vue), with the input straight on the card and
                     the chat's icon in front of it. It's always dark, to suit the dark area round the image, and see-through, so it takes on the
                     photo's colours, with no edge or glow (see components/ai.css). There's no ×: Ask AI and Esc close it, as Ask does the chat
                     before it's started. The skill pills sit beside the card in this wrapper, not inside it: inside, their blur would only see the
                     card, so they'd never look frosted. -->
                <div data-ai-image-card class="dark group/card absolute end-4 bottom-4 z-10 w-84 max-w-[calc(100%-2rem)] animate-in duration-150 fade-in-0 slide-in-from-bottom-2" @keydown="onKeydown">
                    <div class="ai-chat-card backdrop-blur-xl backdrop-saturate-150 rounded-xl border border-transparent ps-2.25" data-glass :data-working="loading || undefined">
                        <form class="relative p-2 ps-0.25" @submit.prevent="send">
                            <!-- The chat's icon in front of the text (see AiChat.vue). There's no send button: Enter sends. -->
                            <Icon name="ai-chat" class="pointer-events-none absolute start-2.5 top-[16.5px] z-(--z-index-above) size-4 text-white/85" />
                            <Textarea
                                ref="input"
                                v-model="instruction"
                                rows="1"
                                resize="none"
                                elastic
                                :placeholder="__('Ask for changes…')"
                                :aria-label="__('Ask the AI about this image')"
                                class="min-h-[34px] border-0 ps-8.5! pt-[3.5px]! pb-[6px]! shadow-none! outline-hidden placeholder:overflow-hidden placeholder:whitespace-nowrap placeholder:text-white/85 text-white! dark:bg-transparent!"
                                :class="cancellable ? 'pe-16!' : 'pe-2!'"
                            />

                            <p class="sr-only" role="status">{{ loading ? __('Creating 4 images…') : variations.length ? __('4 images to choose from') : '' }}</p>
                        </form>

                        <!-- The new images, in a slim column (96px) above the card, as the AI's answer, cropped to the original's shape. They take the skill
                             pills' place: the pills go once you've asked, and the column starts where they were, 12px above the card and level with its
                             right edge (13px up and 2px out, as it's placed from inside the card's 1px border). A strip of them under the
                             image looked like a gallery of the folder's images. Hovering one, or tabbing to it, shows it in the image's place. They come
                             after the input in the page, so Tab goes from the input up through them, one stop each: Save, or Saved once
                             it's saved. Tabbing away from them puts the original back. -->
                        <div v-if="loading || variations.length" class="absolute -end-0.5 bottom-full mb-[13px] flex w-24 flex-col gap-1.5" @mouseleave="showPreview(null)" @focusout="!$event.currentTarget.contains($event.relatedTarget) && showPreview(null)">
                            <template v-if="loading">
                                <div v-for="i in 4" :key="i" class="w-full animate-pulse rounded-md bg-white/10" :style="{ aspectRatio: ratio }" />
                            </template>
                            <div v-for="(variation, i) in variations" v-else :key="variation.file">
                                <div class="group relative w-full overflow-hidden rounded-md shadow-lg ring-2 ring-transparent focus-within:ring-ai hover:ring-ai" :style="{ aspectRatio: ratio }" @mouseenter="preview(i)">
                                    <img :src="variation.src" :style="variation.style" class="size-full object-cover" alt="" />

                                    <!-- Save, as a white bar across the bottom on hover or focus, which turns into the purple Saved pill. It's one button, so
                                         focus stays on it when you save it, and a saved image can still be tabbed to and previewed. Saved does nothing. -->
                                    <button
                                        type="button"
                                        class="absolute bottom-1 text-2xs font-medium"
                                        :class="
                                            saved.has(i)
                                                ? 'end-1 flex h-[21px] cursor-default items-center rounded-full bg-ai px-2 text-white'
                                                : 'inset-x-1 cursor-pointer rounded bg-white/90 py-0.5 text-gray-900 opacity-0 group-hover:opacity-100 focus-visible:opacity-100'
                                        "
                                        :aria-disabled="saved.has(i) || undefined"
                                        @focus="preview(i)"
                                        @click="save(i)"
                                    >
                                        <span v-if="saved.has(i)" class="st-text-trim-optical-cap">{{ __('Saved') }}</span>
                                        <template v-else>{{ __('Save') }}</template>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Cancel sits at the end of the input, where the chat has Clear. It comes after the new images in the page, so it's the last
                             stop for Tab. -->
                        <button v-if="cancellable" type="button" :class="cancelLink" class="absolute end-2 bottom-[12.5px]" @mousedown.prevent @click="dismiss">{{ __('Cancel') }}</button>
                    </div>

                    <!-- The skills, as pills above the card while you're in it and haven't typed anything, as the chat's are. Picking one runs it,
                         and they make way for the new images.
                         mousedown.prevent keeps focus in the input, so they stay put. Their instructions show in a tooltip on hover. They come after
                         the card in the page, as the chat's do, so Tab goes from the input to them. -->
                    <div
                        v-if="!instruction && !cancellable"
                        class="pointer-events-none absolute -end-px bottom-full mb-3 flex justify-end gap-1.5 opacity-0 transition-opacity group-focus-within/card:pointer-events-auto group-focus-within/card:opacity-100"
                    >
                        <button
                            v-for="skill in imagePrompts"
                            :key="skill.name"
                            type="button"
                            :class="skillPill"
                            :aria-disabled="loading"
                            v-tooltip="{ content: skill.instructions, hoverOnly: true, class: 'max-w-48 text-xs text-pretty' }"
                            @mousedown.prevent
                            @click="send"
                        >
                            <span class="st-text-trim-optical-cap">{{ skill.name }}</span>
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>
</template>
