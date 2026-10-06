<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue';
import DragDots from '@/../svg/drag-dots.svg';
import { pointerDistanceExceeded } from '@/components/sortable/pointerDragThreshold.js';

const emit = defineEmits(['keyboard-reorder']);
const buttonEl = ref(null);

let pointerStart = null;
let didDrag = false;

function markDrag() {
    didDrag = true;
}

function onKeydown(event) {
    if (event.key !== ' ' && event.key !== 'Enter') return;

    event.preventDefault();
    event.stopPropagation();
    emit('keyboard-reorder');
}

function onPointerDown(event) {
    if (event.button !== 0) return;

    // pageX/Y match @shopify/draggable's MouseSensor distance check.
    pointerStart = { x: event.pageX, y: event.pageY };
    didDrag = false;
    window.addEventListener('pointermove', onPointerMove, true);
    window.addEventListener('pointerup', onPointerUp, true);
}

function onPointerMove(event) {
    if (!pointerStart || didDrag) return;

    if (pointerDistanceExceeded(pointerStart, { x: event.pageX, y: event.pageY })) {
        didDrag = true;
    }
}

function onPointerUp() {
    window.removeEventListener('pointermove', onPointerMove, true);
    window.removeEventListener('pointerup', onPointerUp, true);

    const wasDrag = didDrag;
    pointerStart = null;
    didDrag = false;

    // Draggable suppresses native click on handles; only treat a still pointer as a click.
    if (wasDrag) return;

    emit('keyboard-reorder');
}

onMounted(() => {
    buttonEl.value?.addEventListener('statamic-drag-start', markDrag);
});

onBeforeUnmount(() => {
    buttonEl.value?.removeEventListener('statamic-drag-start', markDrag);
    window.removeEventListener('pointermove', onPointerMove, true);
    window.removeEventListener('pointerup', onPointerUp, true);
});
</script>

<template>
    <button
        ref="buttonEl"
        type="button"
        class="flex items-center justify-center rounded-sm p-1 cursor-grab text-gray-400 dark:text-gray-500 hover:text-gray-700 dark:hover:text-gray-400"
        style="--focus-outline-offset: 2px"
        data-drag-handle
        :aria-label="__('Drag to reorder, or click / press Space to move with arrow keys')"
        v-tooltip="{ content: __('Click or press Space, then ↑↓ to reorder'), focusOnly: true }"
        @keydown="onKeydown"
        @pointerdown="onPointerDown"
    >
        <DragDots class="w-[7px] h-[17px]" />
    </button>
</template>
