<script setup>
import { computed, nextTick, onBeforeUnmount, ref, useTemplateRef, watch } from 'vue';
import { Icon, Textarea } from '@ui';
import { toast } from '@api';
import { variationsOf } from './mock-image-variations.js';
import { cutoutsOf, erasuresOf, extensionsOf, upscalesOf } from './mock-image-edits.js';
import AiChatPrompts from '@/components/ai-chat/AiChatPrompts.vue';
import AiImageNewSkill from './AiImageNewSkill.vue';
import { usePage } from '@inertiajs/vue3';
// Prototype: whether the person can open Settings → AI, set by the demos in the nav
import { canEditAi } from '@/components/ai-chat/mock-runs.js';

// Prototype: ask the AI for things to do with an image, in the asset editor. Opening it pins a prompt card to the image's corner, as in
// Wix Harmony. There's no frame round the image (it looked like a crop) and no "Image" chip in the card, as the editor only has the one
// image. Its skills (Make variations, Remove background, Extend, Upscale, Erase something), or anything typed, come back with new images,
// in a column above the card. Hovering one shows it in the image's place, and Save adds it as a new asset next to the original.
// Nothing is saved until you click Save, and the original is never changed, as with the AI chat, where your own save is the approval.
// The image itself comes in the slot, which gets the src and style to show (the original, or the variation being previewed).
const props = defineProps({
    asset: { type: Object, required: true },
});

const open = defineModel('open', { type: Boolean, default: false });


const instruction = ref('');
const loading = ref(false);
const variations = ref([]);
// The skill that's running or ran last, for how many images to wait for and what to call them
const running = ref(null);
const saved = ref(new Set());
const previewing = ref(null);
let timer = null;

// The chat's Clear link (see AiChat.vue), for Cancel, in white, as the card is always dark here
const cancelLink = 'flex h-6.5 cursor-pointer items-center rounded-md px-1.5 text-xs text-gray-500 hover:text-gray-900 hover:underline dark:text-white dark:hover:text-white';

// Prototype: what the image model can do, which each image skill starts from (see imagePrompts). Each makes a few images (count), with run
// (see mock-image-variations.js and mock-image-edits.js). Erase something first asks you to paint over what to take out (brush). They're
// also what a typed request runs (see skillFor), and, until the skills come from Settings → AI, the skills themselves.
const builtInSkills = [
    { name: __('Make variations'), command: 'variations', instructions: __('Make 4 new images with the same subject and style as this one.'), count: 4, run: variationsOf },
    { name: __('Remove background'), command: 'remove-background', instructions: __('Cut out the subject, on a see-through background.'), count: 2, run: cutoutsOf },
    { name: __('Extend'), command: 'extend', instructions: __('Widen or heighten the image to common shapes, filling in the new edges.'), count: 3, run: extensionsOf },
    { name: __('Upscale'), command: 'upscale', instructions: __('Make it bigger and sharper, for large spaces.'), count: 2, run: upscalesOf },
    { name: __('Erase something'), command: 'erase', instructions: __('Paint over something to take it out, and the AI fills in the gap.'), count: 2, run: erasuresOf, brush: true },
];

// As in the AI chat (see AiChat.vue): up to three show as pills above the card. With more, a "+ 2" pill follows, which opens the list of
// them all, so lots of them don't pile up over the image, and they stay on one row.
const promptPills = computed(() => imagePrompts.value.slice(0, 3));
const morePrompts = computed(() => imagePrompts.value.length - promptPills.value.length);

// The list of skills, above the card, as the chat's help: opened by "+ 2" or by typing /. Typing / and then letters narrows it to the skills
// whose command, or a word in whose name, starts with them. Up and down highlight one, and Enter runs it. Typing anything else, Esc, or
// leaving the card puts it away.
const showSkills = ref(false);
const activeSkill = ref(-1);
const commandQuery = computed(() => instruction.value.match(/^\/([\w-]*)$/)?.[1]?.toLowerCase() ?? null);
const skillsOpen = computed(() => showSkills.value || commandQuery.value !== null);
const listedSkills = computed(() => {
    const query = commandQuery.value;
    if (!query) return imagePrompts.value;
    return imagePrompts.value.filter((skill) => skill.command.startsWith(query) || skill.name.toLowerCase().split(/\s+/).some((word) => word.startsWith(query)));
});
// While typing a command, the first match is highlighted, so Enter runs it
watch(commandQuery, (query) => (activeSkill.value = query !== null && listedSkills.value.length ? 0 : -1));
watch(showSkills, () => commandQuery.value === null && (activeSkill.value = -1));

// Opening the list takes away the pills, "+ 2" among them, so focus goes back to the input first rather than being lost (from the keyboard)
function openSkills() {
    focusInputIfInCard();
    showSkills.value = true;
}

function pickFromList(skill) {
    showSkills.value = false;
    instruction.value = '';
    pick(skill);
}

function onCardFocusOut(event) {
    if (!event.currentTarget.contains(event.relatedTarget)) showSkills.value = false;
}
const [variationsSkill, cutoutSkill, extendSkill, upscaleSkill, eraseSkill] = builtInSkills;

// The image skills, from Settings → AI (or saved from here, with Save as skill), kept like page skills: the ones it comes with, and any the
// site has changed or added. Each starts from something the image model can do, or a plain edit (which here makes variations), and makes as
// many images as it says. (Prototype: its instructions and look aren't used, as nothing goes to an AI. An extend puts the shape it asks for
// first.)
const page = usePage();
const imagePrompts = computed(() => {
    const settings = page.props._statamic?.aiImagePrompts;
    if (!Array.isArray(settings)) return builtInSkills;
    const shapes = { '16:9': __('Wide'), '1:1': __('Square'), '4:5': __('Portrait') };
    return settings.map((skill) => {
        const base = builtInSkills.find(({ command }) => command === skill.starts_from) ?? variationsSkill;
        const first = (results) => [...results].sort((a, b) => (b.label === shapes[skill.shape]) - (a.label === shapes[skill.shape]));
        return {
            name: skill.name,
            command: skill.command,
            instructions: skill.instructions,
            count: skill.makes,
            brush: base.brush,
            run: async (asset, mask) => first(await base.run(asset, mask)).slice(0, skill.makes),
        };
    });
});

// Prototype: what a typed request is asking for, from its words. Anything else makes variations.
function skillFor(text) {
    if (/background|cut ?out/i.test(text)) return cutoutSkill;
    if (/extend|expand|wider|taller|widen|square|portrait|landscape|16:9/i.test(text)) return extendSkill;
    if (/upscale|bigger|larger|sharper|resolution/i.test(text)) return upscaleSkill;
    if (/erase|remove|get rid|take out/i.test(text)) return eraseSkill;
    return variationsSkill;
}

// The skill pills have the card's own decoration (see-through, with what's behind blurred, and no edge), a little lighter than it: 55% of
// the same gray-900, against the card's 70%. Their text is trimmed like a Badge's, so it sits in the middle, with 8.5px above and below,
// so they're as tall as they were untrimmed.
const skillPill =
    'ai-glass-in flex cursor-pointer whitespace-nowrap rounded-full bg-gray-900/55 px-2.5 py-[8.5px] text-xs text-white backdrop-blur-xl backdrop-saturate-150 transition-colors hover:bg-gray-900/70 aria-disabled:cursor-default aria-disabled:opacity-60';


// A saved image says so with a small purple pill in its bottom right corner, 21px tall, saying "Saved", with 8px either side. The text is
// trimmed like a Badge's, which centres its capitals and baseline.

// Cancel shows while you're painting what to erase, while images are being made (and stops them) or once they're back (and puts them
// away), leaving the original as it was
const cancellable = computed(() => brushing.value || loading.value || variations.value.length > 0);

// Prototype: "think" for a moment, then show the skill's mock images. A newer request (or Cancel) drops the results of an older one.
let request = 0;
function generate(skill) {
    loading.value = true;
    running.value = skill;
    previewing.value = null;
    variations.value = [];
    const mask = skill.brush && painted.value ? brushCanvas.value : null;
    brushing.value = false;
    const current = ++request;
    Promise.all([skill.run(props.asset, mask), new Promise((resolve) => (timer = setTimeout(resolve, 1800)))]).then(([results]) => {
        if (current !== request) return;
        variations.value = results;
        saved.value = new Set();
        loading.value = false;
        painted.value = false;
    });
}

// Picking a skill runs it, apart from Erase something, which first asks you to paint over what to erase
function pick(skill) {
    if (loading.value) return;
    focusInputIfInCard();
    if (skill.brush) return startBrushing();
    generate(skill);
}

// Sending and cancelling take away the button you pressed (the skill pill, Cancel), so focus goes back to the input first, rather than
// being lost when the button goes. (Save stays put: it turns into Saved.)
function focusInputIfInCard() {
    document.activeElement?.closest('[data-ai-image-card]')?.querySelector('textarea')?.focus();
}

// Sending clears the box for the next request, as in the AI chat. While you're painting what to erase, it erases.
function send() {
    if (loading.value) return;
    focusInputIfInCard();
    const skill = brushing.value ? eraseSkill : skillFor(instruction.value);
    asked.value = instruction.value.trim() ? { instructions: instruction.value.trim(), startsFrom: skill === variationsSkill ? 'edit' : skill.command } : null;
    instruction.value = '';
    generate(skill);
}

// Save as skill: once a typed request has come back with images, it can be kept as an image skill, to run again with its /command. Only
// for people who can edit Settings → AI, as it adds to it. Skills you picked are already skills, so it's only for typed requests.
const asked = ref(null);
const newSkillFrom = ref(null);
const canSaveSkill = computed(() => canEditAi.value && asked.value && variations.value.length > 0 && !loading.value);

function startNewSkill() {
    newSkillFrom.value = asked.value;
}

// Back to the link that opened it, or the input once it's saved (the link goes)
function cancelNewSkill() {
    newSkillFrom.value = null;
    nextTick(() => saveSkillLink.value?.focus());
}

function savedNewSkill(command) {
    newSkillFrom.value = null;
    asked.value = null;
    nextTick(() => input.value?.$el?.querySelector('textarea')?.focus());
    toast.success(__('Saved. Run it with /:command', { command }));
}

const saveSkillLink = useTemplateRef('saveSkillLink');

// Save adds it as a new asset in the same folder (prototype: nothing is saved, it just says so)
function save(index) {
    if (saved.value.has(index)) return;
    saved.value = new Set([...saved.value, index]);
    toast.success(__('Saved as :file', { file: variations.value[index].file }));
}

function dismiss() {
    focusInputIfInCard();
    newSkillFrom.value = null;
    asked.value = null;
    clearTimeout(timer);
    request++;
    loading.value = false;
    variations.value = [];
    previewing.value = null;
    brushing.value = false;
    painted.value = false;
}

const emit = defineEmits(['closed']);

// Closing it from inside (Esc) takes away what had focus, so it tells the editor, which puts focus back on Edit with AI (see Editor.vue)
function close() {
    const hadFocus = !!document.activeElement?.closest('[data-ai-image-card]');
    dismiss();
    instruction.value = '';
    open.value = false;
    if (hadFocus) emit('closed');
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

// A result the original's shape fills its place. One that isn't (an extended image) is shown whole, inside it, so you can see the new edges.
const shown = computed(() =>
    hovered.value
        ? { src: hovered.value.src, style: { ...originalSize.value, objectFit: hovered.value.ratio ? 'contain' : 'cover', ...hovered.value.style } }
        : { src: props.asset.preview, style: {} },
);

// The new images are shown at the original's shape, or their own if they're different (an extended image)
const ratio = computed(() => (props.asset.width && props.asset.height ? `${props.asset.width} / ${props.asset.height}` : '4 / 3'));

// What the card tells screen readers: that it's working, then how many images came back
const status = computed(() => {
    if (brushing.value) return __('Paint over what to erase, or describe it, then press Enter');
    if (loading.value) return __('Making :count images…', { count: running.value?.count ?? 4 });
    return variations.value.length ? __(':count images to choose from', { count: variations.value.length }) : '';
});

// Erase something: you paint over what to take out, on a layer over the image, in the AI's purple at half strength. What you paint is kept
// at full strength on the canvas, so it can be used as the mask of what to erase; the half strength is the canvas's own opacity.
const brushing = ref(false);
const painted = ref(false);
const brushCanvas = useTemplateRef('brushCanvas');
const brushPlace = ref(null);
let drawing = false;

function startBrushing() {
    const image = imageBox.value?.querySelector('img');
    if (!image) return;
    const box = imageBox.value.getBoundingClientRect();
    const rect = image.getBoundingClientRect();
    brushPlace.value = { left: `${rect.left - box.left}px`, top: `${rect.top - box.top}px`, width: `${rect.width}px`, height: `${rect.height}px` };
    brushing.value = true;
    painted.value = false;
    nextTick(() => {
        const canvas = brushCanvas.value;
        canvas.width = rect.width * devicePixelRatio;
        canvas.height = rect.height * devicePixelRatio;
    });
}

function paintAt(event) {
    const canvas = brushCanvas.value;
    const rect = canvas.getBoundingClientRect();
    const context = canvas.getContext('2d');
    const x = ((event.clientX - rect.left) / rect.width) * canvas.width;
    const y = ((event.clientY - rect.top) / rect.height) * canvas.height;
    // A brush about a twentieth of the image's width
    context.lineWidth = canvas.width / 20;
    context.lineCap = context.lineJoin = 'round';
    context.strokeStyle = context.fillStyle = 'rgb(124 58 237)';
    if (!drawing) {
        context.beginPath();
        context.moveTo(x, y);
    }
    context.lineTo(x, y);
    context.stroke();
    painted.value = true;
}

function startPainting(event) {
    // A demo's made-up pointer can't be captured (see AiChatDemos.vue), so carry on without it
    try {
        event.currentTarget.setPointerCapture(event.pointerId);
    } catch {}
    drawing = false;
    paintAt(event);
    drawing = true;
}

const paint = (event) => drawing && paintAt(event);
const stopPainting = () => (drawing = false);

const input = useTemplateRef('input');
watch(open, (isOpen) => isOpen && nextTick(() => input.value?.$el?.querySelector('textarea')?.focus()));

// Enter in the input sends, as in the AI chat (on a button, it presses the button). Shift+Enter starts a new line. Esc closes it, rather
// than the editor.
// While the list of skills is open, Up and Down highlight one (from the top or the bottom), Enter runs it, and Esc puts the list away.
function onKeydown(event) {
    const inInput = event.target.tagName === 'TEXTAREA';

    if (inInput && skillsOpen.value && (event.key === 'ArrowDown' || event.key === 'ArrowUp')) {
        event.preventDefault();
        const count = listedSkills.value.length;
        if (!count) return;
        const from = activeSkill.value;
        activeSkill.value = from < 0 ? (event.key === 'ArrowDown' ? 0 : count - 1) : (from + (event.key === 'ArrowDown' ? 1 : -1) + count) % count;
        return;
    }
    if (event.key === 'Enter' && !event.shiftKey && inInput) {
        event.preventDefault();
        if (skillsOpen.value && activeSkill.value >= 0) return pickFromList(listedSkills.value[activeSkill.value]);
        send();
        return;
    }
    if (event.key === 'Escape') {
        event.stopPropagation();
        if (skillsOpen.value) {
            showSkills.value = false;
            if (commandQuery.value !== null) instruction.value = '';
            return;
        }
        close();
        return;
    }
    // Typing anything puts the list away, as in the chat (/ then letters narrows it instead)
    if (inInput && showSkills.value && !['Shift', 'Control', 'Alt', 'Meta', 'Tab', 'ArrowDown', 'ArrowUp'].includes(event.key)) showSkills.value = false;
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
                <span v-else-if="brushing" class="absolute start-0 bottom-full rounded-t-md bg-ai px-2 py-1 text-xs font-medium whitespace-nowrap text-white">{{ __('Paint over what to erase') }}</span>

                <!-- An upscale, previewed as a close-up of the image's middle, split down the middle: the left half is the original enlarged (soft),
                     the right the upscale (sharp). Both are the same close-up, one over the other, so the picture carries on across the line. -->
                <div v-if="hovered?.compare" class="pointer-events-none absolute inset-0 overflow-hidden" aria-hidden="true">
                    <div class="absolute inset-0 bg-size-[400%] bg-center" :style="{ backgroundImage: `url(${hovered.compare.after})` }" />
                    <div class="absolute inset-0 bg-size-[400%] bg-center [clip-path:inset(0_50%_0_0)]" :style="{ backgroundImage: `url(${hovered.compare.before})` }" />
                    <div class="absolute inset-y-0 start-1/2 w-0.5 -translate-x-1/2 bg-white" />
                    <span class="absolute start-2 top-2 rounded-full bg-gray-900/70 px-2 py-0.5 text-2xs font-medium text-white">{{ __('Before') }}</span>
                    <span class="absolute end-2 top-2 rounded-full bg-ai px-2 py-0.5 text-2xs font-medium text-white">{{ __('After') }}</span>
                </div>

                <!-- Erase something: paint over what to take out. mousedown.prevent keeps focus in the input as you paint, so Tab goes on to Erase. -->
                <canvas
                    v-if="brushing"
                    ref="brushCanvas"
                    class="absolute cursor-crosshair touch-none opacity-50"
                    :style="brushPlace"
                    aria-hidden="true"
                    @pointerdown="startPainting"
                    @mousedown.prevent
                    @pointermove="paint"
                    @pointerup="stopPainting"
                    @pointercancel="stopPainting"
                />

                <!-- Ask for something. It's the AI chat's floating card (see PanelLayout.vue and AiChat.vue), with the input straight on the card and
                     the chat's icon in front of it. It's always dark, to suit the dark area round the image, and see-through, so it takes on the
                     photo's colours, with no edge or glow (see components/ai.css). There's no ×: Edit with AI and Esc close it, as Ask does the chat
                     before it's started. The skill pills sit beside the card in this wrapper, not inside it: inside, their blur would only see the
                     card, so they'd never look frosted. -->
                <!-- 400px wide, so three skill pills and "+ 2" fit over it -->
                <div
                    data-ai-image-card
                    class="dark group/card absolute end-4 bottom-4 z-10 w-100 max-w-[calc(100%-2rem)]"
                    @keydown="onKeydown"
                    @focusout="onCardFocusOut"
                >
                    <!-- It's there at once, as the floating AI chat is -->
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
                                :placeholder="brushing ? __('Paint over it, or describe it…') : __('Ask for image changes…')"
                                :aria-label="__('Ask the AI about this image')"
                                class="min-h-[34px] border-0 ps-8.5! pt-[3.5px]! pb-[6px]! shadow-none! outline-hidden placeholder:overflow-hidden placeholder:whitespace-nowrap placeholder:text-white/85 text-white! dark:bg-transparent!"
                                :class="canSaveSkill ? 'pe-36!' : cancellable ? 'pe-16!' : 'pe-2!'"
                            />

                            <p class="sr-only" role="status">{{ status }}</p>
                        </form>

                        <!-- The new images, in a slim column (96px) above the card, as the AI's answer, cropped to the original's shape. They take the skill
                             pills' place: the pills go once you've asked, and the column starts where they were, 12px above the card and level with its
                             right edge (13px up and 2px out, as it's placed from inside the card's 1px border). A strip of them under the
                             image looked like a gallery of the folder's images. Hovering one, or tabbing to it, shows it in the image's place. They come
                             after the input in the page, so Tab goes from the input up through them, one stop each: Save, or Saved once
                             it's saved. Tabbing away from them puts the original back. -->
                        <div v-if="(loading || variations.length) && !newSkillFrom" class="absolute -end-0.5 bottom-full mb-[13px] flex w-24 flex-col gap-1.5" @mouseleave="showPreview(null)" @focusout="!$event.currentTarget.contains($event.relatedTarget) && showPreview(null)">
                            <template v-if="loading">
                                <div v-for="i in running?.count ?? 4" :key="i" class="w-full animate-pulse rounded-md bg-white/10" :style="{ aspectRatio: ratio }" />
                            </template>
                            <div v-for="(variation, i) in variations" v-else :key="variation.file">
                                <div class="group relative w-full overflow-hidden rounded-md shadow-lg ring-2 ring-transparent focus-within:ring-ai hover:ring-ai" :style="{ aspectRatio: variation.ratio ?? ratio }" @mouseenter="preview(i)">
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
                    </div>

                    <!-- The skills, as pills above the card while you're in it and haven't typed anything, as the chat's are. Picking one runs it,
                         and they make way for the new images. Opening the card, they fade in where they are, as the chat's do (see ai-glass-in).
                         mousedown.prevent keeps focus in the input, so they stay put. Their instructions show in a tooltip on hover. They come after
                         the card in the page, as the chat's do, so Tab goes from the input to them. -->
                    <div
                        v-if="!instruction && !cancellable && !skillsOpen"
                        class="absolute -end-px bottom-full mb-3 hidden max-w-full justify-end gap-1.5 group-focus-within/card:flex"
                    >
                        <button
                            v-for="skill in promptPills"
                            :key="skill.command"
                            type="button"
                            :class="skillPill"
                            class="max-w-36"
                            :aria-disabled="loading"
                            v-tooltip="{ content: skill.instructions, hoverOnly: true, class: 'max-w-48 text-xs text-pretty' }"
                            @mousedown.prevent
                            @click="pick(skill)"
                        >
                            <!-- A long name ends in "…", clipped only side to side, as in the chat, so the trimmed text keeps its ascenders and descenders -->
                            <span class="min-w-0 overflow-x-clip text-ellipsis whitespace-nowrap st-text-trim-optical-cap">{{ skill.name }}</span>
                        </button>
                        <!-- The rest, as "+ 2", with "2 more" in its tooltip. It opens them all. -->
                        <button
                            v-if="morePrompts"
                            type="button"
                            :class="skillPill"
                            class="shrink-0 px-2! tabular-nums"
                            v-tooltip="{ content: __(':count more', { count: morePrompts }), hoverOnly: true }"
                            @mousedown.prevent
                            @click="openSkills"
                        >
                            <span class="st-text-trim-optical-cap">+ {{ morePrompts }}</span>
                        </button>
                    </div>


                    <!-- All the skills, as the chat's help lists them (see AiChatPrompts.vue), above the card. It takes the card's dark look. -->
                    <Transition
                        enter-active-class="transition duration-150 ease-out motion-reduce:transition-none"
                        enter-from-class="translate-y-3 opacity-0"
                        leave-active-class="transition duration-150 ease-out motion-reduce:transition-none"
                        leave-to-class="translate-y-3 opacity-0"
                    >
                        <div v-if="skillsOpen && !cancellable" class="absolute -start-px bottom-full z-(--z-index-portal) mb-3 w-[calc(100%+2px)] rounded-2xl border border-gray-700 bg-gray-900 shadow-ui-xl">
                            <AiChatPrompts v-if="listedSkills.length" :prompts="listedSkills" :active="activeSkill" @pick="pickFromList" />
                            <p v-else class="px-3 py-3 text-sm text-gray-400">{{ __('No skill matches. Enter sends it as a request.') }}</p>
                        </div>
                    </Transition>

                    <!-- Save as skill's form, in the same place as the list of skills, while the new images make way for it -->
                    <div v-if="newSkillFrom" class="absolute -start-px bottom-full z-(--z-index-portal) mb-3 w-[calc(100%+2px)] rounded-2xl border border-gray-700 bg-gray-900 shadow-ui-xl">
                        <AiImageNewSkill :from="newSkillFrom" :built-in="builtInSkills" @saved="savedNewSkill" @cancel="cancelNewSkill" />
                    </div>

                    <!-- While you're painting what to erase, Erase takes the skills' place. It's always shown (painting doesn't need the input
                         focused, and leaves focus there). Pressing it, or Enter in the input, erases what's painted, or what's described. -->
                    <div v-if="brushing" class="absolute -end-px bottom-full mb-3 flex justify-end">
                        <button type="button" :class="skillPill" @mousedown.prevent @click="send">
                            <span class="st-text-trim-optical-cap">{{ __('Erase') }}</span>
                        </button>
                    </div>

                    <!-- Cancel sits at the end of the input, where the chat has Clear (1px further in than it was inside the card, for the card's
                         border). It's out here, after the new images, the skills and Erase, so it's the last stop for Tab: while you're painting,
                         Tab goes from the input to Erase, then Cancel. -->
                    <div v-if="cancellable" class="absolute end-[calc(var(--spacing)*2+1px)] bottom-[13.5px] flex items-center">
                        <!-- Save as skill, for a typed request that's come back, before Cancel, which stays the last stop for Tab -->
                        <button v-if="canSaveSkill" ref="saveSkillLink" type="button" :class="cancelLink" :aria-expanded="!!newSkillFrom" @mousedown.prevent @click="startNewSkill">{{ __('Save as skill') }}</button>
                        <button type="button" :class="cancelLink" @mousedown.prevent @click="dismiss">{{ __('Cancel') }}</button>
                    </div>
                </div>
            </template>
        </div>
    </div>
</template>
