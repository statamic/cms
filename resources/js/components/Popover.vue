<template>
    <div class="popover-container" :class="{'popover-open': isOpen}" v-on-clickaway="close">
        <div @click="toggle" ref="trigger" aria-haspopup="true" :aria-expanded="isOpen" v-if="$slots.default">
            <slot name="trigger"></slot>
        </div>
        <div ref="popover" class="popover">
            <div class="popover-content bg-white shadow-popover rounded-md">
                <slot></slot>
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
        placement: {
            type: String,
            default: 'bottom-start',
        },
        offset: {
            type: Array,
            default: () => [0, 10]
        }
    },

    data() {
        return {
            isOpen: false,
        }
    },

    mounted() {
        createPopper(this.$refs.trigger, this.$refs.popover, {
            placement: this.placement,
            modifiers: [
                {
                    name: 'offset',
                    options: {
                        offset: this.offset
                    }
                }
            ]
        })
    },

    methods: {
        toggle() {
            this.isOpen = ! this.isOpen;
        },
        open() {
            this.isOpen = true
        },
        close() {
            this.isOpen = false
        }
    }
}
</script>
