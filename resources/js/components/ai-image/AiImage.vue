<script setup>
import { computed, nextTick, onBeforeUnmount, ref, useTemplateRef, watch } from 'vue';
import { Icon, Textarea } from '@ui';
import { toast } from '@api';
import { variationsOf } from './mock-image-variations.js';

// Prototype: ask the AI for things to do with an image, in the asset editor. Opening it pins a prompt card to the image's corner, as in
// Wix Harmony. There's no frame round the image (it looked like a crop) and no "Image" chip in the card, as the editor only has the one
// image. For now it makes variations: the "Similar images" skill (or anything typed) comes back with four new images, in the card.
// Hovering one shows it in the image's place, and Keep saves it as a new asset next to the original.
// Nothing is saved until you click Keep, and the original is never changed, as with the AI chat, where your own save is the approval.
// The image itself comes in the slot, which gets the src and style to show (the original, or the variation being previewed).
const props = defineProps({
    asset: { type: Object, required: true },
});

const open = defineModel('open', { type: Boolean, default: false });

const instruction = ref('');
const loading = ref(false);
const variations = ref([]);
const kept = ref(new Set());
const previewing = ref(null);
let timer = null;

// The chat's skill pills (see AiChat.vue) and its Clear link. The pills' text is a step brighter than the chat's, and Cancel is white,
// as the card is always dark here.
const pill = 'cursor-pointer whitespace-nowrap rounded-full bg-white px-2.5 py-1 text-xs text-gray-700 shadow-ui-sm ring ring-gray-200 transition-colors hover:bg-gray-50 hover:text-gray-900 aria-disabled:cursor-default aria-disabled:opacity-60 dark:bg-gray-850 dark:text-gray-200 dark:ring-gray-700/80 dark:hover:bg-gray-800';
const cancelLink = 'flex h-6.5 cursor-pointer items-center rounded-md px-1.5 text-xs text-gray-500 hover:text-gray-900 hover:underline dark:text-white';

// Prototype: the skills for images, as the chat has its own (see AiChatPrompts.vue). They'd come from Settings → AI, as a third
// kind beside page and field skills. For now there's one, which makes four images like this one.
const imagePrompts = [{ name: __('Similar images'), instructions: __('Make 4 new images with the same subject and style as this one.') }];

// Cancel shows while images are being made (and stops them) or once they're back (and puts them away), leaving the original as it was
const cancellable = computed(() => loading.value || variations.value.length > 0);

// Prototype: "think" for a moment, then show the mock variations
function generate() {
    loading.value = true;
    previewing.value = null;
    clearTimeout(timer);
    timer = setTimeout(() => {
        variations.value = variationsOf(props.asset);
        kept.value = new Set();
        loading.value = false;
    }, 1800);
}

// Sending clears the box for the next request, as in the AI chat
function send() {
    if (loading.value) return;
    instruction.value = '';
    generate();
}

// Keep saves it as a new asset in the same folder (prototype: nothing is saved, it just says so)
function keep(index) {
    if (kept.value.has(index)) return;
    kept.value = new Set([...kept.value, index]);
    toast.success(__('Saved as :file', { file: variations.value[index].file }));
}

function dismiss() {
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
const shown = computed(() => (hovered.value ? { src: hovered.value.src, style: hovered.value.style } : { src: props.asset.preview, style: {} }));

// The variations are cropped to the original's shape
const ratio = computed(() => (props.asset.width && props.asset.height ? `${props.asset.width} / ${props.asset.height}` : '4 / 3'));

const input = useTemplateRef('input');
watch(open, (isOpen) => isOpen && nextTick(() => input.value?.$el?.querySelector('textarea')?.focus()));

// Enter sends, as in the AI chat. Shift+Enter starts a new line. Esc closes it, rather than the editor.
function onKeydown(event) {
    if (event.key === 'Enter' && !event.shiftKey) {
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
        <div class="relative flex min-h-0 max-w-full items-center justify-center">
            <slot v-bind="shown" />

            <template v-if="open">
                <span v-if="hovered" class="absolute start-0 -top-8 rounded-md bg-ai px-2 py-1 text-xs font-medium whitespace-nowrap text-white shadow-sm">{{ __('Previewing :name', { name: hovered.label }) }}</span>

                <!-- Ask for something. It's the AI chat's floating card (see PanelLayout.vue and AiChat.vue), with the input straight on the card and
                     the chat's icon in front of it. It's always dark, to suit the dark area round the image, and see-through, so it takes on the
                     photo's colours, with no edge and the purple glow while it's focused or making images (see components/ai.css). There's no ×:
                     Ask AI and Esc close it, as Ask does the chat before it's started. -->
                <div
                    class="ai-chat-card dark group/card backdrop-blur-xl backdrop-saturate-150 absolute end-4 bottom-4 z-10 w-84 max-w-[calc(100%-2rem)] animate-in rounded-xl border border-transparent ps-2.25 transition-shadow duration-150 fade-in-0 slide-in-from-bottom-2"
                    data-glass
                    :data-working="loading || undefined"
                    @keydown="onKeydown"
                >
                    <!-- The skills, as pills above the card while you're in it and haven't typed anything, as the chat's are. Picking one runs it.
                         mousedown.prevent keeps focus in the input, so they stay put. Their instructions show in a tooltip on hover. -->
                    <div
                        v-if="!instruction"
                        class="pointer-events-none absolute -end-px bottom-full mb-3 flex justify-end gap-1.5 opacity-0 transition-opacity group-focus-within/card:pointer-events-auto group-focus-within/card:opacity-100"
                    >
                        <button
                            v-for="saved in imagePrompts"
                            :key="saved.name"
                            type="button"
                            :class="pill"
                            :aria-disabled="loading"
                            v-tooltip="{ content: saved.instructions, hoverOnly: true, class: 'max-w-48 text-xs' }"
                            @mousedown.prevent
                            @click="send"
                        >
                            {{ saved.name }}
                        </button>
                    </div>

                    <!-- The new images, in the card above the input, as the AI's answer, cropped to the original's shape. A strip of them under the
                         image looked like a gallery of the folder's images. Hovering one shows it in the image's place. -->
                    <div v-if="loading || variations.length" class="flex gap-1.5 pe-2.25 pt-2.25" @mouseleave="previewing = null">
                        <template v-if="loading">
                            <div v-for="i in 4" :key="i" class="flex-1 animate-pulse rounded-md bg-white/10" :style="{ aspectRatio: ratio }" />
                        </template>
                        <div v-for="(variation, i) in variations" v-else :key="variation.file" class="group relative flex-1 overflow-hidden rounded-md ring-2 ring-transparent hover:ring-ai" :style="{ aspectRatio: ratio }" @mouseenter="previewing = i">
                            <img :src="variation.src" :style="variation.style" class="size-full object-cover" alt="" />
                            <button v-if="!kept.has(i)" type="button" class="absolute inset-x-1 bottom-1 cursor-pointer rounded bg-white/90 py-0.5 text-2xs font-medium text-gray-900 opacity-0 group-hover:opacity-100 focus-visible:opacity-100" @focus="previewing = i" @click="keep(i)">{{ __('Keep') }}</button>
                            <span v-else class="absolute end-1 bottom-1 flex items-center gap-1 rounded bg-ai px-1 py-0.5 text-2xs font-medium text-white"><Icon name="checkmark" class="size-2.5" />{{ __('Kept') }}</span>
                        </div>
                    </div>

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

                        <!-- Cancel sits at the end, where the chat has Clear -->
                        <button v-if="cancellable" type="button" :class="cancelLink" class="absolute end-2 bottom-[12.5px]" @mousedown.prevent @click="dismiss">{{ __('Cancel') }}</button>

                        <p class="sr-only" role="status">{{ loading ? __('Creating 4 images…') : variations.length ? __('4 images to choose from') : '' }}</p>
                    </form>
                </div>
            </template>
        </div>
    </div>
</template>
