<script setup>
import CommandPalette from '@/components/command-palette/CommandPalette.vue';
import { Icon } from '@ui';

const isMac = typeof navigator !== 'undefined' && /Mac|iPhone|iPad/.test(navigator.platform);
const modifierLabel = isMac ? '⌘' : 'Ctrl';
</script>

<template>
    <CommandPalette>
        <div v-tooltip="`${__('Search')} (${modifierLabel} K)`" class="
            data-[focus-visible]:outline-focus hover flex cursor-pointer justify-center items-center gap-x-1.5 group h-8
            rounded-xl [button:has(>&)]:rounded-xl bg-black/40 text-xs text-white/60 outline-none
            ring-1 ring-white/10
            w-8
            hover:bg-black/45 hover:text-white/70
        ">
            <Icon name="magnifying-glass" class="size-5 flex-none text-white/50 group-hover:text-white/70" />
            <!-- Icon only, to keep the header calm. The tooltip names it and gives the shortcut. -->
            <span class="sr-only">{{ __('Search') }}</span>
        </div>
    </CommandPalette>
</template>
