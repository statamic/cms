<template>
    <button
        class="h-full cursor-grab flex items-center justify-center text-gray-400 dark:text-gray-500 hover:text-gray-700 dark:hover:text-gray-400"
        data-drag-handle
        :aria-label="__('Drag to reorder')"
    >
        <ui-icon name="ui/drag-dots" class="w-[7px] h-[17px]" />
    </button>
</template>
