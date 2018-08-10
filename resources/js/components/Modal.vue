<template>
    <div class="modal" :class="{'full-width': full}" role="dialog" tabindex="-1" v-if="show">
        <div class="modal-dialog">
            <div class="modal-content animated" :class="{'shake': shake}" v-on-clickaway="closeIfDismissible">

                <div class="saving" v-if="saving">
                    <div class="inner">
                        <i class="icon icon-circular-graph animation-spin"></i> {{ translate('cp.saving') }}
                    </div>
                </div>

                <div class="modal-header">
                    <slot name="close">
                        <button type="button" tabindex="-1" class="close" aria-label="Close" @click="close"><span aria-hidden="true">&times;</span></button>
                    </slot>
                    <h1><slot name="header"></slot></h1>
                </div>

                <div v-if="loading" class="loading">
                    <span class="icon icon-circular-graph animation-spin"></span> {{ translate('cp.loading') }}
                </div>

                <div v-if="! loading" class="modal-body">
                    <slot name="body"></slot>
                </div>

                <div v-if="! loading" class="modal-footer">
                    <slot name="footer">
                        <button type="button" class="btn" @click="close">{{ translate('cp.close') }}</button>
                    </slot>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { mixin as clickaway } from 'vue-clickaway';

export default {

    mixins: [ clickaway ],

    props: {
        show: {
            type: Boolean,
            required: true,
            default: false
        },
        full: {
            type: Boolean,
            required: false,
            default: false
        },
        dismissible: {
            type: Boolean,
            default: false
        },
        loading: Boolean,
        saving: Boolean,
        shake: {
            type: Boolean,
            default: false
        }
    },

    methods: {
        close: function() {
            this.show = false
        },
        closeIfDismissible: function() {
            if (this.dismissible) {
                this.show = false
            }
        },
    },

    watch: {
        // Emit an event so we can use one-way-down props to prepare for vue2
        show(val) {
            this.$eventHub.$emit(val === true ? 'opened' : 'closed');
            this.$eventHub.$emit(val === true ? 'modal.open' : 'modal.close');
        }
    },

    mounted() {
        // Mousetrap.bind('esc', function(e) {
        //     this.close();
        // }.bind(this), 'keyup');
    },

    events: {
        'close-modal': function () {
            this.show = false;
        }
    }
};
</script>
