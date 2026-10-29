<script setup>
import { Icon } from '@ui';
import { moving } from './mock-runs.js';

// While the chat takes you to another page, the whole page (nav included) fades back under a soft blur, and a badge says where it's going. Once it's there, they stay over the new page for a moment, then lift together.
// The fade sits at the same level as the floating chat but comes before it, so the chat stays clear.
</script>

<template>
    <Transition
        enter-active-class="transition duration-500 ease-out motion-reduce:transition-none"
        enter-from-class="opacity-0"
        leave-active-class="transition duration-700 ease-out motion-reduce:transition-none"
        leave-to-class="opacity-0"
    >
        <div v-if="moving.title" class="pointer-events-none fixed inset-0 z-(--z-index-modal) bg-white/60 backdrop-blur-[2px] dark:bg-gray-900/60" />
    </Transition>

    <!-- A white pill with the AI-gradient edge and an AI glow that breathes while it waits: what it's doing in grey, and the entry's title in bold. It fades out with the blur. -->
    <Transition
        enter-active-class="transition duration-300 ease-out motion-reduce:transition-none"
        enter-from-class="-translate-y-2 opacity-0"
        leave-active-class="transition duration-700 ease-out motion-reduce:transition-none"
        leave-to-class="opacity-0"
    >
        <div
            v-if="moving.title"
            role="status"
            class="ai-move-glow pointer-events-none fixed start-1/2 top-1/3 z-(--z-index-modal) flex -translate-x-1/2 items-center gap-2 rounded-full border border-transparent px-3.5 py-1.5 text-sm text-gray-600 [background:linear-gradient(white,white)_padding-box,linear-gradient(135deg,var(--color-ai),var(--color-ai-end))_border-box] dark:text-gray-300 dark:[background:linear-gradient(var(--color-gray-900),var(--color-gray-900))_padding-box,linear-gradient(135deg,var(--color-ai),var(--color-ai-end))_border-box]"
        >
            <Icon name="loading" class="size-4 text-ai" />
            <span>{{ __('Taking you to') }} <strong class="font-semibold text-gray-900 dark:text-white">{{ moving.title }}</strong>…</span>
        </div>
    </Transition>
</template>

<style>
.ai-move-glow {
    box-shadow: 0 10px 40px -10px color-mix(in oklab, var(--color-ai) 30%, transparent);
    animation: ai-move-glow 1.6s ease-in-out infinite;
}

@keyframes ai-move-glow {
    50% {
        box-shadow: 0 12px 48px -8px color-mix(in oklab, var(--color-ai) 70%, transparent);
    }
}

@media (prefers-reduced-motion: reduce) {
    .ai-move-glow {
        animation: none;
    }
}
</style>
