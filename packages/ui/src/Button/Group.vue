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
        ]"
        data-ui-button-group
    >
        <slot />
    </div>
</template>

<style>
    [data-ui-button-group] [data-ui-group-target] {

        &:not(:first-child):not([data-floating-toolbar] &) {
            border-inline-start: 0;
        }

        /* Split button groups apart into individual buttons on small screens */
        [data-floating-toolbar] & {
            @media (width >= 1024px) {
                &:not(:first-child) {
                    border-inline-start: 0;
                }
            }
        }

        &.btn-danger {
            box-shadow: none;
            color: var(--color-red-600);
            .dark & {
                color: var(--color-red-500);
                background-color: var(--color-gray-850);
                border-color: var(--color-gray-700);
            }
        }
    }
</style>
