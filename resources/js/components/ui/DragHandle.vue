<script setup>
import DragDots from '@/../svg/drag-dots.svg';

const emit = defineEmits(['keyboard-reorder']);

function onKeydown(event) {
    if (event.key !== ' ' && event.key !== 'Enter') return;

    event.preventDefault();
    event.stopPropagation();
    emit('keyboard-reorder');
}
</script>

<template>
    <button
        type="button"
        class="h-full cursor-grab flex items-center justify-center text-gray-400 dark:text-gray-500 hover:text-gray-700 dark:hover:text-gray-400"
        data-drag-handle
        :aria-label="__('Drag to reorder, or press Space to move with arrow keys')"
        @keydown="onKeydown"
    >
        <DragDots class="w-[7px] h-[17px]" />
    </button>
</template>
