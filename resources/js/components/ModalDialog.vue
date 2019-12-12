<template>
    <div class="modal" role="dialog" tabindex="-1" ref="modal" v-cloak>
        <div class="modal-dialog">
            <div class="modal-content" v-on-clickaway="dismiss">
                <div class="modal-header flex items-center justify-between">
                    <slot name="header"></slot>
                    <slot name="close">
                        <button type="button" tabindex="-1" class="close" aria-label="Close" @click="dismiss">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </slot>
                </div>

                <div class="modal-body">
                    <slot></slot>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { mixin as clickaway } from 'vue-clickaway';

export default {
    mixins: [ clickaway ],

    data() {
        return {
            keybinding: null,
        }
    },

    props: {
        show: {
            type: Boolean,
            required: true,
            default: false
        },
        dismissible: {
            type: Boolean,
            default: true
        },
        shake: {
            type: Boolean,
            default: false
        }
    },
    watch: {
        show: {
            immediate: true,
            handler: show => {
                if (show) {
                    document.body.style.setProperty("overflow", "hidden")
                } else {
                    document.body.style.removeProperty("overflow")
                }
            }
        }
    },

    created() {
        this.keybinding = this.$keys.bind('esc', this.dismiss)
    },

    destroyed() {
        this.keybinding.destroy();
    },

    methods: {
        dismiss: function() {
            this.$emit('close')
        }
    },
};
</script>
