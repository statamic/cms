<template>
    <div
        :class="[
            'group/button inline-flex flex-wrap [[data-floating-toolbar]_&]:justify-center [[data-floating-toolbar]_&]:gap-1 [[data-floating-toolbar]_&]:lg:gap-x-0',
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

    /* When the listings are shorter than the viewport, position the floating toolbar at the bottom of the listings container. */
    @supports (anchor-name: --results) {
        /* [1] Test whether the listings container is scrollable if listings are present. */
        #main-content:has([data-listings-container]) {
            container-type: scroll-state;

            /* [2] Set up an anchor point for the floating toolbar. */
            [data-listings-container] {
                anchor-name: --results;
            }

            #content-card {
                /* [/3] If the listings container is relatively short (not scrollable), position the floating toolbar at the bottom of it. This is helpful for taller screens. The toolbar is more noticeable when it's not at the bottom of the viewport. */
                @container not scroll-state(scrollable: y) {
                    [data-floating-toolbar] {
                        position: absolute;
                        position-anchor: --results;
                        top: 3rem;
                        position-area: bottom;

                        > * {
                            translate: unset;
                        }
                    }
                }
            }
        }
    }
</style>
