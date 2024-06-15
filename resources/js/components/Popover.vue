<template>
    <div :class="{'popover-open': isOpen}" @mouseleave="leave">

        <div @click="toggle" ref="trigger" aria-haspopup="true" :aria-expanded="isOpen" v-if="$slots.default">
            <slot name="trigger"></slot>
        </div>

        <teleport
            to="#popover"
            :target-class="`popover-container ${targetClass || ''}`"
        >
            <div :class="`${isOpen ? 'popover-open' : ''}`">
                <div ref="popover" class="popover" v-if="!disabled" v-click-away="clickawayClose">
                    <div class="popover-content bg-white dark:bg-dark-550 shadow-popover dark:shadow-dark-popover rounded-md">
                        <slot :close="close" />
                    </div>
                </div>
            </div>
        </teleport>
    </div>
</template>

<script>
import {
    useFloating,
    offset,
    flip,
    shift,
} from '@floating-ui/vue';

// import { computePosition, flip, shift, offset, autoUpdate } from '@floating-ui/dom';

export default {

    props: {
        autoclose: {
            type: Boolean,
            default: false
        },
        clickaway: {
            type: Boolean,
            default: true
        },
        disabled: {
            type: Boolean,
            default: false
        },
        offset: {
            type: Array,
            default: () => [10, 0]
        },
        placement: {
            type: String,
            default: 'bottom-end',
        },
    },

    setup() {

    }
    data() {
        return {
            isOpen: false,
            escBinding: null,
            cleanupAutoUpdater: null,
            portalTarget: null,
        }
    },

    provide() {
        return {
            popover: this.makeProvide(),
        }
    },

    computed: {

        targetClass() {
            return ''

            // @todo(jasonvarga): what was this used for?
            return this.$vnode.data.staticClass;
        }

    },

    methods: {

        computePosition() {
            if (! this.$refs.trigger?.firstChild) return;
            if (! this.$refs.popover) return;

            console.log('t', this.$refs.trigger, this.$refs.trigger.firstChild);
            console.log('p', this.$refs.popover);

            computePosition(this.$refs.trigger.firstChild, this.$refs.popover, {
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
            this.escBinding = this.$keys.bindGlobal('esc', e => this.close());
            this.$nextTick(() => {
                this.cleanupAutoUpdater = autoUpdate(this.$refs.trigger.firstChild, this.$refs.popover, this.computePosition);

                this.$refs.popover.addEventListener('transitionend', () => {
                    this.$emit('opened');
                }, { once: true });
            });
        },

        clickawayClose(e) {
            // If disabled or closed, do nothing.
            if (! this.clickaway || ! this.isOpen) return;

            // If clicking within the popover, or inside the trigger, do nothing.
            // These need to be checked separately, because the popover contents away.
            if (this.$refs.popover.contains(e.target) || this.$el.contains(e.target)) return;

            this.close();
            this.$emit('clicked-away', e);
        },

        close() {
            if (! this.isOpen) return;

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
        }
    }
}
</script>
