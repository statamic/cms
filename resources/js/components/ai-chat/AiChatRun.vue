<script setup>
import { computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { Badge, Icon } from '@ui';
import AiChatDoneIcon from './AiChatDoneIcon.vue';
import AiChatPageMarker from './AiChatPageMarker.vue';

const props = defineProps({
    // From mock-runs.js: a title, and a list of tool calls and their state
    run: { type: Object, required: true },
});

// How it ended, shown at the bottom of the outcome card, under what it did. There's no badge while it's going: the cards show that.
const badge = computed(() =>
    ({
        done: { color: 'emerald', text: __('Done') },
        cancelled: { color: 'default', text: __('Cancelled') },
        // It couldn't carry on, like when saving your changes failed
        stopped: { color: 'default', text: __('Stopped') },
    })[props.run.status],
);

// The tooltip on each link it added: the page it goes to, then its URL in grey
const escape = (text) => text.replace(/[&<>"]/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' })[c]);
const linkTooltip = (link) => ({ content: `${escape(link.to)} <span class="opacity-60">· ${escape(link.url)}</span>`, html: true });

// Each tool call in words: what it's doing while it runs (or waits to), and what it did once it's done. The tool's own name is in the tooltip.
const toolLabels = {
    'statamic.search_entries': ['Searching Entries', 'Searched Entries'],
    'statamic.get_entry': ['Reading the Entry', 'Read the Entry'],
    'statamic.get_entries': ['Reading the Entries', 'Read the Entries'],
    'statamic.get_blueprint': ['Reading the Blueprint', 'Read the Blueprint'],
    'statamic.get_assets': ['Looking at Assets', 'Looked at Assets'],
    'statamic.create_entry': ['Creating an Entry', 'Created an Entry'],
    'statamic.update_entry': ['Updating the Entry', 'Updated the Entry'],
    'cp.open_entry': ['Opening the Entry', 'Opened the Entry'],
    'cp.new_entry': ['Opening a New Entry', 'Opened a New Entry'],
    'cp.fill_fields': ['Filling in Fields', 'Filled in Fields'],
};

// The cards double as the trail of pages the chat has taken you to. Each knows the page it happened on. A line runs down through the
// cards' ticks (showing in the gaps between them, as if behind the cards), with a marker on it above the first card and wherever the
// page changes. Any page you're not on is a link back to it.
const inertiaPage = usePage();
const isHere = (p) => p?.url === inertiaPage.url.split('?')[0];
// The trail (the line, dots and page names) only shows for a run that takes you somewhere: one that's been on more than one page, or that's
// going to open a page. On a run that stays put, the cards stand on their own.
const moves = computed(
    () => new Set(props.run.steps.filter((step) => step.page).map((step) => step.page.url)).size > 1 || props.run.steps.some((step) => ['cp.open_entry', 'cp.new_entry'].includes(step.tool)),
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
const justNow = (time) => time && Date.now() - time < 1000;

const stepTitle = (step) => {
    const labels = toolLabels[step.tool];
    return labels ? __(labels[step.state === 'done' ? 1 : 0]) : step.tool;
};
</script>

<template>
    <!-- isolate: the trail's line and travelling dot sit underneath the cards (with negative z-indexes), and this keeps them above whatever the chat sits on -->
    <div class="isolate space-y-3">
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
                    <Icon v-if="step.state === 'running'" name="loading" class="size-4 text-blue-500" />
                    <AiChatDoneIcon v-else-if="step.state === 'done'" />
                    <!-- Waiting for you: a dot -->
                    <span v-else-if="step.state === 'waiting'" class="size-2 rounded-full bg-gray-500" />
                    <span v-else class="size-4 rounded-full border border-dashed border-gray-300 dark:border-gray-600" />
                </div>

                <div class="min-w-0 flex-1 space-y-1">
                    <p class="truncate text-[0.8125rem] font-semibold text-gray-900 dark:text-gray-200" :title="step.tool">{{ stepTitle(step) }}</p>
                    <!-- What it worked on, in words. The arguments it was really called with are in the tooltip. -->
                    <p class="text-xs text-gray-500 dark:text-gray-400" :title="step.args">{{ step.detail ?? step.args }}</p>
                    <p v-if="step.state === 'done'" class="text-xs font-medium text-emerald-700 dark:text-emerald-400">{{ step.result }}</p>
                    <p v-else-if="step.state === 'running'" class="text-xs text-gray-500 dark:text-gray-400">{{ __('Running…') }}</p>
                    <p v-else-if="step.state === 'skipped'" class="text-xs text-gray-500 dark:text-gray-400">{{ __('Skipped') }}</p>
                    <p v-else-if="step.state === 'waiting'" class="text-xs text-gray-700 dark:text-gray-300">{{ __('Waiting for you') }}</p>
                </div>
            </div>
        </template>

        <!-- The outcome, built like the tool-call cards above it: a tick (or a cross, if it was cancelled or stopped) and the same spacing -->
        <div
            v-if="['done', 'cancelled', 'stopped'].includes(run.status)"
            :class="[
                // In from the line like the cards above, which carries on down beside it, for the page it finished on
                'relative flex items-start gap-2.5 rounded-xl bg-white p-3.5 shadow-ui-sm ring ring-gray-200 before:absolute before:-z-20 before:start-[21.5px] before:-top-3 before:h-3 dark:bg-gray-850 dark:shadow-ui-md dark:ring-gray-700/80',
                line(run.steps.findLast((step) => step.page) ?? {}),
                // And the outcome comes in the same way once it's done
                justNow(run.endedAt) ? 'ai-card-in' : '',
            ]"
        >
            <div class="mt-0.5 flex size-4 shrink-0 items-center justify-center">
                <Icon v-if="run.status !== 'done'" name="x" class="size-3.5 text-gray-500 dark:text-gray-400" />
                <AiChatDoneIcon v-else />
            </div>
            <div class="min-w-0 flex-1 space-y-1.5">
                <!-- The summary can run to two lines, so it's set tighter than the body text (18px lines rather than 19.5px) -->
                <p class="text-[0.8125rem]/[1.375] font-semibold text-gray-900 dark:text-gray-200">{{ run.done.text }}</p>
                <!-- What it changed, or found (for jobs that check, like a saved legal check). The bullets hang out to the left, in the gap by the tick, so the names line up with the title. -->
                <ul v-if="run.done.items" class="list-disc space-y-1 ps-0 text-xs/5 text-gray-700 dark:text-gray-300">
                    <li v-for="item in run.done.items" :key="item">{{ item }}</li>
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
                <!-- The links it added: the words that are now links, in blue and underlined like links, so you can spot them in the field. Hover one to see the page it goes to, and its URL. -->
                <ul v-if="run.done.links" class="list-disc space-y-1 ps-0 text-xs/5 text-gray-700 dark:text-gray-300">
                    <li v-for="link in run.done.links" :key="link.text">
                        <span v-tooltip="linkTooltip(link)" class="cursor-default text-blue-600 underline decoration-blue-600/50 underline-offset-2 dark:text-blue-400 dark:decoration-blue-400/50">{{ link.text }}</span>
                    </li>
                </ul>
                <p v-if="run.done.note" class="text-xs text-gray-500 dark:text-gray-400">{{ run.done.note }}</p>
                <!-- Done (or Cancelled, or Stopped) signs it off, under what it did -->
                <!-- The small size with a little more room either side, and medium weight, so the pill doesn't look cramped -->
                <Badge pill size="sm" v-bind="badge" class="px-2 font-medium" />
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
