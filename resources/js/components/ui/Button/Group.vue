<template>
    <div
        :class="[
            'group/button flex flex-wrap [[data-floating-toolbar]_&]:justify-center [[data-floating-toolbar]_&]:gap-1 [[data-floating-toolbar]_&]:lg:gap-x-0',
            '[&>[data-ui-group-target]:not(:first-child):not(:last-child)]:rounded-none',
            '[&>[data-ui-group-target]:first-child:not(:last-child)]:rounded-e-none',
            '[&>[data-ui-group-target]:last-child:not(:first-child)]:rounded-s-none',
            '[&>*:not(:first-child):not(:last-child):not(:only-child)_[data-ui-group-target]]:rounded-none',
            '[&>*:first-child:not(:last-child)_[data-ui-group-target]]:rounded-e-none',
            '[&>*:last-child:not(:first-child)_[data-ui-group-target]]:rounded-s-none',
            'dark:[&_button]:ring-0',
            'max-lg:[[data-floating-toolbar]_&_button]:rounded-md!',
            'shadow-ui-sm rounded-lg'
        ]"
        data-ui-button-group
    >
        <slot />
    </div>
</template>

<style>
    [data-ui-button-group] [data-ui-group-target] {

        @apply shadow-none;

        &:not(:first-child):not([data-floating-toolbar] &) {
            border-inline-start: 0;
        }

        /* Account for button groups being split apart on small screens */
        [data-floating-toolbar] & {
            @media (width >= 1024px) {
                &:not(:first-child) {
                    border-inline-start: 0;
                }
            }
        }
    }
</style>
