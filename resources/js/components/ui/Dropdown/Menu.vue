<template>
    <div
        class="
            grid grid-cols-[auto_1fr] p-1.5
            bg-white border-gray-200 dark:bg-gray-900 dark:border-black rounded-b-xl shadow-ui-xs
            group-has-data-ui-dropdown-footer:border-b
            [&_[data-ui-radio-item]]:ps-3 [&_[data-ui-radio-item]]:py-0.5
            overflow-hidden z-10
        "
        data-ui-dropdown-menu
    >
        <slot />
    </div>
</template>
