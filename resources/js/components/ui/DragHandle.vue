<script setup>
import DragDots from '@/../svg/drag-dots.svg';
</script>

<template>
    <button
        tabindex="-1"
        class="h-full cursor-grab flex items-center justify-center text-gray-400 dark:text-gray-500 hover:text-gray-700 dark:hover:text-gray-400"
        data-drag-handle
        :aria-label="__('Drag to reorder')"
    >
        <DragDots class="w-[7px] h-[17px] [&_g]:fill-current!" />
    </button>
</template>
