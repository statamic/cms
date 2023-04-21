<template>
    <div :class="{'popover-open': isOpen}" @mouseleave="leave">

        <div @click="toggle" ref="trigger" aria-haspopup="true" :aria-expanded="isOpen" v-if="$scopedSlots.default">
            <slot name="trigger"></slot>
        </div>

        <portal
            name="popover"
            :target-class="`popover-container ${targetClass || ''}`"
            :provide="provide"
        >
            <div :class="`${isOpen ? 'popover-open' : ''}`">
                <div ref="popover" class="popover" v-if="!disabled" v-on-clickaway="clickawayClose">
                    <div class="popover-content bg-white shadow-popover rounded-md relative z-2">
                        <slot :close="close" />
                    </div>
                    <div v-if="arrow" ref="arrow" class="absolute z-3 bg-white h-3 w-3 rotate-45" />
                    <div v-if="arrow" ref="arrowShadow" class="absolute z-1 shadow-popover h-3 w-3 rotate-45" />
                </div>
            </div>
        </portal>

    </div>
</template>

<script>
import { mixin as clickaway } from 'vue-clickaway';
import { computePosition, flip, shift, offset, autoUpdate, arrow } from '@floating-ui/dom';

export default {

    mixins: [ clickaway ],

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
        arrow: {
            type: Boolean,
            default: true
        }
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
        }
    },

    computed: {

        targetClass() {
            return this.$vnode.data.staticClass;
        }

    },

    methods: {

        computePosition() {
            if (! this.$refs.trigger) return;

            // Adjust the offset so that the arrow doesn't overlap the trigger.
            let mainAxisOffset = this.offset[0];
            if (this.arrow) {
                const arrowLen = this.$refs.arrow.offsetWidth;
                const halfArrowHypotenuseLength = Math.sqrt(2 * arrowLen ** 2) / 2;
                mainAxisOffset += halfArrowHypotenuseLength;
            }

            let middleware = [
                offset({ mainAxis: mainAxisOffset, crossAxis: this.offset[1] }),
                flip(), // If you place it on the right, and there's not enough room, it'll flip to the left, etc.
                shift({ padding: 5 }), // If it'll end up positioned offscreen, it'll shift it enough to display it fully.
            ];

            if (this.arrow) {
                middleware.push(arrow({ element: this.$refs.arrow, padding: 20 }));
            }

            computePosition(this.$refs.trigger.firstChild, this.$refs.popover, {
                placement: this.placement,
                middleware
            }).then(({ x, y, middlewareData, placement }) => {
                Object.assign(this.$refs.popover.style, {
                    transform: `translate(${Math.round(x)}px, ${Math.round(y)}px)`, // Round to avoid blurry text
                });

                if (middlewareData.arrow) {
                    const { x, y } = middlewareData.arrow;
                    const side = placement.split('-')[0];
                    const staticSide = {
                        top: 'bottom',
                        right: 'left',
                        bottom: 'top',
                        left: 'right'
                    }[side];

                    [this.$refs.arrow, this.$refs.arrowShadow].forEach(el => {
                        const len = el.offsetWidth;
                        Object.assign(el.style, {
                            left: x != null ? `${x}px` : '',
                            top: y != null ? `${y}px` : '',
                            // Ensure the static side gets unset when flipping to other placements' axes.
                            right: '',
                            bottom: '',
                            [staticSide]: `${-len/2}px`,
                        });
                    });
                }
            });
        },

        toggle(e) {
            this.isOpen ? this.close() : this.open();
        },

        open() {
            if (this.disabled) return;

            this.isOpen = true;
            this.escBinding = this.$keys.bind('esc', e => this.close());
            this.$nextTick(() => {
                this.cleanupAutoUpdater = autoUpdate(this.$refs.trigger.firstChild, this.$refs.popover, this.computePosition);
                this.$emit('opened');

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
