<script setup>
import { cva } from 'cva';
import { hasComponent } from '@/composables/has-component.js';
import { DialogContent, DialogOverlay, DialogPortal, DialogRoot, DialogTitle, DialogTrigger } from 'reka-ui';
import { computed, getCurrentInstance, ref, watch } from 'vue';
import Icon from '../Icon/Icon.vue';

const emit = defineEmits(['update:open', 'dismissed']);

const props = defineProps({
    blur: { type: Boolean, default: true },
    title: { type: String, default: '' },
    icon: { type: [String, null], default: null },
    open: { type: Boolean, default: false },
    dismissible: { type: Boolean, default: true },
});

const hasModalTitleComponent = hasComponent('ModalTitle');

const overlayClasses = cva({
    base: 'data-[state=open]:show fixed inset-0 z-(--z-index-portal) bg-gray-800/20 dark:bg-gray-800/50',
    variants: {
        blur: {
            true: 'backdrop-blur-[2px]',
        },
    },
})({ ...props });

const modalClasses = cva({
    base: [
        'fixed outline-hidden left-1/2 top-1/6 z-(--z-index-modal) w-full max-w-2xl -translate-x-1/2',
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
const isUsingOpenProp = computed(() => instance?.vnode.props?.hasOwnProperty('open'));

const open = ref(props.open);

watch(
    () => props.open,
    (value) => open.value = value,
);

// When the parent component controls the open state, emit an update event
// so it can update its state, which eventually gets passed down as a prop.
// Otherwise, update the local state.
function updateOpen(value) {
    if (isUsingOpenProp.value) {
        emit('update:open', value);
        return;
    }

    open.value = value;
}

function preventIfNotDismissible(event) {
    if (!props.dismissible) event.preventDefault();

    emit('dismissed');
}
</script>

<template>
    <DialogRoot :open="open" @update:open="updateOpen">
        <DialogTrigger data-ui-modal-trigger as-child>
            <slot name="trigger" />
        </DialogTrigger>
        <DialogPortal>
            <DialogOverlay :class="overlayClasses" />
            <DialogContent
                :class="[modalClasses, $attrs.class]"
                data-ui-modal-content
                :aria-describedby="undefined"
                @pointer-down-outside="preventIfNotDismissible"
                @escape-key-down="preventIfNotDismissible"
            >
                <div class="relative space-y-3 rounded-xl overflow-auto max-h-[60vh] border border-gray-400/60 bg-white p-4 shadow-[0_1px_16px_-2px_rgba(63,63,71,0.2)] dark:border-none dark:bg-gray-800 dark:shadow-[0_10px_15px_rgba(0,0,0,.5)] dark:inset-shadow-2xs dark:inset-shadow-white/10" >
                    <DialogTitle v-if="!hasModalTitleComponent" data-ui-modal-title class="flex items-center gap-2">
                        <Icon :name="icon" v-if="icon" class="size-4" />
                        <ui-heading :text="title" size="lg" class="font-medium" />
                    </DialogTitle>
                    <slot />
                </div>
                <slot name="footer" />
            </DialogContent>
        </DialogPortal>
    </DialogRoot>
</template>
