<template>
    <div class="popover-container" :class="{'popover-open': isOpen}" v-on-clickaway="close" @mouseleave="leave">
        <div @click="toggle" ref="trigger" aria-haspopup="true" :aria-expanded="isOpen" v-if="$scopedSlots.default">
            <slot name="trigger"></slot>
        </div>
        <div ref="popover" class="popover" v-if="!disabled">
            <div class="popover-content bg-white shadow-popover rounded-md">
                <slot :close="close" :after-closed="afterClosed" />
            </div>
        </div>
    </div>
</template>

<script>
import { mixin as clickaway } from 'vue-clickaway';
import { createPopper } from '@popperjs/core';

export default {

    mixins: [ clickaway ],

    props: {
        disabled: {
            type: Boolean,
            default: false
        },
        placement: {
            type: String,
            default: 'bottom-end',
        },
        offset: {
            type: Array,
            default: () => [0, 10]
        },
        scroll: {
            type: Boolean,
            default: false
        },
        autoclose: {
            type: Boolean,
            default: false
        }
    },

    data() {
        return {
            isOpen: false,
            escBinding: null,
            popper: null,
            closedCallbacks: []
        }
    },

    mounted() {
        if (! this.disabled) this.bindPopper();
    },

    beforeDestroy() {
        this.destroyPopper();
    },

    methods: {
        bindPopper() {
            this.popper = createPopper(this.$refs.trigger, this.$refs.popover, {
                placement: this.placement,
                modifiers: [
                    {
                        name: 'offset',
                        options: {
                            offset: this.offset
                        }
                    },
                    {
                        name: 'eventListeners',
                        options: {
                            scroll: this.scroll,
                            resize: true
                        }
                    }
                ]
            })
        },
        toggle() {
            this.isOpen ? this.close() : this.open();
        },
        open() {
            this.isOpen = true;
            this.escBinding = this.$keys.bind('esc', e => this.close())
            this.popper && this.popper.update();
        },
        close() {
            this.isOpen = false;
            if (this.escBinding) {
                this.escBinding.destroy();
            }
        },
        leave() {
            if (this.autoclose) {
                this.close();
            }
        },
        destroyPopper() {
            if (!this.popper) return;

            this.popper.destroy();
            this.popper = null;

            // run any after-closed callbacks
            this.closedCallbacks.forEach(callback => callback());
        },
        afterClosed(callback) {
            this.closedCallbacks.push(callback);
        },
    }
}
</script>
