<template>
    <div :class="{'popover-open': isOpen}" @mouseleave="leave">
        <div @click="toggle" ref="trigger" aria-haspopup="true" :aria-expanded="isOpen" v-if="$slots.default">
            <slot name="trigger"></slot>
        </div>

        <portal
            name="popover"
            :target-class="`popover-container ${targetClass || ''}`"
            :provide="provide"
        >
            <div :class="`${isOpen ? 'popover-open' : ''}`">
                <div ref="popover" class="popover" v-if="!disabled" v-click-away="clickawayClose">
                    <div class="popover-content bg-white dark:bg-dark-550 shadow-popover dark:shadow-dark-popover rounded-md">
                        <slot :close="close" />
                    </div>
                </div>
            </div>
        </portal>
    </div>
</template>

<script>
import { nextTick } from 'vue';
import { computePosition as floatingComputePosition, flip, shift, offset, autoUpdate } from '@floating-ui/dom';

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
    data() {
        return {
            isOpen: false,
            escBinding: null,
            cleanupAutoUpdater: null,
        };
    },
    computed: {
        targetClass() {
            // @todo(jasonvarga): what was this used for?
            // return this.$vnode.data.staticClass;

            return this.$refs.trigger ? this.$refs.trigger.classList.value : '';
        },
        provide() {
            return {
                close: this.close,
            };
        }
    },
    methods: {
        computePosition() {
            if (!this.$refs.trigger) return;

            floatingComputePosition(this.$refs.trigger.firstChild, this.$refs.popover, {
                placement: this.placement,
                middleware: [
                    offset({mainAxis: this.offset[0], crossAxis: this.offset[1]}),
                    flip(),
                    shift({padding: 5}),
                ],
            }).then(({x, y}) => {
                Object.assign(this.$refs.popover.style, {
                    transform: `translate(${Math.round(x)}px, ${Math.round(y)}px)`,
                });
            });
        },
        toggle() {
            this.isOpen ? this.close() : this.open();
        },
        open() {
            if (this.disabled) return;

            this.isOpen = true;
            this.escBinding = this.$keys.bindGlobal('esc', e => this.close());

            nextTick(() => {
                if (this.$refs.trigger?.firstChild && this.$refs.popover) {
                    this.cleanupAutoUpdater = autoUpdate(this.$refs.trigger.firstChild, this.$refs.popover, this.computePosition);
                }
            });

            this.$refs.popover.addEventListener('transitionend', () => {
                this.$emit('opened');
            }, {once: true});
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
            if (!this.isOpen) return;

            this.isOpen = false;
            this.$emit('closed');
            if (this.cleanupAutoUpdater) this.cleanupAutoUpdater();
            if (this.escBinding) this.escBinding.destroy();
        },
        leave() {
            if (this.autoclose) this.close();
        }
    }
};
</script>
