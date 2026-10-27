<script setup>
import { computed } from 'vue';

const props = defineProps({
    /** Whether there are examples on screen to pick from, here or elsewhere. */
    hasExamples: { type: Boolean, default: false },
});

// Prototype: the chat's keyboard shortcuts, and the /clear command. (Alt+Enter also adds a new line, but isn't listed.) Each has the keys, then what they do.
const shortcuts = [
    { keys: ['⌘', '/'], text: 'Open or close' },
    { keys: ['/'], text: 'Show the help' },
    { keys: ['?'], text: 'Show these shortcuts' },
    { keys: ['Enter'], text: 'Send' },
    { keys: ['Shift', 'Enter'], text: 'New line' },
    { keys: ['↑'], text: 'Pick an example', or: ['↓'], needsExamples: true },
    { keys: ['↑'], text: 'Pick a saved prompt', or: ['↓'], needsExamples: false },
    { keys: ['Tab'], text: 'Show what it does', needsExamples: false },
    { keys: ['Esc'], text: 'Back to the page' },
    { keys: ['/name'], text: 'Run a saved prompt' },
    { keys: ['/clear'], text: 'Start a new chat' },
];

// While the examples are showing, up and down choose one. Otherwise they go through the saved prompts.
const visible = computed(() => shortcuts.filter((shortcut) => shortcut.needsExamples === undefined || shortcut.needsExamples === props.hasExamples));

// The arrows and the words sit about 1px above the middle of their key, so push them down. The symbols (⌘ and /) are left alone here, but the slash is drawn about 0.9px low in its key, so it's lifted separately.
const nudge = (key) => (['⌘', '/'].includes(key) ? '' : 'pt-0.5');

const keyClasses = 'inline-flex h-5 min-w-5 items-center justify-center rounded-md bg-gray-100 px-1.5 font-sans text-[0.6875rem] font-medium text-gray-600 ring-1 ring-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:ring-gray-700';
</script>

<template>
    <!-- Laid out like the examples above it: a heading, then white cards with a ring -->
    <div class="space-y-2 p-2">
        <p class="px-1 text-sm tracking-tight text-gray-600 dark:text-gray-400">{{ __('Keyboard shortcuts') }}</p>

        <dl class="divide-y divide-gray-200 rounded-xl bg-white ring ring-gray-200 dark:divide-gray-700/80 dark:bg-gray-850 dark:ring-gray-700/80">
            <div v-for="(shortcut, index) in visible" :key="index" class="flex flex-wrap items-center justify-between gap-x-3 gap-y-1.5 px-3 py-2 text-sm/5 text-gray-900 dark:text-gray-300">
                <dt class="whitespace-nowrap">{{ __(shortcut.text) }}</dt>
                <dd class="ms-auto flex shrink-0 items-center gap-1">
                    <kbd v-for="key in shortcut.keys" :key="key" :class="[keyClasses, nudge(key)]"><span :class="key === '/' ? 'inline-block -translate-y-[0.75px]' : ''">{{ key }}</span></kbd>
                    <template v-if="shortcut.or">
                        <span class="px-0.5 text-xs text-gray-500">{{ __('or') }}</span>
                        <kbd v-for="key in shortcut.or" :key="key" :class="[keyClasses, nudge(key)]">{{ key }}</kbd>
                    </template>
                </dd>
            </div>
        </dl>
    </div>
</template>
