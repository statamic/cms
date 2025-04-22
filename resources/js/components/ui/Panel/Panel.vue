<template>
    <div
        :class="[
            'bg-gray-100 dark:border-b dark:border-white/13 dark:bg-black/25 dark:inset-shadow-2xs dark:inset-shadow-black',
            'not-prose w-full rounded-[.825rem] px-1 py-1.25',
        ]"
        data-ui-panel
    >
        <slot />
    </div>
</template>
