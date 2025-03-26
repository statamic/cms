<script setup>
import { cva } from 'cva';
import { hasComponent } from '@statamic/composables/has-component.js';
import { DialogContent, DialogOverlay, DialogPortal, DialogRoot, DialogTitle, DialogTrigger } from 'reka-ui';

const props = defineProps({
    title: { type: String, default: '' },
});

const hasModalTitleComponent = hasComponent('ModalTitle');

const modalClasses = cva({
    base: [
        'fixed outline-hidden left-1/2 top-1/2 z-50 w-full max-w-lg -translate-x-1/2 -translate-y-1/2',
        'bg-white/80 dark:bg-gray-850 backdrop-blur-[2px] rounded-2xl p-2',
        'shadow-[0_8px_5px_-6px_rgba(0,0,0,0.12),_0_3px_8px_0_rgba(0,0,0,0.02),_0_30px_22px_-22px_rgba(39,39,42,0.35)]',
        'dark:shadow-[0_5px_20px_rgba(0,0,0,.5)]',
        'duration-200 will-change-[transform,opacity]',
        'data-[state=open]:animate-in data-[state=closed]:animate-out',
        'data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0',
        'data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95',
        'slide-in-from-top-2',
    ],
})({});
</script>

<template>
    <DialogRoot>
        <DialogTrigger data-ui-modal-trigger>
            <slot name="trigger" />
        </DialogTrigger>
        <DialogPortal>
            <DialogOverlay
                class="data-[state=open]:show fixed inset-0 z-30 bg-gray-800/20 backdrop-blur-[2px] dark:bg-gray-800/50"
            />
            <DialogContent :class="[modalClasses, $attrs.class]" data-ui-modal-content :aria-describedby="undefined">
                <div
                    class="dark:inset-shadow-2xs dark:inset-shadow-white/15 relative space-y-3 rounded-xl border border-gray-400/60 bg-white p-4 shadow-[0_1px_16px_-2px_rgba(63,63,71,0.2)] dark:border-none dark:bg-gray-800 dark:shadow-[0_10px_15px_rgba(0,0,0,.5)]"
                >
                    <DialogTitle v-if="!hasModalTitleComponent" data-ui-modal-title>
                        {{ title }}
                    </DialogTitle>
                    <slot />
                </div>
                <slot name="footer" />
            </DialogContent>
        </DialogPortal>
    </DialogRoot>
</template>
