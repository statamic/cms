<template>

    <portal :to="portal">
        <vue-modal v-bind="modalProps" :delay="25" @opened="modalOpened" @closed="modalClosed" :class="{'disable-overflow': overflow === false}">
            <slot :close="close" />
        </vue-modal>
    </portal>

</template>

<script>
export default {

    props: {
        name: { type: String, required: true },
        adaptive: { type: Boolean, default: true },
        draggable: { type: Boolean, default: false },
        clickToClose: { type: Boolean, default: false },
        pivotY: { type: Number, default: 0.1 },
        height: { default: 'auto' },
        width: {},
        overflow: { type: Boolean, default: true},
        scrollable: { type: Boolean, default: false}
    },

    data() {
        return {
            portal: null,
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
                pivotY: this.pivotY,
                width: this.width,
                scrollable: this.scrollable,
            }
        }

    },

    mounted() {
        this.portal = `modal-${this.$modals.count()}`;
        this.$modals.open(this.name);
    },

    destroyed() {
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
            this.$modals.remove(this.name);
            this.$emit('closed');
        }

    }

}
</script>
