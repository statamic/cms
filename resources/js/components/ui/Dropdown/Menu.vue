<template>
    <div
        class="shadow-ui-xs z-10 grid grid-cols-[auto_1fr] overflow-hidden rounded-b-xl border-gray-200 bg-white p-1.5 group-has-data-ui-dropdown-footer:border-b dark:border-black dark:bg-gray-900 [&_[data-ui-radio-item]]:py-0.5 [&_[data-ui-radio-item]]:ps-3"
        data-ui-dropdown-menu
    >
        <slot />
    </div>
</template>
