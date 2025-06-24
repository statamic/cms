<script setup>
import { cva } from 'cva';
import { hasComponent } from '@statamic/composables/has-component.js';
import { DialogContent, DialogOverlay, DialogPortal, DialogRoot, DialogTitle, DialogTrigger } from 'reka-ui';
import { getCurrentInstance, ref, watch } from 'vue';

const emit = defineEmits(['update:open']);

const props = defineProps({
    title: { type: String, default: '' },
    open: { type: Boolean, default: false },
});

const hasModalTitleComponent = hasComponent('ModalTitle');

const modalClasses = cva({
    base: [
        'fixed outline-hidden left-1/2 top-1/6 z-50 w-full max-w-2xl -translate-x-1/2',
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

const instance = getCurrentInstance();
const isUsingOpenProp = instance && 'open' in instance.vnode.props;

const open = ref(props.open);

watch(
    () => props.open,
    (value) => open.value = value,
);

// When the parent component controls the open state, emit an update event
// so it can update its state, which eventually gets passed down as a prop.
// Otherwise, just update the local state.
function updateOpen(value) {
    if (isUsingOpenProp) {
        emit('update:open', value);
        return;
    }

    open.value = value;
}
</script>

<template>
    <DialogRoot :open @update:open="updateOpen">
        <DialogTrigger data-ui-modal-trigger>
            <slot name="trigger" />
        </DialogTrigger>
        <DialogPortal>
            <DialogOverlay
                class="data-[state=open]:show fixed inset-0 z-30 bg-gray-800/20 backdrop-blur-[2px] dark:bg-gray-800/50"
            />
            <DialogContent :class="[modalClasses, $attrs.class]" data-ui-modal-content :aria-describedby="undefined">
                <div
                    class="relative space-y-3 rounded-xl border border-gray-400/60 bg-white p-4 shadow-[0_1px_16px_-2px_rgba(63,63,71,0.2)] dark:border-none dark:bg-gray-800 dark:shadow-[0_10px_15px_rgba(0,0,0,.5)] dark:inset-shadow-2xs dark:inset-shadow-white/15"
                >
                    <DialogTitle v-if="!hasModalTitleComponent" data-ui-modal-title class="font-medium">
                        {{ title }}
                    </DialogTitle>
                    <slot />
                </div>
                <slot name="footer" />
            </DialogContent>
        </DialogPortal>
    </DialogRoot>
</template>
