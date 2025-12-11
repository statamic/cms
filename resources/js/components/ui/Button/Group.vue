<template>
    <div
        :class="[
            'group/button flex flex-wrap relative [[data-floating-toolbar]_&]:justify-center [[data-floating-toolbar]_&]:gap-1 [[data-floating-toolbar]_&]:lg:gap-x-0',
            'dark:[&_button]:ring-0',
            'max-lg:[[data-floating-toolbar]_&_button]:rounded-md!',
            'shadow-ui-sm rounded-lg',

            // Horizontal orientation
            '[&[data-orientation=horizontal]>[data-ui-group-target]:not(:first-child):not(:last-child)]:rounded-none',
            '[&[data-orientation=horizontal]>[data-ui-group-target]:first-child:not(:last-child)]:rounded-e-none',
            '[&[data-orientation=horizontal]>[data-ui-group-target]:last-child:not(:first-child)]:rounded-s-none',
            '[&[data-orientation=horizontal]>*:not(:first-child):not(:last-child):not(:only-child)_[data-ui-group-target]]:rounded-none',
            '[&[data-orientation=horizontal]>*:first-child:not(:last-child)_[data-ui-group-target]]:rounded-e-none',
            '[&[data-orientation=horizontal]>*:last-child:not(:first-child)_[data-ui-group-target]]:rounded-s-none',

            // Vertical orientation
            '[&[data-orientation=vertical]]:flex-col',
            '[&[data-orientation=vertical]>[data-ui-group-target]:not(:first-child):not(:last-child)]:rounded-none',
            '[&[data-orientation=vertical]>[data-ui-group-target]:first-child:not(:last-child)]:rounded-b-none',
            '[&[data-orientation=vertical]>[data-ui-group-target]:last-child:not(:first-child)]:rounded-t-none',
            '[&[data-orientation=vertical]>*:not(:first-child):not(:last-child):not(:only-child)_[data-ui-group-target]]:rounded-none',
            '[&[data-orientation=vertical]>*:first-child:not(:last-child)_[data-ui-group-target]]:rounded-b-none',
            '[&[data-orientation=vertical]>*:last-child:not(:first-child)_[data-ui-group-target]]:rounded-t-none',
        ]"
        data-ui-button-group
    >
        <slot />
    </div>
</template>

<script>
export default {
    props: {
        orientation: {
            type: String,
            default: 'horizontal',
        },
    },

    data() {
        return {
            resizeObserver: null,
        };
    },

    mounted() {
        this.$el.dataset.orientation = this.orientation;
        if (this.orientation === 'auto') {
            this.observeOrientation();
        }
    },

    beforeUnmount() {
        this.unobserveOrientation();
    },

    methods: {
        observeOrientation() {
            this.resizeObserver = new ResizeObserver(() => this.checkOrientation(this.$el));
            this.resizeObserver.observe(this.$el);
        },

        unobserveOrientation() {
            this.resizeObserver?.disconnect();
        },

        checkOrientation(node) {
            delete node.dataset.orientation;

            const child = node.lastElementChild;
            if (!child) return;

            node.dataset.orientation = child.offsetTop > node.clientTop
                ? 'vertical'
                : 'horizontal';
        },
    },
};
</script>

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

    /* Vertical orientation border handling */
    [data-ui-button-group][data-orientation='vertical'] [data-ui-group-target]:not(:first-child) {
        border-inline-start-width: 1px;
        border-top: 0;
    }
</style>
