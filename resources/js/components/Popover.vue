<template>
    <div class="popover-container" :class="{'popover-open': isOpen}" v-on-clickaway="close">
        <div @click="toggle" ref="trigger" aria-haspopup="true" :aria-expanded="isOpen" v-if="$scopedSlots.default">
            <slot name="trigger"></slot>
        </div>
        <div ref="popover" class="popover" v-if="!disabled">
            <div class="popover-content bg-white shadow-popover rounded-md">
                <slot :close="close" />
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
        }
    },

    data() {
        return {
            isOpen: false
        }
    },

    mounted() {
        if (! this.disabled) this.bindPopper()
    },

    methods: {
        bindPopper() {
            createPopper(this.$refs.trigger, this.$refs.popover, {
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
            this.$keys.bind('esc', e => this.close())
        },
        close() {
            this.isOpen = false;
        }
    }
}
</script>
