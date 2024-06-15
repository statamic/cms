<template>
    <div :class="{'popover-open': isOpen}" @mouseleave="leave">
        <div @click="toggle" ref="trigger" aria-haspopup="true" :aria-expanded="isOpen">
            <slot name="trigger"></slot>
        </div>
        <portal
            name="popover"
            :target-class="`popover-container ${targetClass || ''}`"
            :provide="provide"
        >
            <div :class="`popover-container ${targetClass || ''} ${isOpen ? 'popover-open' : ''}`">
                <div ref="popover" class="popover" v-if="!disabled" v-click-outside="clickawayClose">
                    <div class="popover-content bg-white dark:bg-dark-550 shadow-popover dark:shadow-dark-popover rounded-md">
                        <slot :close="close"></slot>
                    </div>
                </div>
            </div>
        </portal>
    </div>
</template>

<script>
import { ref, computed, nextTick } from 'vue';
import { computePosition as floatingComputePosition, flip, shift, offset, autoUpdate } from '@floating-ui/dom';

export default {
    directives: {
        clickOutside: {
            beforeMount(el, binding) {
                el.clickOutsideEvent = function(event) {
                    // Check if the click was outside the element and its children
                    if (!(el == event.target || el.contains(event.target))) {
                        binding.value(event);
                    }
                };
                document.body.addEventListener('click', el.clickOutsideEvent);
            },
            unmounted(el) {
                document.body.removeEventListener('click', el.clickOutsideEvent);
            },
        },
    },
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
            default: 'bottom-end'
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
            this.escBinding = (e) => {
                if (e.key === 'Escape') this.close();
            };
            document.addEventListener('keydown', this.escBinding);

            nextTick(() => {
                if (this.$refs.trigger && this.$refs.trigger.firstChild && this.$refs.popover) {
                    this.cleanupAutoUpdater = autoUpdate(this.$refs.trigger.firstChild, this.$refs.popover, this.computePosition);
                }
            });

            this.$refs.popover.addEventListener('transitionend', () => {
                this.$emit('opened');
            }, {once: true});
        },
        clickawayClose(e) {
            if (!this.clickaway || !this.isOpen) return;
            if (this.$refs.popover.contains(e.target) || this.$refs.trigger.contains(e.target)) return;

            this.close();
            this.$emit('clicked-away', e);
        },
        close() {
            if (!this.isOpen) return;

            this.isOpen = false;
            this.$emit('closed');
            if (this.cleanupAutoUpdater) this.cleanupAutoUpdater();
            if (this.escBinding) document.removeEventListener('keydown', this.escBinding);
        },
        leave() {
            if (this.autoclose) this.close();
        }
    },
    mounted() {
        document.addEventListener('click', this.clickawayClose);
    },
    unmounted() {
        document.removeEventListener('click', this.clickawayClose);
        if (this.cleanupAutoUpdater) this.cleanupAutoUpdater();
        if (this.escBinding) document.removeEventListener('keydown', this.escBinding);
    }
};
</script>
