<script setup>
import { cva } from 'cva';
import { DialogClose, DialogContent, DialogDescription, DialogOverlay, DialogPortal, DialogRoot, DialogTitle, DialogTrigger } from 'reka-ui';
import { computed, getCurrentInstance, ref, useSlots, watch } from 'vue';
import { Motion } from 'motion-v'

const emit = defineEmits(['update:open']);

const props = defineProps({
    description: { type: [String, null], default: null },
    open: { type: Boolean, default: false },
    side: { type: String, default: 'right' },
    title: { type: String, default: '' },
});

const drawerClasses = cva({
    base: [
        'fixed flex flex-col outline-hidden overflow-auto z-50 w-full',
        'bg-white dark:bg-gray-850 rounded-xl',
        'shadow-[0_8px_5px_-6px_rgba(0,0,0,0.12),_0_3px_8px_0_rgba(0,0,0,0.02),_0_30px_22px_-22px_rgba(39,39,42,0.35)]',
        'dark:shadow-[0_5px_20px_rgba(0,0,0,.5)]',
        'duration-250 will-change-[transform] transition-translate-x',
        'data-[state=open]:animate-in data-[state=closed]:animate-out',
    ],
    variants: {
        side: {
            right: 'right-2 slide-in-from-right slide-out-to-right h-[calc(100vh-1rem)] max-w-md inset-y-2',
            left: 'left-2 slide-in-from-left slide-out-to-left h-[calc(100vh-1rem)] max-w-md inset-y-2',
            bottom: 'bottom-2 slide-in-from-bottom slide-out-to-bottom inset-x h-auto max-h-[80vh]',
            top: 'top-2 slide-in-from-top slide-out-to-top inset-x h-auto max-h-[80vh]',
        },
    },
})({ ...props });

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

const slots = useSlots();
const hasFooter = !!slots.footer;
</script>

<template>
    <DialogRoot :open="open" @update:open="updateOpen">
        <DialogTrigger data-ui-drawer-trigger as-child>
            <slot name="trigger" />
        </DialogTrigger>
        <DialogPortal>
            <DialogOverlay as-child>
                <Motion :initial="{ opacity: 0 }" :animate="{ opacity: 1 }" :exit="{ opacity: 0 }" :transition="{ duration: 0.25 }" class="fixed inset-0 z-30 bg-gray-800/20 dark:bg-gray-800/50" />
            </DialogOverlay>
            <DialogContent :class="[drawerClasses, $attrs.class]" data-ui-drawer-content :aria-describedby="description || undefined">
                <header class="py-2 px-4">
                    <div class="flex items-center justify-between">
                        <ui-heading size="lg">
                            <DialogTitle>{{ title }}</DialogTitle>
                        </ui-heading>
                        <DialogClose as-child :aria-label="__('Close')">
                            <ui-button icon="x" variant="ghost" class="-me-2" />
                        </DialogClose>
                    </div>
                    <DialogDescription v-if="description">
                        <ui-description :text="description" />
                    </DialogDescription>
                </header>
                <div class="p-4 flex-1"><slot /></div>
                <footer class="p-4" v-if="hasFooter">
                    <slot name="footer" />
                </footer>
            </DialogContent>
        </DialogPortal>
    </DialogRoot>
</template>
