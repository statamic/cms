<template>

    <portal name="modal">
        <v-modal v-bind="modalProps" :delay="25" @opened="modalOpened" @closed="modalClosed">
            <slot :close="close" />
        </v-modal>
    </portal>

</template>

<script>
import uniqid from 'uniqid';
import { disableBodyScroll, enableBodyScroll } from 'body-scroll-lock';

export default {

    props: {
        adaptive: { type: Boolean, default: true },
        draggable: { default: false },
        clickToClose: { type: Boolean, default: false },
        shiftY: { type: Number, default: 0.1 },
        focusTrap: {type: Boolean, default: true},
        height: { default: 'auto' },
        width: {},
        scrollable: { type: Boolean, default: false}
    },

    data() {
        return {
            modal: null,
            name: uniqid(),
        }
    },

    computed: {

        modalProps() {
            return {
                name: this.name,
                adaptive: this.adaptive,
                clickToClose: this.clickToClose,
                draggable: this.draggable,
                height: this.height,
                shiftY: this.shiftY,
                focusTrap: this.focusTrap,
                width: this.width,
                scrollable: this.scrollable,
            }
        },

    },

    mounted() {
        this.$nextTick(() => this.$modal.show(this.name));
        if (!this.scrollable) disableBodyScroll(this.$el);
    },

    beforeDestroy() {
        enableBodyScroll(this.$el);
        this.close();
    },

    methods: {

        modalOpened(event) {
            this.$emit('opened');
        },

        modalClosed(event) {
            this.close();
        },

        close() {
            this.$modal.hide(this.name);
            this.$emit('closed');
        }

    }

}
</script>
