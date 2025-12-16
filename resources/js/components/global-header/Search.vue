<script setup>
import CommandPalette from '@/components/command-palette/CommandPalette.vue';
import { Icon } from '@ui';
</script>

<template>
    <CommandPalette>
        <div class="
            data-[focus-visible]:outline-focus hover flex cursor-pointer justify-center sm:justify-start items-center gap-x-1.5 group h-8
            rounded-xl [button:has(>&)]:rounded-md bg-black/40 text-xs text-white/60 outline-none
            ring-1 ring-white/10
            w-8 sm:w-32 sm:px-2 md:py-[calc(5/16*1rem)]
            hover:bg-black/45 hover:text-white/70
        ">
            <Icon name="magnifying-glass" class="size-5 flex-none text-white/50 group-hover:text-white/70" />
            <span class="sr-only leading-none sm:not-sr-only st-text-trim-cap">{{ __('Search') }}</span>
            <kbd class="ml-auto hidden self-center rounded-md bg-white/5 px-[0.3125rem] py-[0.0625rem] text-[0.625rem]/4 font-medium text-white/60 group-hover:text-white/70 ring-1 ring-white/7.5 [word-spacing:-0.15em] ring-inset md:block">
                <kbd class="font-sans">⌘ </kbd><kbd class="font-sans">K</kbd>
            </kbd>
        </div>
    </CommandPalette>
</template>
