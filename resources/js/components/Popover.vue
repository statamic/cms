<template>
    <div :class="[triggerClass, { 'popover-open': isOpen }]" @mouseleave="leave">
        <div @click="toggle" ref="trigger" aria-haspopup="true" :aria-expanded="isOpen" v-if="$slots.default">
            <slot name="trigger"></slot>
        </div>

        <portal name="popover" :target-class="`popover-container ${targetClass || ''}`" :provide="provide">
            <div :class="`${isOpen ? 'popover-open' : ''}`">
                <div ref="popover" class="popover" v-if="!disabled" v-click-away="clickawayClose">
                    <div
                        class="popover-content rounded-md bg-white shadow-md dark:bg-dark-550 dark:shadow-lg-lg"
                    >
                        <slot :close="close" />
                    </div>
                </div>
            </div>
        </portal>
    </div>
</template>

<script>
import { computePosition, flip, shift, offset, autoUpdate } from '@floating-ui/dom';

export default {
    props: {
        autoclose: {
            type: Boolean,
            default: false,
        },
        clickaway: {
            type: Boolean,
            default: true,
        },
        disabled: {
            type: Boolean,
            default: false,
        },
        offset: {
            type: Array,
            default: () => [10, 0],
        },
        placement: {
            type: String,
            default: 'bottom-end',
        },
        class: {
            // Gets applied to the popover content.
            type: String,
        },
        triggerClass: {
            // Gets applied to the trigger.
            type: String,
        },
    },

    data() {
        return {
            isOpen: false,
            escBinding: null,
            cleanupAutoUpdater: null,
            portalTarget: null,
            provide: {
                popover: this.makeProvide(),
            },
        };
    },

    computed: {
        targetClass() {
            return this.class;
        },
    },

    methods: {
        computePosition() {
            if (!this.$refs.trigger) return;

            computePosition(this.$refs.trigger.firstElementChild, this.$refs.popover, {
                placement: this.placement,
                middleware: [
                    offset({ mainAxis: this.offset[0], crossAxis: this.offset[1] }),
                    flip(), // If you place it on the right, and there's not enough room, it'll flip to the left, etc.
                    shift({ padding: 5 }), // If it'll end up positioned offscreen, it'll shift it enough to display it fully.
                ],
            }).then(({ x, y }) => {
                Object.assign(this.$refs.popover.style, {
                    transform: `translate(${Math.round(x)}px, ${Math.round(y)}px)`, // Round to avoid blurry text
                });
            });
        },

        toggle(e) {
            this.isOpen ? this.close() : this.open();
        },

        open() {
            if (this.disabled) return;

            this.isOpen = true;
            this.escBinding = this.$keys.bindGlobal('esc', (e) => this.close());
            this.$nextTick(() => {
                this.cleanupAutoUpdater = autoUpdate(
                    this.$refs.trigger.firstElementChild,
                    this.$refs.popover,
                    this.computePosition,
                );

                this.$refs.popover.addEventListener(
                    'transitionend',
                    () => {
                        this.$emit('opened');
                    },
                    { once: true },
                );
            });
        },

        clickawayClose(e) {
            // If disabled or closed, do nothing.
            if (!this.clickaway || !this.isOpen) return;

            // If clicking within the popover, or inside the trigger, do nothing.
            // These need to be checked separately, because the popover contents away.
            if (this.$refs.popover.contains(e.target) || this.$el.contains(e.target)) return;

            this.close();
            this.$emit('clicked-away', e);
        },

        close() {
            if (!this.isOpen) return;

            this.isOpen = false;
            this.$emit('closed');
            this.cleanupAutoUpdater();

            if (this.escBinding) this.escBinding.destroy();
        },

        leave() {
            if (this.autoclose) this.close();
        },

        makeProvide() {
            const provide = {};
            Object.defineProperties(provide, {
                vm: { get: () => this },
            });
            return provide;
        },
    },
};
</script>
