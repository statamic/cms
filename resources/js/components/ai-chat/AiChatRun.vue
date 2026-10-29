<script setup>
import { computed } from 'vue';
import { Badge, Icon } from '@ui';
import AiChatDoneIcon from './AiChatDoneIcon.vue';

const props = defineProps({
    // From mock-runs.js: a title, a list of tool calls and their state, and who's asking
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
</script>

<template>
    <div class="space-y-3">
        <div class="flex items-center gap-2 px-1">
            <p class="text-sm font-medium tracking-tight text-gray-700 dark:text-white">{{ run.title }}</p>
            <Badge pill v-bind="badge" />
        </div>

        <!-- One card per tool call, like the CP's Card. They go from pending, to running, to done. -->
        <div
            v-for="step in run.steps"
            :key="step.tool"
            :class="[
                'flex items-start gap-2.5 rounded-xl bg-white p-3.5 shadow-ui-sm ring ring-gray-200 dark:bg-gray-850 dark:shadow-ui-md dark:ring-gray-700/80',
                // Waiting is faded like the steps still to come, as the question itself is in the input
                ['pending', 'waiting', 'skipped'].includes(step.state) ? 'opacity-60' : '',
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
                <p class="truncate font-mono text-[0.8125rem] font-semibold text-gray-900 dark:text-gray-200">{{ step.tool }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ step.args }}</p>
                <p v-if="step.state === 'done'" class="text-xs font-medium text-emerald-700 dark:text-emerald-400">{{ step.result }}</p>
                <p v-else-if="step.state === 'running'" class="text-xs text-gray-500 dark:text-gray-400">{{ __('Running…') }}</p>
                <p v-else-if="step.state === 'skipped'" class="text-xs text-gray-500 dark:text-gray-400">{{ __('Skipped') }}</p>
                <p v-else-if="step.state === 'waiting'" class="text-xs text-gray-700 dark:text-gray-300">{{ __('Waiting for you') }}</p>
            </div>
        </div>

        <!-- The outcome, built like the tool-call cards above it: a tick (or a cross, if it was cancelled or stopped) and the same spacing -->
        <div
            v-if="['done', 'cancelled', 'stopped'].includes(run.status)"
            class="flex items-start gap-2.5 rounded-xl bg-white p-3.5 shadow-ui-sm ring ring-gray-200 dark:bg-gray-850 dark:shadow-ui-md dark:ring-gray-700/80"
        >
            <div class="mt-0.5 flex size-4 shrink-0 items-center justify-center">
                <Icon v-if="run.status !== 'done'" name="x" class="size-3.5 text-gray-500 dark:text-gray-400" />
                <AiChatDoneIcon v-else />
            </div>
            <div class="min-w-0 flex-1 space-y-1.5">
                <p class="text-[0.8125rem] font-semibold text-gray-900 dark:text-gray-200">{{ run.done.text }}</p>
                <!-- What it found, for jobs that check rather than change, like a saved legal check -->
                <ul v-if="run.done.items" class="list-disc space-y-1 ps-4 text-xs/5 text-gray-700 dark:text-gray-300">
                    <li v-for="item in run.done.items" :key="item">{{ item }}</li>
                </ul>
                <p v-if="run.done.note" class="text-xs text-gray-500 dark:text-gray-400">{{ run.done.note }}</p>
            </div>
        </div>

        <p class="px-1 text-xs leading-relaxed text-gray-500 dark:text-gray-400">
            {{ __('Permission evaluated in Statamic') }}<br />
            <span class="text-gray-700 dark:text-gray-300">{{ run.who.name }} · {{ run.who.role }} · {{ run.permission }} ✓</span>
        </p>
    </div>
</template>
