<template>

    <portal :to="portal">
        <vue-modal v-bind="modalProps" :delay="25" @opened="modalOpened" @closed="modalClosed" :class="{'disable-overflow': overflow === false}" v-show="isTopmostPortal">
            <slot :close="close" />
        </vue-modal>
    </portal>

</template>

<script>
import uniqid from 'uniqid';

export default {

    props: {
        adaptive: { type: Boolean, default: true },
        draggable: { type: Boolean, default: false },
        clickToClose: { type: Boolean, default: false },
        shiftY: { type: Number, default: 0.1 },
        focusTrap: {type: Boolean, default: true},
        height: { default: 'auto' },
        width: {},
        overflow: { type: Boolean, default: true},
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

        isTopmostPortal() {
            const portals = this.$root.portals;
            return portals[portals.length - 1] === this.modal;
        },

        portal() {
            return this.modal ? this.modal.key : null;
        },

    },

    mounted() {
        this.modal = this.$modals.open(this.name);
        this.$root.hideOverflow = true;
    },

    destroyed() {
        this.close();
        this.$root.hideOverflow = false;
    },

    methods: {

        modalOpened(event) {
            this.$emit('opened');
        },

        modalClosed(event) {
            this.close();
        },

        close() {
            this.$modals.remove(this.name);
            this.$emit('closed');
        }

    }

}
</script>
