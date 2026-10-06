<script setup>
import { computed, onBeforeUnmount, onMounted, ref, useAttrs } from 'vue';
import DragDots from '@/../svg/drag-dots.svg';
import { pointerDistanceExceeded } from '@/components/sortable/pointerDragThreshold.js';

defineOptions({ inheritAttrs: false });

/**
 * When true, click / Space / Enter emits `keyboard-reorder` for arrow-key moving.
 * Opt-in so other DragHandle consumers keep native button behaviour.
 */
const props = defineProps({
    keyboardReorder: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['keyboard-reorder']);
const attrs = useAttrs();
const buttonEl = ref(null);

let pointerStart = null;
let didDrag = false;

const ariaLabel = computed(() => {
    if (props.keyboardReorder) {
        return __('messages.keyboard_item_reorder_handle');
    }

    return __('Drag to reorder');
});

const tooltip = computed(() => {
    if (!props.keyboardReorder) return null;

    return {
        content: __('messages.keyboard_item_reorder_handle_instructions'),
        focusOnly: true,
    };
});

function markDrag() {
    didDrag = true;
}

function clearPointerListeners() {
    window.removeEventListener('pointermove', onPointerMove, true);
    window.removeEventListener('pointerup', onPointerUp, true);
    window.removeEventListener('pointercancel', onPointerCancel, true);
}

function onKeydown(event) {
    if (!props.keyboardReorder) return;
    if (event.key !== ' ' && event.key !== 'Enter') return;

    event.preventDefault();
    event.stopPropagation();
    emit('keyboard-reorder');
}

function onPointerDown(event) {
    if (!props.keyboardReorder) return;
    if (event.button !== 0) return;
    // Touch keeps Sortable drag; click-to-move is for fine pointers / keyboard.
    if (event.pointerType === 'touch') return;

    // pageX/Y match @shopify/draggable's MouseSensor distance check.
    pointerStart = { x: event.pageX, y: event.pageY };
    didDrag = false;
    window.addEventListener('pointermove', onPointerMove, true);
    window.addEventListener('pointerup', onPointerUp, true);
    window.addEventListener('pointercancel', onPointerCancel, true);
}

function onPointerMove(event) {
    if (!pointerStart || didDrag) return;

    if (pointerDistanceExceeded(pointerStart, { x: event.pageX, y: event.pageY })) {
        didDrag = true;
    }
}

function onPointerUp() {
    clearPointerListeners();

    const wasDrag = didDrag;
    pointerStart = null;
    didDrag = false;

    // Draggable suppresses native click on handles; only treat a still pointer as a click.
    if (wasDrag) return;

    emit('keyboard-reorder');
}

function onPointerCancel() {
    clearPointerListeners();
    pointerStart = null;
    didDrag = false;
}

onMounted(() => {
    if (!props.keyboardReorder) return;
    buttonEl.value?.addEventListener('statamic-drag-start', markDrag);
});

onBeforeUnmount(() => {
    buttonEl.value?.removeEventListener('statamic-drag-start', markDrag);
    clearPointerListeners();
});
</script>

<template>
    <button
        ref="buttonEl"
        type="button"
        class="h-full flex items-center justify-center cursor-grab text-gray-400 dark:text-gray-500 hover:text-gray-700 dark:hover:text-gray-400"
        :class="{ 'rounded-sm p-1': keyboardReorder }"
        style="--focus-outline-offset: 2px"
        data-drag-handle
        :aria-label="ariaLabel"
        v-bind="attrs"
        v-tooltip="tooltip"
        @keydown="onKeydown"
        @pointerdown="onPointerDown"
    >
        <DragDots class="w-[7px] h-[17px]" />
    </button>
</template>
