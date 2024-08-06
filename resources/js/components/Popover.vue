<script setup lang="ts">
import { computed, ref, provide, onUnmounted } from 'vue';
import { useFloating, flip, offset, shift, Placement, autoUpdate } from '@floating-ui/vue';

const props = withDefaults(defineProps<{
    class?: string
    autoclose?: boolean
    clickaway?: boolean
    disabled?: boolean
    offset?: number[]
    placement?: Placement
}>(), {
    autoclose: false,
    clickaway: true,
    disabled: false,
    offset: () => [10, 0],
    placement: 'bottom-end',
})

const $emit = defineEmits(['opened', 'closed', 'clicked-away'])

const isOpen = ref(false)
const escBinding = ref(null)

const root = ref(null)
const trigger = ref(null)
const popover = ref(null)

const targetClass = computed(() => {
    return props.class;
})

function open() {
    if (props.disabled){
        return;
    }

    isOpen.value = true;

    escBinding.value = window.Statamic.$keys.bindGlobal('esc', close);

    popover.value.addEventListener('transitionend', () => {
        $emit('opened');
    }, { once: true });
}

function close() {
    if (!isOpen.value) return;

    isOpen.value = false;

    $emit('closed');

    escBinding.value?.destroy();
}

function toggle() {
    isOpen.value ? close() : open();
}

function clickawayClose(e) {
    // If disabled or closed, do nothing.
    if (!props.clickaway || !isOpen.value) {
        return;
    }

    // If clicking within the popover, or inside the trigger, do nothing.
    // These need to be checked separately, because the popover contents away.
    if (popover.value.contains(e.target) || root.value.contains(e.target)) {
        return;
    }

    close();

    $emit('clicked-away', e);
}

function leave() {
    if (props.autoclose) {
        close();
    }
}

const { floatingStyles } = useFloating(trigger, popover, {
    placement: props.placement,
    middleware: [
        offset({ mainAxis: props.offset[0], crossAxis: props.offset[1] }),
        flip(),
        shift({ padding: 5 }),
    ],
    whileElementsMounted: autoUpdate,
});

const provide = computed(() => ({
    popover: {
        close: close,
    }
}))

onUnmounted(() => {
    escBinding.value?.destroy();
})

defineExpose({
    open,
    close,
    toggle,
})
</script>

<template>
    <div ref="root" :class="[isOpen && 'popover-open', targetClass]" @mouseleave="leave">
        <div
            ref="trigger"
            v-if="$slots.default"
            aria-haspopup="true"
            :aria-expanded="isOpen"
            @click="toggle"
        >
            <slot name="trigger"></slot>
        </div>

        <portal
            name="popover"
            :target-class="`popover-container ${targetClass || ''}`"
            :provide="provide"
        >
            <div :class="`${isOpen ? 'popover-open' : ''}`">
                <div
                    ref="popover"
                    v-if="!disabled"
                    v-click-away="clickawayClose"
                    class="popover"
                    :style="floatingStyles"
                >
                    <div
                        class="popover-content bg-white dark:bg-dark-550 shadow-popover dark:shadow-dark-popover rounded-md"
                    >
                        <slot :close="close" />
                    </div>
                </div>
            </div>
        </portal>
    </div>
</template>
