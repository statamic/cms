<script setup>
import { Badge, Icon } from '@ui';

defineProps({
    // From mock-runs.js: a title, a list of tool calls and their state, and who's asking
    run: { type: Object, required: true },
});
</script>

<template>
    <div class="space-y-3">
        <div class="flex items-center justify-between gap-2 px-1">
            <p class="text-sm font-medium tracking-tight text-gray-700 dark:text-white">{{ run.title }}</p>
            <Badge pill :color="run.status === 'done' ? 'emerald' : 'blue'" :text="run.status === 'done' ? __('Done') : __('Working')" />
        </div>

        <!-- One card per tool call, like the CP's Card. They go from pending, to running, to done. -->
        <div
            v-for="step in run.steps"
            :key="step.tool"
            :class="[
                'flex items-start gap-3 rounded-xl p-3.5 ring transition-colors duration-300',
                step.state === 'done'
                    ? 'bg-emerald-50 ring-emerald-200 dark:bg-emerald-300/6 dark:ring-emerald-400/25'
                    : 'bg-white ring-gray-200 dark:bg-gray-850 dark:ring-gray-700/80',
                step.state === 'pending' ? 'opacity-60' : '',
            ]"
        >
            <div class="mt-0.5 flex size-4 shrink-0 items-center justify-center">
                <Icon v-if="step.state === 'running'" name="loading" class="size-4 text-blue-500" />
                <Icon v-else-if="step.state === 'done'" name="checkmark" class="size-4 text-green-600 dark:text-green-400" />
                <span v-else class="size-4 rounded-full border border-dashed border-gray-300 dark:border-gray-600" />
            </div>

            <div class="min-w-0 flex-1 space-y-1">
                <p class="truncate font-mono text-[0.8125rem] font-semibold text-gray-900 dark:text-gray-200">{{ step.tool }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ step.args }}</p>
                <p v-if="step.state === 'done'" class="text-xs font-medium text-emerald-700 dark:text-emerald-400">{{ step.result }}</p>
                <p v-else-if="step.state === 'running'" class="text-xs text-gray-500 dark:text-gray-400">{{ __('Running…') }}</p>
            </div>
        </div>

        <!-- The outcome, built like the tool-call cards above it: the done colours, a tick, and the same spacing -->
        <div
            v-if="run.status === 'done'"
            class="flex items-start gap-3 rounded-xl bg-emerald-50 p-3.5 ring ring-emerald-200 dark:bg-emerald-300/6 dark:ring-emerald-400/25"
        >
            <div class="mt-0.5 flex size-4 shrink-0 items-center justify-center">
                <Icon name="checkmark" class="size-4 text-green-600 dark:text-green-400" />
            </div>
            <p class="min-w-0 flex-1 text-[0.8125rem] font-semibold text-gray-900 dark:text-gray-200">{{ run.done.text }}</p>
        </div>

        <p class="px-1 text-xs leading-relaxed text-gray-500 dark:text-gray-400">
            {{ __('Permission evaluated in Statamic') }}<br />
            <span class="text-gray-700 dark:text-gray-300">{{ run.who.name }} · {{ run.who.role }} · {{ run.permission }} ✓</span>
        </p>
    </div>
</template>
