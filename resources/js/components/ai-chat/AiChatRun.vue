<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { Badge, Icon } from '@ui';
import AiChatDoneIcon from './AiChatDoneIcon.vue';
import AiChatPageMarker from './AiChatPageMarker.vue';

const props = defineProps({
    // From mock-runs.js: a title, and a list of tool calls and their state
    run: { type: Object, required: true },
});

const badge = computed(() =>
    ({
        working: { color: 'blue', text: __('Working') },
        // A pointer clicking: it's your move
        waiting: { color: 'default', text: __('Needs you'), icon: 'cursor-click' },
        done: { color: 'emerald', text: __('Done') },
        cancelled: { color: 'default', text: __('Cancelled') },
        // It couldn't carry on, like when saving your changes failed
        stopped: { color: 'default', text: __('Stopped') },
    })[props.run.status],
);

// Each tool call in words: what it's doing while it runs (or waits to), and what it did once it's done. The tool's own name is in the tooltip.
const toolLabels = {
    'statamic.search_entries': ['Searching Entries', 'Searched Entries'],
    'statamic.get_entry': ['Reading the Entry', 'Read the Entry'],
    'statamic.get_blueprint': ['Reading the Blueprint', 'Read the Blueprint'],
    'statamic.get_assets': ['Looking at Assets', 'Looked at Assets'],
    'statamic.create_entry': ['Creating an Entry', 'Created an Entry'],
    'statamic.update_entry': ['Updating the Entry', 'Updated the Entry'],
    'cp.open_entry': ['Opening the Entry', 'Opened the Entry'],
    'cp.fill_fields': ['Filling in Fields', 'Filled in Fields'],
};

// The cards double as the trail of pages the chat has taken you to. Each knows the page it happened on. A line runs down through the
// cards' ticks (showing in the gaps between them, as if behind the cards), with a marker on it above the first card and wherever the
// page changes. Any page you're not on is a link back to it.
const inertiaPage = usePage();
const isHere = (p) => p?.url === inertiaPage.url.split('?')[0];
const pageChangesAt = (i) => i === 0 || props.run.steps[i].page?.url !== props.run.steps[i - 1].page?.url;

// The bit of line in the gap above each card: a soft AI colour on the page you're on, and dashed grey on pages you've left behind
const line = (step) =>
    isHere(step.page) ? 'before:w-px before:bg-ai/30' : 'before:border-s before:border-dashed before:border-gray-300 dark:before:border-gray-600';

const stepTitle = (step) => {
    const labels = toolLabels[step.tool];
    return labels ? __(labels[step.state === 'done' ? 1 : 0]) : step.tool;
};
</script>

<template>
    <div class="space-y-3">
        <div class="flex items-center gap-2 px-1">
            <p class="text-sm font-medium tracking-tight text-gray-700 dark:text-white">{{ run.title }}</p>
            <Badge pill v-bind="badge" />
        </div>

        <!-- One card per tool call, like the CP's Card. They go from pending, to running, to done. A marker for the page they happened on sits above the first, and wherever the page changes. -->
        <template v-for="(step, i) in run.steps" :key="step.tool">
            <AiChatPageMarker
                v-if="step.page && pageChangesAt(i)"
                :page="step.page"
                :previous="i ? run.steps[i - 1].page : null"
            />
            <div
                :class="[
                    'relative flex items-start gap-2.5 rounded-xl bg-white p-3.5 shadow-ui-sm ring ring-gray-200 before:absolute before:start-[21.5px] before:-top-3 before:h-3 dark:bg-gray-850 dark:shadow-ui-md dark:ring-gray-700/80',
                    // Waiting is faded like the steps still to come, as the question itself is in the input
                    ['pending', 'waiting', 'skipped'].includes(step.state) ? 'opacity-60' : '',
                    line(step),
                    // Each card only appears once its step starts, opening up from nothing
                    'ai-card-in',
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
                'relative flex items-start gap-2.5 rounded-xl bg-white p-3.5 shadow-ui-sm ring ring-gray-200 before:absolute before:start-[21.5px] before:-top-3 before:h-3 dark:bg-gray-850 dark:shadow-ui-md dark:ring-gray-700/80',
                line(run.steps.findLast((step) => step.page) ?? {}),
                // And the outcome comes in the same way once it's done
                'ai-card-in',
            ]"
        >
            <div class="mt-0.5 flex size-4 shrink-0 items-center justify-center">
                <Icon v-if="run.status !== 'done'" name="x" class="size-3.5 text-gray-500 dark:text-gray-400" />
                <AiChatDoneIcon v-else />
            </div>
            <div class="min-w-0 flex-1 space-y-1.5">
                <p class="text-[0.8125rem] font-semibold text-gray-900 dark:text-gray-200">{{ run.done.text }}</p>
                <!-- What it changed, or found (for jobs that check, like a saved legal check). The bullets hang out to the left, in the gap by the tick, so the names line up with the title. -->
                <ul v-if="run.done.items" class="list-disc space-y-1 ps-0 text-xs/5 text-gray-700 dark:text-gray-300">
                    <li v-for="item in run.done.items" :key="item">{{ item }}</li>
                </ul>
                <p v-if="run.done.note" class="text-xs text-gray-500 dark:text-gray-400">{{ run.done.note }}</p>
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
