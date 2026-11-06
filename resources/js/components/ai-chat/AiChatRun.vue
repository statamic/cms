<script setup>
import { computed, nextTick, onBeforeUnmount, ref, useTemplateRef, watch } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { Badge, Button, Icon, Textarea } from '@ui';
import diffWords from '@/components/ai-suggest/diff-words.js';
import AiChatDoneIcon from './AiChatDoneIcon.vue';
import AiChatPageMarker from './AiChatPageMarker.vue';
import { linkTooltip, playRun } from './mock-runs.js';

const props = defineProps({
    // From mock-runs.js: a title, and a list of tool calls and their state
    run: { type: Object, required: true },
});

// A search that found nothing: its result is grey rather than green, and the badge says so, as "Done" would sound like it found something
const isEmptyRun = computed(() => !!props.run.done?.empty);

// A URL in a list of changes is written between backticks (like `/returns`) and shown as a small monospace chip, as it's code rather than a name
const urlParts = (item) => item.split('`').map((text, i) => ({ text, url: i % 2 === 1 }));

// How it ended, shown at the bottom of the outcome card, under what it did. There's no badge while it's going: the cards show that.
const badge = computed(() =>
    ({
        done: isEmptyRun.value ? { color: 'default', text: __('Nothing found') } : { color: 'emerald', text: __('Done') },
        cancelled: { color: 'default', text: __('Cancelled') },
        // It couldn't carry on, like when saving your changes failed
        stopped: { color: 'default', text: __('Stopped') },
        // Your role doesn't allow it, like changing a blueprint
        unable: { color: 'default', text: __('Not allowed') },
        // The AI didn't respond
        failed: { color: 'default', text: __('Failed') },
    })[props.run.status],
);

// A run that was blocked (your role doesn't allow it, like changing a blueprint) has a red × in a ring, the counterpart of the tick in a ring on a run
// that's done. Its badge and heading stay as they are: the icon is enough to say you've been stopped.
// A run that failed (the AI didn't respond) has the same red cross.
const blocked = computed(() => ['unable', 'failed'].includes(props.run.status));

// Each tool call in words: what it's doing while it runs (or waits to), and what it did once it's done. The tool's own name is in the tooltip.
const toolLabels = {
    'statamic.search_entries': ['Searching Entries', 'Searched Entries'],
    'statamic.get_entry': ['Reading the Entry', 'Read the Entry'],
    'statamic.get_entries': ['Reading the Entries', 'Read the Entries'],
    'statamic.get_blueprint': ['Reading the Blueprint', 'Read the Blueprint'],
    'statamic.get_assets': ['Looking at Assets', 'Looked at Assets'],
    'cp.open_navigation': ['Opening a Navigation', 'Opened a Navigation'],
    'cp.add_link': ['Adding a Link', 'Added a Link'],
    'statamic.get_navigation': ['Reading the Navigation', 'Read the Navigation'],
    'statamic.create_entry': ['Creating an Entry', 'Created an Entry'],
    'statamic.update_entry': ['Updating the Entry', 'Updated the Entry'],
    'statamic.publish_entry': ['Scheduling the Entry', 'Scheduled the Entry'],
    'statamic.localize_entry': ['Translating the Entry', 'Translated the Entry'],
    'statamic.describe_images': ['Describing the Images', 'Described the Images'],
    'statamic.get_submissions': ['Reading Submissions', 'Read Submissions'],
    'cp.open_entry': ['Opening the Entry', 'Opened the Entry'],
    'cp.new_entry': ['Opening a New Entry', 'Opened a New Entry'],
    'cp.open_global': ['Opening a Global Set', 'Opened a Global Set'],
    'cp.open_term': ['Opening a Term', 'Opened a Term'],
    'statamic.get_term': ['Reading the Term', 'Read the Term'],
    'statamic.get_global_set': ['Reading the Global Set', 'Read the Global Set'],
    'cp.fill_fields': ['Filling in Fields', 'Filled in Fields'],
    'cp.open_blueprint': ['Opening the Blueprint', 'Opened the Blueprint'],
    'cp.add_field': ['Adding a Field', 'Added a Field'],
};

// The cards double as the trail of pages the chat has taken you to. Each knows the page it happened on. A line runs down through the
// cards' ticks (showing in the gaps between them, as if behind the cards), with a marker on it above the first card and wherever the
// page changes. Any page you're not on is a link back to it.
const inertiaPage = usePage();
const isHere = (p) => p?.url === inertiaPage.url.split('?')[0];
// The trail (the line, dots and page names) only shows for a run that takes you somewhere: one that's been on more than one page, or that's
// going to open a page. On a run that stays put, the cards stand on their own.
const moves = computed(
    () => new Set(props.run.steps.filter((step) => step.page).map((step) => step.page.url)).size > 1 || props.run.steps.some((step) => ['cp.open_entry', 'cp.new_entry', 'cp.open_global', 'cp.open_term', 'cp.open_navigation'].includes(step.tool)),
);
const pageChangesAt = (i) => i === 0 || props.run.steps[i].page?.url !== props.run.steps[i - 1].page?.url;
// The latest page in the trail, while the run is still going, so its dot can stay purple until the next page's takes over
const latestPageAt = computed(() =>
    ['working', 'waiting'].includes(props.run.status) ? props.run.steps.findLastIndex((step, i) => step.page && pageChangesAt(i)) : -1,
);

// The bit of line in the gap above each card: a soft AI colour on the page you're on, and dashed grey on pages you've left behind
// It sits underneath everything, so the trail's dot passes over it (and under the cards) as it travels down to a new page (see AiChatPageMarker.vue)
const line = (step) =>
    !moves.value
        ? ''
        : isHere(step.page) ? 'before:w-px before:bg-ai/30' : 'before:border-s before:border-dashed before:border-gray-300 dark:before:border-gray-600';

// Cards open up as they first appear in a run that's playing, but not when the chat is drawn again later (opened, docked, or after a page change)
// Once one of those has played, take its class off. The floating chat is only hidden while it's closed, and showing it again would play
// anything still marked to animate all over again. The line the trail's dot draws has faded by then, so it goes for good.
function settle(event) {
    const el = event.target;
    if (event.animationName === 'ai-card-in') el.classList.remove('ai-card-in');
    if (event.animationName === 'ai-trail-drop') el.classList.remove('ai-trail-drop');
    if (event.animationName === 'ai-trail-line-fade') el.style.display = 'none';
}
const justNow = (time) => time && Date.now() - time < 1000;

// What it changed, word by word, in the same yellow and red as the suggestions and the Changed by AI badge
// Where words were swapped, the old one comes first, then the new one
const diffParts = computed(() => {
    if (!props.run.done?.diff) return [];
    const parts = diffWords(props.run.done.diff.before, props.run.done.diff.after);
    for (let i = 1; i < parts.length; i++) {
        if (parts[i].type === 'removed' && parts[i - 1].type === 'added') [parts[i - 1], parts[i]] = [parts[i], parts[i - 1]];
    }
    return parts;
});
const diffClass = (type) => ({
    'rounded-sm bg-yellow-100 dark:bg-transparent dark:text-yellow-300': type === 'added',
    'rounded-sm bg-red-50 text-red-700 line-through dark:bg-transparent dark:text-red-400': type === 'removed',
});

// The buttons under an outcome: open a page in the CP, or (for previews) the site in a new tab
// Try again plays the run over (the mock fails every time)
function retry() {
    props.run.status = 'working';
    props.run.endedAt = null;
    props.run.steps.forEach((step) => ((step.state = 'pending'), (step.shownAt = null)));
    playRun(props.run);
}
// A step that's been running for 6 seconds or more says it's still going, and for how long. A clock ticks each second to keep it up to date.
// (Prototype: the "Chat slow" demo has a step that takes 16.)
const now = ref(Date.now());
const clock = setInterval(() => (now.value = Date.now()), 1000);
onBeforeUnmount(() => clearInterval(clock));
const slowFor = (step) => (step.state === 'running' && step.shownAt && now.value - step.shownAt >= 6000 ? Math.floor((now.value - step.shownAt) / 1000) : 0);

const follow = (action) => (action.retry ? retry() : action.newTab ? window.open(action.url(), '_blank') : router.visit(action.url()));

// The alt text review goes one image at a time: a big image, its suggestion in a box to edit, then Apply and next, or Skip. A strip of
// all the thumbnails under it shows how far you are, and you can click one to go back to it, even once they're all done: an applied one
// can be edited and updated, or undone, and a skipped one can be applied after all. (Prototype: applying just marks it applied.)
const images = computed(() => props.run.done?.images ?? []);
// The one showing. Applying or skipping moves on to the next one not dealt with (or to the end, once they all are).
const altIndex = ref(0);
const altImage = computed(() => images.value[altIndex.value]);
const altFinished = computed(() => images.value.length && images.value.every((image) => image.applied || image.skipped));
function nextAlt() {
    const after = images.value.findIndex((image, i) => i > altIndex.value && !image.applied && !image.skipped);
    const any = images.value.findIndex((image) => !image.applied && !image.skipped);
    altIndex.value = after >= 0 ? after : any >= 0 ? any : altIndex.value;
}
// Once they're all done the review closes up to the strip, and clicking a thumbnail opens it again on that image (and again, or Update, closes it)
const altOpen = ref(false);
const showReview = computed(() => !altFinished.value || altOpen.value);
function pickAlt(i) {
    if (altFinished.value && altOpen.value && altIndex.value === i) return (altOpen.value = false);
    altIndex.value = i;
    altOpen.value = true;
}
// When the review first appears (the run has just finished), focus lands on Apply and next, so Enter takes the suggestion and moves on.
// This is only for a run that finishes while you watch, not for a chat that's drawn again. The button has the CP's usual focus ring on any focus (focus-outline),
// as it only shows for the keyboard otherwise, and not for focus that lands by itself after a click.
const applyButton = useTemplateRef('applyButton');
watch(
    () => props.run.status,
    (status) => status === 'done' && props.run.done?.images && nextTick(() => applyButton.value?.$el?.focus({ preventScroll: true })),
);
// After dealing with one: on to the next not dealt with, or, if that was the last, close the review up
function settleAlt() {
    if (altFinished.value) altOpen.value = false;
    else nextAlt();
}
const applyAlt = () => ((altImage.value.applied = true), (altImage.value.skipped = false), settleAlt());
const skipAlt = () => ((altImage.value.skipped = true), settleAlt());
// Changed your mind about one you applied: skip it after all
const skipApplied = () => ((altImage.value.applied = false), skipAlt());
// Accepts the suggestions for all the ones not dealt with yet, as they are (with any edits you've made)
const acceptAllAlt = () => images.value.forEach((image) => image.applied || image.skipped || (image.applied = true));

// The current thumbnail has a 2px border in the AI colour (so it follows the theme); the others have a 1px grey one. They're borders rather
// than rings, as a ring is a box-shadow, and the thumbnails have no shadow of any kind.
const altRing = (i) => (i === altIndex.value ? 'border-2 border-ai' : 'border border-gray-200 dark:border-gray-700');

// An applied thumbnail gets a small white badge with a green tick, top right (15px, with a 9px tick), and its image stays at full strength.
// Skipped ones are just faded.

// Once they're all done, the review says nothing more: the card's heading changes to say what was added, and the strip of thumbnails stays
const altApplied = computed(() => images.value.filter((image) => image.applied).length);
const headline = computed(() => {
    if (!props.run.done?.images || !altFinished.value) return props.run.done?.text;
    if (!altApplied.value) return __('No alt text added');
    return altApplied.value === 1 ? __('Alt text added to 1 image') : __('Alt text added to :count images', { count: altApplied.value });
});

const altLeft = computed(() => images.value.filter((image) => !image.applied && !image.skipped).length);

// A search step has a magnifying glass rather than the spinner and tick: blue and pulsing while it runs, green once it's done. It's 18px, as the glass has space around it in its box and would look small at the tick's 16px.
const isSearch = (step) => step.tool === 'statamic.search_entries';
const showSearchIcon = (step) => isSearch(step) && ['running', 'done'].includes(step.state);
const searchIconClass = (step) => (step.state === 'running' ? 'text-blue-500 motion-safe:animate-pulse' : 'text-emerald-700 dark:text-emerald-400');

const stepTitle = (step) => {
    const labels = toolLabels[step.tool];
    return labels ? __(labels[step.state === 'done' ? 1 : 0]) : step.tool;
};
</script>

<template>
    <!-- isolate: the trail's line and travelling dot sit underneath the cards (with negative z-indexes), and this keeps them above whatever the chat sits on -->
    <div class="isolate space-y-3" @animationend="settle">
        <!-- No title or status: the request above says what the job is, and the cards' spinners and ticks show how it's going -->
        <!-- One card per tool call, like the CP's Card. They go from pending, to running, to done. A marker for the page they happened on sits above the first, and wherever the page changes. -->
        <template v-for="(step, i) in run.steps" :key="step.tool">
            <AiChatPageMarker
                v-if="moves && step.page && pageChangesAt(i)"
                :page="step.page"
                :previous="i ? run.steps[i - 1].page : null"
                :arriving="justNow(step.pageAt ?? step.shownAt)"
                :latest="i === latestPageAt"
            />
            <div
                :class="[
                    'relative flex items-start gap-2.5 rounded-xl bg-white p-3.5 shadow-ui-sm ring ring-gray-200 before:absolute before:-z-20 before:start-[21.5px] before:-top-3 before:h-3 dark:bg-gray-850 dark:shadow-ui-md dark:ring-gray-700/80',
                    // Waiting is faded like the steps still to come, as the question itself is in the input
                    ['pending', 'waiting', 'skipped'].includes(step.state) ? 'opacity-60' : '',
                    line(step),
                    // Each card only appears once its step starts, opening up from nothing as it does
                    justNow(step.shownAt) ? 'ai-card-in' : '',
                    step.state === 'pending' ? 'hidden' : '',
                ]"
            >
                <div class="mt-0.5 flex size-4 shrink-0 items-center justify-center">
                    <Icon v-if="showSearchIcon(step)" name="magnifying-glass" class="size-[18px]! shrink-0" :class="searchIconClass(step)" />
                    <Icon v-else-if="step.state === 'running'" name="loading" class="size-4 text-blue-500" />
                    <AiChatDoneIcon v-else-if="step.state === 'done'" />
                    <!-- Waiting for you: a dot -->
                    <span v-else-if="step.state === 'waiting'" class="size-2 rounded-full bg-gray-500" />
                    <svg v-else-if="step.state === 'failed'" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="size-4 text-red-600 dark:text-red-400" aria-hidden="true">
                        <circle cx="8" cy="8" r="6.75" />
                        <path d="m6 6 4 4m0-4-4 4" />
                    </svg>
                    <span v-else class="size-4 rounded-full border border-dashed border-gray-300 dark:border-gray-600" />
                </div>

                <div class="min-w-0 flex-1 space-y-1">
                    <p class="truncate text-[0.8125rem] font-semibold text-gray-900 dark:text-gray-200" :title="step.tool">{{ stepTitle(step) }}</p>
                    <!-- What it worked on, in words. The arguments it was really called with are in the tooltip. -->
                    <p class="text-xs text-gray-500 dark:text-gray-400" :title="step.args">{{ step.detail ?? step.args }}</p>
                    <p v-if="step.state === 'done'" class="text-xs font-medium" :class="isEmptyRun ? 'text-gray-500 dark:text-gray-400' : 'text-emerald-700 dark:text-emerald-400'">{{ step.result }}</p>
                    <p v-else-if="step.state === 'running' && slowFor(step)" class="text-xs text-gray-500 tabular-nums dark:text-gray-400">{{ __('Still running…') }} {{ slowFor(step) }}s</p>
                    <p v-else-if="step.state === 'running'" class="text-xs text-gray-500 dark:text-gray-400">{{ __('Running…') }}</p>
                    <p v-else-if="step.state === 'skipped'" class="text-xs text-gray-500 dark:text-gray-400">{{ __('Skipped') }}</p>
                    <p v-else-if="step.state === 'failed'" class="text-xs font-medium text-red-700 dark:text-red-400">{{ __('No response') }}</p>
                    <p v-else-if="step.state === 'waiting'" class="text-xs text-gray-700 dark:text-gray-300">{{ __('Waiting for you') }}</p>
                </div>
            </div>
        </template>

        <!-- The outcome, built like the tool-call cards above it: a tick (or a cross, if it was cancelled or stopped) and the same spacing -->
        <div
            v-if="['done', 'cancelled', 'stopped', 'unable', 'failed'].includes(run.status)"
            :class="[
                // In from the line like the cards above, which carries on down beside it, for the page it finished on
                'relative flex items-start gap-2.5 rounded-xl bg-white p-3.5 shadow-ui-sm ring ring-gray-200 before:absolute before:-z-20 before:start-[21.5px] before:-top-3 before:h-3 dark:bg-gray-850 dark:shadow-ui-md dark:ring-gray-700/80',
                line(run.steps.findLast((step) => step.page) ?? {}),
                // And the outcome comes in the same way once it's done
                justNow(run.endedAt) ? 'ai-card-in' : '',
            ]"
        >
            <div class="mt-0.5 flex size-4 shrink-0 items-center justify-center">
                <svg v-if="blocked" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="size-4 text-red-600 dark:text-red-400" aria-hidden="true">
                    <circle cx="8" cy="8" r="6.75" />
                    <path d="m6 6 4 4m0-4-4 4" />
                </svg>
                <!-- A search that found nothing gets a grey cross, not a tick, which would suggest it found something -->
                <Icon v-else-if="run.status !== 'done' || run.done.empty" name="x" class="size-3.5 text-gray-500 dark:text-gray-400" />
                <AiChatDoneIcon v-else />
            </div>
            <div class="min-w-0 flex-1 space-y-1.5">
                <!-- The summary can run to two lines, so it's set tighter than the body text (18px lines rather than 19.5px) -->
                <p class="text-[0.8125rem]/[1.375] font-semibold text-gray-900 dark:text-gray-200">{{ headline }}</p>
                <!-- What it changed, or found (for jobs that check, like a saved legal check). The bullets hang out to the left, in the gap by the tick, so the names line up with the title. -->
                <ul v-if="run.done.items" class="list-disc space-y-1 ps-0 text-xs/5 text-gray-700 dark:text-gray-300">
                    <li v-for="item in run.done.items" :key="item">
                        <template v-for="(part, p) in urlParts(item)" :key="p">
                            <span v-if="part.url" class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-2xs text-gray-700 dark:bg-white/10 dark:text-gray-300">{{ part.text }}</span>
                            <template v-else>{{ part.text }}</template>
                        </template>
                    </li>
                </ul>
                <!-- What it found, for searches: each entry's name, a link to open it, with the line that matched in its tooltip -->
                <ul v-if="run.done.results" class="space-y-1 pt-0.5 text-xs/5">
                    <li v-for="result in run.done.results" :key="result.title" class="flex items-center gap-1.5">
                        <Icon name="entry" class="size-3.5! text-gray-400" />
                        <button type="button" :title="result.text" class="cursor-pointer font-medium text-gray-900 underline decoration-gray-300 underline-offset-2 hover:decoration-gray-900 dark:text-white dark:decoration-gray-600 dark:hover:decoration-white" @click="router.visit(result.url())">
                            {{ result.title }}
                        </button>
                    </li>
                </ul>
                <!-- The links it added: the words that are now links, in blue and underlined like links, so you can spot them in the field. Hover one to see the page it goes to, and its URL, which is also beside it in grey. -->
                <ul v-if="run.done.links" class="list-disc space-y-1 ps-0 text-xs/5 text-gray-700 dark:text-gray-300">
                    <li v-for="link in run.done.links" :key="link.text">
                        <span v-tooltip="linkTooltip(link)" class="cursor-default text-blue-600 underline decoration-blue-600/50 underline-offset-2 dark:text-blue-400 dark:decoration-blue-400/50">{{ link.text }}</span>
                        <span class="ms-1.5 text-gray-500 dark:text-gray-400">{{ link.url }}</span>
                    </li>
                </ul>
                <!-- What it changed, word by word: the field, then the sentence with the change marked -->
                <div v-if="run.done.diff" class="space-y-0.5 pt-0.5 text-xs/5">
                    <p class="text-gray-500 dark:text-gray-400">{{ run.done.diff.label }}</p>
                    <p class="text-gray-700 dark:text-gray-300">
                        <template v-for="(part, i) in diffParts" :key="i"><span :class="diffClass(part.type)">{{ part.text }}</span>{{ ' ' }}</template>
                    </p>
                </div>
                <!-- Images to review, one at a time: each one's image and suggested alt text, which you can edit, to apply or skip -->
                <!-- It runs the full width of the card, out under the tick (16px, plus the 10px gap), rather than indented like the text -->
                <div v-if="run.done.images" class="@container -ms-6.5 space-y-2 pt-1">
                    <template v-if="showReview">
                        <img :src="altImage.src" alt="" class="aspect-[4/3] w-full rounded-lg bg-gray-100 object-cover dark:bg-gray-800" />
                        <!-- One you've been back to says what happened to it -->
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ altImage.file }}<template v-if="altImage.applied"> · {{ __('Applied') }}</template><template v-else-if="altImage.skipped"> · {{ __('Skipped') }}</template></p>
                        <!-- Always editable, even once it's applied or skipped -->
                        <Textarea v-model="altImage.alt" rows="2" resize="none" class="px-2! py-1.5! text-xs!" :aria-label="__('Alt text for :file', { file: altImage.file })" />
                        <!-- The buttons wrap onto a second row when the chat is docked narrow, rather than running out of the card -->
                        <div class="flex flex-wrap gap-1.5">
                            <!-- One you applied: save your edit, or skip it after all -->
                            <template v-if="altImage.applied">
                                <Button size="sm" variant="primary" :text="__('Update')" @click="settleAlt" />
                                <Button size="sm" variant="ghost" :text="__('Skip')" @click="skipApplied" />
                            </template>
                            <!-- One you skipped: apply it after all -->
                            <Button v-else-if="altImage.skipped" size="sm" variant="primary" :text="__('Apply')" @click="applyAlt" />
                            <template v-else>
                                <!-- "Apply and next" when the card is wide enough for all three buttons on one row (about 270px), and just "Apply" when it isn't, as when the chat is docked narrow -->
                                <Button ref="applyButton" size="sm" variant="primary" class="focus:focus-outline" @click="applyAlt">
                                    <span class="@min-[270px]:hidden">{{ __('Apply') }}</span>
                                    <span class="hidden @min-[270px]:inline">{{ __('Apply and next') }}</span>
                                </Button>
                                <Button size="sm" variant="ghost" :text="__('Skip')" @click="skipAlt" />
                                <!-- At the far end, for when the suggestions look right without going through each -->
                                <Button v-if="altLeft > 1" size="sm" class="ms-auto" :text="__('Accept all')" @click="acceptAllAlt" />
                            </template>
                        </div>
                    </template>
                    <!-- All the thumbnails, 40px and 6px apart: this one ringed in the AI colour, applied ones ticked, skipped ones drained of colour. Click one to go
                         to it. While reviewing, 22px above it (the 8px between rows, and 14px more), so the strip reads as a row of its own under the buttons. They wrap onto more rows when the chat is narrow. -->
                    <div :class="['flex flex-wrap gap-1.5', altFinished ? '' : 'pt-3.5']">
                        <button v-for="(image, i) in images" :key="image.file" type="button" :class="['relative size-10 cursor-pointer overflow-hidden rounded-md', altRing(i)]" :aria-label="image.file" @click="pickAlt(i)">
                            <img :src="image.src" alt="" :class="['size-full object-cover', image.skipped ? 'opacity-60 grayscale' : '']" />
                            <span v-if="image.applied" class="absolute end-0.5 top-0.5 grid size-[15px] place-items-center rounded-full border border-emerald-700/40 bg-emerald-600">
                                <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.125" stroke-linecap="round" stroke-linejoin="round" class="size-[9px] text-white" aria-hidden="true">
                                    <path d="m3.5 8.5 3 3 6-6.5" />
                                </svg>
                            </span>
                        </button>
                    </div>
                </div>
                <p v-if="run.done.note" class="pt-2.5 text-xs text-gray-500 dark:text-gray-400">{{ run.done.note }}</p>
                <!-- What you might do next, like opening what it made -->
                <!-- What went wrong, for whoever can fix it -->
                <p v-if="run.done.reason" class="mt-2 rounded bg-gray-100 px-2 py-1 font-mono text-2xs text-gray-600 dark:bg-white/5 dark:text-gray-400">{{ run.done.reason }}</p>
                <div v-if="run.done.actions" class="flex flex-wrap gap-1.5 pt-0.5">
                    <Button v-for="action in run.done.actions" :key="action.label" size="sm" :text="__(action.label)" @click="follow(action)" />
                </div>
                <!-- Done (or Cancelled, or Stopped) signs it off, under what it did -->
                <!-- The small size with a little more room either side, and medium weight, so the pill doesn't look cramped -->
                <!-- Pulled out 1px, as its rounded end makes it look further in than the text above it -->
                <!-- Not under a review (like the alt text), which isn't done until you've been through it, and says so itself -->
                <Badge v-if="!run.done.images" pill size="sm" v-bind="badge" class="-ms-px px-2 font-medium" />
            </div>
        </div>
    </div>
</template>

<style>
/* The cards open up from nothing, pushing what's below down smoothly rather than appearing all at once. Reduced motion skips it. */
@media (prefers-reduced-motion: no-preference) {
    .ai-card-in {
        interpolate-size: allow-keywords;
        animation: ai-card-in 450ms cubic-bezier(0.22, 1, 0.36, 1) both;
    }
}

@keyframes ai-card-in {
    from {
        opacity: 0;
        height: 0;
        padding-block: 0;
        overflow: clip;
    }
}
</style>
