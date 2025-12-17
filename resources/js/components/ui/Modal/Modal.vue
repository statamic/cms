<script setup>
import { cva } from 'cva';
import { hasComponent } from '@/composables/has-component.js';
import { computed, getCurrentInstance, nextTick, onBeforeUnmount, onMounted, provide, ref, useAttrs, useSlots, watch } from 'vue';
import Icon from '../Icon/Icon.vue';
import Heading from '../Heading.vue';
import { portals, keys } from '@api';
import wait from '@/util/wait';

defineOptions({
    inheritAttrs: false,
});

const attrs = useAttrs();
const slots = useSlots();
const emit = defineEmits(['update:open', 'dismissed']);

const props = defineProps({
    blur: { type: Boolean, default: true },
    title: { type: String, default: '' },
    icon: { type: [String, null], default: null },
    open: { type: Boolean, default: false },
    dismissible: { type: Boolean, default: true },
});

const overlayClasses = cva({
    base: 'fixed inset-0 z-(--z-index-portal) bg-gray-800/20 dark:bg-gray-800/50',
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
    ],
})({});

const hasModalTitleComponent = hasComponent('ModalTitle');
const isUsingOpenProp = computed(() => instance?.vnode.props?.hasOwnProperty('open'));

const instance = getCurrentInstance();

const modal = ref(null);
const mounted = ref(false);
const visible = ref(false);
const escBinding = ref(null);

const portal = computed(() => modal.value ? `#portal-target-${modal.value.id}` : null);

function open() {
    if (!modal.value) modal.value = portals.create('modal');

    escBinding.value = keys.bindGlobal('esc', dismiss);

    nextTick(() => {
        mounted.value = true;

        nextTick(() => visible.value = true);

	    updateOpen(true);
    });
}

function close() {
    visible.value = false;

    wait(300).then(() => {
        mounted.value = false;
        updateOpen(false);
    });
}

function dismiss() {
    if (!props.dismissible) return;

    emit('dismissed');
    close();
}

provide('closeModal', close);

function updateOpen(value) {
    if (isUsingOpenProp.value) {
        emit('update:open', value);
    }
}

watch(
    () => props.open,
    (value) => value ? open() : close(),
);

onMounted(() => {
    if (props.open) open();
});

onBeforeUnmount(() => {
    modal.value?.destroy();
    escBinding.value?.destroy();
});

defineExpose({
    open,
    close,
});
</script>

<template>
    <div v-if="slots.trigger" @click="open">
        <slot name="trigger" />
    </div>
    <teleport :to="portal" v-if="mounted && portal">
        <div class="vue-portal-target modal">
            <transition
                enter-active-class="duration-200"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="duration-200"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="visible" :class="overlayClasses" @click="dismiss" />
            </transition>
            <transition
                enter-active-class="duration-200"
                enter-from-class="opacity-0 scale-95"
                enter-to-class="opacity-100 scale-100"
                leave-active-class="duration-200"
                leave-from-class="opacity-100 scale-100"
                leave-to-class="opacity-0 scale-95"
            >
                <div v-if="visible" :class="[modalClasses, attrs.class]" data-ui-modal-content>
                    <div class="relative space-y-3 rounded-xl overflow-auto max-h-[60vh] border border-gray-400/60 bg-white p-4 shadow-[0_1px_16px_-2px_rgba(63,63,71,0.2)] dark:border-none dark:bg-gray-800 dark:shadow-[0_1px_16px_-2px_rgba(0,0,0,.5)] dark:inset-shadow-2xs dark:inset-shadow-white/10">
                        <div v-if="!hasModalTitleComponent && (title || icon)" data-ui-modal-title class="flex items-center gap-2">
                            <Icon :name="icon" v-if="icon" class="size-4" />
                            <Heading :text="title" size="lg" class="font-medium" />
                        </div>
                        <slot />
                    </div>
                    <slot name="footer" />
                </div>
            </transition>
        </div>
    </teleport>
</template>
