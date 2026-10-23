<script setup>
defineProps({
    /** Saved prompts from Settings → AI (a name, its command and instructions), plus any built-in commands like /clear. */
    prompts: { type: Array, required: true },
    /** The one Enter would run, while typing a command. -1 for none. */
    active: { type: Number, default: -1 },
    /** Shown below another list in the same box, so it needs less padding above. */
    afterList: { type: Boolean, default: false },
});

defineEmits(['pick']);
</script>

<template>
    <!-- Laid out like the keyboard shortcuts: a heading, then rows in one white card with a ring -->
    <div :class="['space-y-2 px-2 pb-2', afterList ? 'pt-3.5' : 'pt-2']">
        <p class="px-1 text-sm tracking-tight text-gray-600 dark:text-gray-400">{{ __('Saved prompts') }}</p>

        <div role="listbox" :aria-label="__('Saved prompts')" class="divide-y divide-gray-200 overflow-hidden rounded-xl bg-white ring ring-gray-200 dark:divide-gray-700/80 dark:bg-gray-850 dark:ring-gray-700/80">
            <!-- mousedown.prevent keeps focus in the input, so picking one doesn't hide the menu before the click lands -->
            <button
                v-for="(prompt, index) in prompts"
                :key="prompt.command"
                type="button"
                role="option"
                :aria-selected="index === active"
                :class="[
                    'relative flex w-full cursor-pointer items-start gap-3 py-2 pe-3 text-start transition-colors focus-visible:focus-outline',
                    index === active ? 'bg-gray-50 dark:bg-gray-800/60' : 'hover:bg-gray-50 dark:hover:bg-gray-800',
                    // While one is highlighted, every row makes room for its dot, so the names line up and don't move as the highlight does
                    active >= 0 ? 'ps-7' : 'ps-3',
                ]"
                @mousedown.prevent
                @click="$emit('pick', prompt)"
            >
                <!-- The highlighted one (the one Enter runs) gets a purple dot with a soft halo, in the gutter to the left of its name and level with it -->
                <span v-if="index === active" aria-hidden="true" class="absolute start-3 top-[18px] size-1.5 -translate-y-1/2 rounded-full bg-primary ring-3 ring-primary/15" />
                <span class="min-w-0 flex-1">
                    <span class="block text-sm/5 text-gray-900 dark:text-gray-200">{{ prompt.name }}</span>
                    <span v-if="prompt.instructions" class="block truncate text-xs/5 text-gray-500 dark:text-gray-400">{{ prompt.instructions }}</span>
                </span>
                <!-- The command in a key, like the keyboard shortcuts, as it's something you type -->
                <span class="inline-flex h-5 shrink-0 items-center rounded-md bg-gray-100 px-1.5 text-[0.6875rem] font-medium text-gray-600 ring-1 ring-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:ring-gray-700">/{{ prompt.command }}</span>
            </button>
        </div>
    </div>
</template>
